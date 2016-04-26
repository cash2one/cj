<?php
/**
 * BlessingRedpackService.class.php
 * 红包定时任务 service
 * @author: anything
 * @createTime: 2015/11/25 15:44
 * @version: $Id$ 
 * @copyright: 畅移信息
 */
namespace UcRpc\Service;
use Think\Log;
use Common\Common\Cache;
use Common\Common\WxqyMsg;
use BlessingRedpack\Model\BlessingRedpackModel;

class BlessingRedpackService extends AbstractService {


    // 红包队列缓存key
    const REDPACK_QUEUE_KEY = "cy_redpack_";

    // 存放红包总数缓存key(用于计算抢红包排名)
    const REDPACK_SUM_KEY = "cy_redpack_sum_";

    // 已抢到红包的用户的缓存key
    const REDPACK_USER_KEY = "cy_redpack_user_";

    // 缓存key前缀
    protected $_redis_key = null;

    // redis配置
    public $config_redis = array();



    // 构造方法
    public function __construct() {
        parent::__construct();

        $cache = &Cache::instance();
        $setting = $cache->get('Common.setting');
        $this->_redis_key = $setting['domain'];

        $this->config_redis = array(
            'host'=> cfg('REDIS_HOST'),
            'port'=> cfg('REDIS_HPORT'),
            'pwd' => cfg("REDIS_PWD")
        );

    }


    /**
     * 查询红包数据，推送红包消息
     */
    public function send_msg(){

        Log::record('红包消息crontab开始', Log::INFO);

        try{

            //缓存
            $cache = &Cache::instance();
            $cache_setting = $cache->get('Common.setting');
            //$cache_departments = $cache->get('Common.department');
            $cache_bless_setting = $cache->get('BlessingRedpack.setting');

            //dao注入
            //$member_Model = D("Common/Member");
            $model_plugin = D('Common/CommonPlugin');
            $blessingMode = D('BlessingRedpack/BlessingRedpack');
            $blessingLogModel = D('BlessingRedpack/BlessingRedpackLog');
            $blessingDepartmentModel = D('BlessingRedpack/BlessingRedpackDepartment');


            // 读取插件信息
            $plugin = $model_plugin->get_by_identifier('blessingredpack');

            // 如果 agentid 为空
            if (empty($plugin['cp_agentid'])) {
                return true;
            }

            // 更新 pluginid, agentid 配置
            cfg('PLUGIN_ID', $plugin['cp_pluginid']);
            cfg('AGENT_ID', $plugin['cp_agentid']);

            //系统当前时间
            $systemTime = time();

            //查询红包主表当前时间之前的所有未推送的数据
            $params = array(
                'msg_status' => BlessingRedpackModel::MSG_NOSENT,
                'systime' => $systemTime
            );
            $blessList = $blessingMode->list_by_params($params);

            /*先更新状态为已推送*/
            $upData = array(
                'msg_status' => BlessingRedpackModel::MSG_SENT
            );
            foreach($blessList as $v){
                $blessingMode->update($v['id'], $upData);
            }

            //记录推送失败
            $faild = array();

            /*遍历，查询明细数据，推送消息*/
            foreach($blessList as $bless){

                //查询红包明细表
                $conds = array(
                    'redpack_id' => $bless['id']
                );
                $blessLogList = $blessingLogModel->list_by_conds($conds);

                //如果没有明细数据，记录失败
                if(empty($blessLogList)){
                    $faild[] = $bless['id'];
                    continue;
                }

                //先根据新增的红包主键，清空红包缓存旧数据，防止极端情况下（直接删除表等）导致缓存遗留重复key值的垃圾数据
                //$this->__delete_cache($bless['id']);

                //如果是自由红包，不推送消息
                if($bless['type'] == BlessingRedpackModel::TYPE_FREE){
                    //放入缓存
                    $this->__add_redis_queue($bless, $blessLogList);
                    continue;
                }

                //查询红包部门关联表
                $blessDepartment = $blessingDepartmentModel->get_by_conds($conds);

                //如果没有部门接收人数据，记录失败
                if(empty($blessDepartment)){
                    $faild[] = $bless['id'];
                    continue;
                }

                //如果部门id和接收人id都为0，给全公司发送
                if($blessDepartment['dep_id'] == '0' && $blessDepartment['receive_uid'] == '0'){
                    $mem_list = '@all';
                }else{
                    $mem_list = unserialize($blessDepartment['receive_uid']);
                }

                //放入缓存
                $this->__add_redis_queue($bless, $blessLogList);

                //封装推送数据
                $arry = array(
                    'redpackId' => $bless['id'],
                    'title' => $bless['actname'],//活动主题
                    'inviteContent' => $bless['invite_content'],//邀请语
                    'domain' => $cache_setting['domain'],
                    'redpackUrl' => $cache_bless_setting['redpack_url'],
                    'pluginid' => $plugin['cp_pluginid'],
                    'agentid' => $plugin['cp_agentid']
                );

                //推送消息
                $result = $this->__send($mem_list, $arry);

                if(!$result){
                    $faild[] = $bless['id'];
                    continue;
                }

            }
            Log::record('失败记录-----' . var_export(count($faild), true), Log::INFO);

            //推送完成，更新推送失败的记录
            if(!empty($faild)){

                Log::record('更新红包主表推送失败的记录开始', Log::INFO);

                $upData = array(
                    'msg_status' => BlessingRedpackModel::MSG_NOSENT
                );
                foreach($faild as $v){
                    $blessingMode->update($v, $upData);
                }

                Log::record('更新红包主表推送失败的记录结束', Log::INFO);
            }
        }catch (\RedisException $redis){
            Log::record('红包定时任务redis异常：', Log::ERR);
            Log::record($redis->getMessage(), Log::ERR);
            return false;
        }catch (\Exception $e){
            Log::record('红包定时任务异常：', Log::ERR);
            Log::record($e->getMessage(), Log::ERR);
            return false;
        }

        Log::record('红包消息crontab结束', Log::INFO);
        return true;
    }

    /**
     * 发送消息
     * @param $uids
     * @param $arry
     * @return bool
     */
    private function __send($uids, $arry) {

        Log::record('红包消息推送开始(crontab)------红包ID：' . $arry['redpackId'], Log::INFO);

        $wxqyMsg = WxqyMsg::instance();

        $url = 'http://' . $arry['domain'] . $arry['redpackUrl'] . '?id=' . $arry['redpackId'] . '&title=' . $arry['title'];
        $desc = "\n" . $arry['inviteContent'];

        //如果给全公司发送
        if($uids == '@all'){
            $result = $wxqyMsg->send_news($arry['title'], $desc, $url, $uids, '', '', $arry['agentid'], $arry['pluginid']);
            Log::record('红包消息推送全公司结束(crontab),result-------' . $result, Log::INFO);
            return true;
        }

        /*推送指定人员*/
        $speed = 100;//每次插入100条
        $batch_count = 0;
        $size = count($uids);
        do{

            $from = $batch_count * $speed;
            $batch_count++;
            $tmp = array_slice($uids, $from, $speed, true);

            $result = $wxqyMsg->send_news($arry['title'], $desc, $url, $tmp, '', '', $arry['agentid'], $arry['pluginid']);

            Log::record('红包消息推送指定人员结果(crontab),result-------' . $result, Log::INFO);

        } while ($size > ($speed * $batch_count));

        return true;
    }

    /**
     * 红包明细放入队列
     * @param $redpack 红包主表数据
     * @param $blessLogList 红包明细数据
     */
    private function __add_redis_queue($redpack, $blessLogList){

        $redis = \Common\Common\RedisUtil\RedisClient::getInstance($this->config_redis);

        //查看缓存是否已经存在，防止推送消息失败，多次推送消息时插入多次
        $data = $redis->get($this->_redis_key.self::REDPACK_SUM_KEY.$redpack['id']);
        if($data){
            Log::record("缓存已存在,不再重复放入", Log::INFO);
            return true;
        }


        //计算缓存有效期，单位：秒
        $time = $redpack['endtime'] - time();

        $pipe = $redis->multi(\Redis::PIPELINE);
        foreach ($blessLogList as $_v) {
            $tmp_arry = array(
                "rid" => $_v['id'],
                "money" => $_v['money']
            );

            // 存入红包明细缓存
            $pipe->rPush($this->_redis_key . self::REDPACK_QUEUE_KEY.$redpack['id'], serialize($tmp_arry));
            // 设置队列红包明细有效期
            $pipe->expire($this->_redis_key . self::REDPACK_QUEUE_KEY.$redpack['id'], $time);
        }
        $pipe->exec();

        //封装数据
        $param = array(
            'total' => $redpack['redpacks'],//红包总数
            'starttime' => $redpack['starttime'],//红包开始时间
            'endtime' => $redpack['endtime']
        );

        $redis->set($this->_redis_key . self::REDPACK_SUM_KEY.$redpack['id'], serialize($param), $time);

    }

    /**
     * 清空红包缓存旧数据，防止极端情况下删除表遗留垃圾数据
     * @param $redpackId
     * @return bool
     */
    private function __delete_cache($redpackId) {

        if(!empty($redpackId)){

            $redis = \Common\Common\RedisUtil\RedisClient::getInstance($this->config_redis);

            $t1 = $redis->get($this->_redis_key.self::REDPACK_SUM_KEY.$redpackId);
            if($t1){
                // 如果缓存中有旧数据就需要清空当前红包缓存
                // 清除主表cache
                $redis->delKey($this->_redis_key.self::REDPACK_SUM_KEY.$redpackId);

                // 清除队列key
                $redis->delKey($this->_redis_key.self::REDPACK_QUEUE_KEY.$redpackId);

                // 清除已抢到该红包的用户cache
                $tmp = $redis->keys($this->_redis_key.self::REDPACK_USER_KEY.$redpackId.'_');
                foreach ($tmp as $value) {
                    $redis->delKey($value);
                }
            }
        }

        Log::record("delete_cache ok", Log::INFO);

        return true;
    }

}
