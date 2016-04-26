<?php
/**
 * Created by PhpStorm.
 * User: gaoyaqiu
 * Date: 15/11/13
 * Time: 下午2:13
 */
namespace BlessingRedpack\Service;

use BlessingRedpack\Model\BlessingRedpackModel;
use BlessingRedpack\Model\BlessingRedpackLogModel;
use Common\Common\Cache;
use Common\Common\wepay\BlessingRedpackPay;
use Common\Common\WxqyMsg;
use Think\Exception;
use Think\Log;
use Common\Model;

class BlessingRedpackService extends AbstractService {

    // 缓存key前缀
    protected $_redis_key = null;

    // redis配置
    public $config_redis = array();

    // 构造方法
    public function __construct() {

        parent::__construct();

        //实例化模型
        $this->_d = D("BlessingRedpack/BlessingRedpack");

        $cache = &Cache::instance();
        $setting = $cache->get('Common.setting');
        $this->_redis_key = $setting['domain'];

        $this->config_redis = array(
            'host'=> cfg('REDIS_HOST'),
            'port'=> cfg('REDIS_HPORT'),
            'pwd' => cfg("REDIS_PWD")
        );


    }

    /**红包列表分页方法
     * @param $params
     * @param $page_option
     * @param $order_option
     */
    public function list_page($params, $page_option, $order_option){

        $total = $this->_d->count();

        $res_list = array();

        if($total > 0 ){
            $list = $this->_d->list_page($params, $page_option, $order_option);
            $log_model = D("BlessingRedpack/BlessingRedpackLog");
            $conds = array(
                'redpack_id' => ''
            );
            foreach($list as &$v){
                $conds['redpack_id'] = $v['id'];
                $array = $log_model->count_redpacksum_by_conds($conds);
                $v['times'] = $array['times'];
                $v['remainder'] = $v['total'] - $array['timesmoney'];
            }
            $res_list[] = $list;
            $res_list[] = $total;
        }else{
            return array($res_list, 0);
        }

        return $res_list;
    }

    /**格式化数据
     * @param array $redpack
     * @return array
     */
    public function format($redpack) {

        // 时间字段
        $time_fields = array('starttime', 'endtime', 'created', 'updated', 'deleted');
        foreach ($time_fields as $_key) {
            $redpack['_' . $_key] = rgmdate($redpack[$_key], 'Y-m-d H:i:s');
        }

        if (BlessingRedpackModel::TYPE_RAND == $redpack['type']) {
            $redpack['_type'] = '拼手气红包';
        } else if(BlessingRedpackModel::TYPE_AVERAGE == $redpack['type']){
            $redpack['_type'] = '普通红包';
        } else if(BlessingRedpackModel::TYPE_FREE == $redpack['type']){
            $redpack['_type'] = '自由红包';
        }

        //格式化红包总金额(单位:元)
        $redpack['_total'] = number_format($redpack['total'] / 100, 2);
        //格式化剩余红包金额(单位:元)
        $redpack['_remainder'] = number_format($redpack['remainder'] / 100, 2);
        //计算已领取红包金额
        $redpack['_received'] = number_format(($redpack['total'] - $redpack['remainder']) / 100, 2);
        //计算剩余红包数量
        $redpack['_remained_number'] = $redpack['redpacks'] - $redpack['times'];
        return $redpack;
    }

    public function add($params){

        Log::record("红包新增开始", Log::INFO);

        //系统当前时间戳
        $sysTimestamp = time();

        $type = $params['type'];
        $total = $params['total'];
        $startTime = $params['startTime'];
        $endTime = $params['endTime'];
        $single = $params['single'];
        $freeSum = $params['freeSum'];
        $freeTotal = $params['freeTotal'];

        /* 格式化开始时间、结束时间成时间戳 */
        $startTimestamp = rstrtotime($startTime);
        $endTimestamp = rstrtotime($endTime);

        //红包单个金额(只有固定金额的红包时，此字段为必填), 默认0, 单位: 分
        $params['money'] = 0;

        if($type == BlessingRedpackModel::TYPE_FREE){//自由红包
            //红包总数
            $params['redpacks'] = $freeSum;

            //红包总金额
            $total = $freeTotal;
            $params['total'] = $total;

            //生成红包
            $countRedpacks = $this->__count_redpack($total, $params['redpacks']);

            $params['count_redpacks'] = $countRedpacks;

            $params['memList'] = array();
            $params['departArr'] = array();

        }else if($type == BlessingRedpackModel::TYPE_RAND){//拼手气

            //封装领取对象
            $this->__count_mem_list($params);

            //红包总数
            $redpacks = count($params['mem_list']);
            $params['redpacks'] = $redpacks;

            //生成红包
            $countRedpacks = $this->__count_redpack($total, $redpacks);

            $params['count_redpacks'] = $countRedpacks;

        }else if($type == BlessingRedpackModel::TYPE_AVERAGE){//固定

            //封装领取对象
            $this->__count_mem_list($params);

            //红包总数
            $redpacks = count($params['mem_list']);
            $params['redpacks'] = $redpacks;

            //红包总金额
            $params['total'] = $redpacks * $single;

            //每个红包固定金额
            $money = $single;
            $params['money'] = $money;

            $params['count_redpacks'] = $redpacks;

        }else{//没有此红包类型
            E('_ERR_BLESSING_REDPACK_TYPE_ERROR');
            return false;
        }

        Log::record("红包总数：" . $params['redpacks'], Log::INFO);

        try{

            //如果当前时间已经大于开始时间，会直接推送消息，这里状态先插入已发送，
            //防止同一时间有定时任务也在运行，避免推送两次消息
            if($sysTimestamp > $startTimestamp){
                $params['msg_status'] = BlessingRedpackModel::MSG_SENT;
            }else{
                $params['msg_status'] = BlessingRedpackModel::MSG_NOSENT;
            }

            //开起事物
            $this->start_trans();

            //插入红包主表 oa_blessing_redpack
            $params['startTime'] = $startTimestamp;
            $params['endTime'] = $endTimestamp;
            $redpack_id = $this->__insert_blessing_redpack($params);
            if(!$redpack_id){
                E('_ERR_INSERT_ERROR');
                return false;
            }

            //插入红包明细表 oa_blessing_redpack_log
            $params['redpack_id'] = $redpack_id;

            $this->__inser_blessing_redpack_log($params);

            //插入红包部门关联表 oa_blessing_redpack_deprtment
            $this->__inser_blessing_redpack_department($params);

            //如果当前时间大于红包开始时间，放入缓存,推送红包消息
            if($sysTimestamp > $startTimestamp){

                //计算缓存有效期, 单位：秒，计算公式：剩余红包有效期 = 红包结束时间-系统当前时间
                $remainingTimestamp = $endTimestamp - $sysTimestamp;

                Log::record("放入缓存队列,redpack_id：" . $params['redpack_id'], Log::INFO);

                //先根据新增的红包主键，清空红包缓存旧数据，防止极端情况下（直接删除表等）导致缓存遗留重复key值的垃圾数据
                //$this->__delete_cache($redpack_id);

                //放入redis队列
                $this->init_redpack($params['redpack_id'], $params['redpacks'], $startTimestamp, $endTimestamp, $remainingTimestamp);

                //自由红包不主动推送消息
                if($type == BlessingRedpackModel::TYPE_FREE){
                    //提交事物
                    $this->commit();
                    Log::record("红包新增结束,自由红包不需要推送消息", Log::INFO);
                    return true;
                }

                Log::record('推送消息开始', Log::INFO);

                //推送消息
                $result = $this->__send_msg($params);

                if(empty($result) && !$result){
                    Log::record('推送消息失败', Log::ERR);
                    E('_ERR_BLESSING_REDPACK_SEND_ERROR');
                    return false;
                }

                Log::record('推送消息结束', Log::INFO);


            }else{
                //活动没有开始，放入定时任务表,使用crontab 定时任务处理缓存、推送消息
                Log::record("放入crontab表开始", Log::INFO);

                $cache = &Cache::instance();
                $setting = $cache->get('Common.setting');

                $client = &\Com\Rpc::phprpc(cfg('UCENTER_RPC_HOST') . '/OaRpc/Rpc/Crontab');

                //封装数据
                $params = array(
                    'domain' => $setting['domain'],
                    'type' => 'blessRedpack',
                    'ip' => '',
                    'runtime' => $startTimestamp,
                    'endtime' => '',
                    'looptime' => 60,
                    'times' => 3,
                    'runs' => 0,
                    'taskid' => md5('blessRedpack'. $redpack_id)
                );
                $result = $client->Add($params);

                Log::record("放入crontab表结束，result-----" . $result, Log::INFO);
            }

            //提交事物
            $this->commit();

        }catch (\RedisException $redis){
            Log::record('红包新增redis异常', Log::ERR);
            Log::record($redis->getMessage(), Log::ERR);
            $this->rollback();
            E('_ERR_INSERT_ERROR');
            return false;
        }catch (\Exception $e){
            $this->rollback();
            E('_ERR_INSERT_ERROR');
            return false;
        }

        Log::record("红包新增结束", Log::INFO);

        return true;

    }

    /**
     * 分配次插入log表
     * @param $size
     * @param $array
     */
    private function __batch_insert($size, $array){
        //var_dump($array);
        $blessing_redPack_log_model = D('BlessingRedpack/BlessingRedpackLog');
        //每次插入100条
        $speed = 100;
        $batch_count = 0;
        do{

            // 开始索引
            $from = $batch_count * $speed;
            $batch_count++;
            // 结束索引
            $to = $batch_count * $speed;
            if($to > $size) {
                $to = $size;
            }
            $tmp = array_slice($array, $from, $speed, true);
            $tmp = array_values($tmp);

            if(!$res_id = $blessing_redPack_log_model->insert_all($tmp)){
                E('_ERR_INSERT_ERROR');
                return false;
            }
            //unset($tmp);
        } while ($size > ($speed * $batch_count));
    }

    /**
     * 去重
     * @param $arr
     * @param $key
     */
    private function __assoc_unique(&$arr, $key)
    {
        $rAr=array();
        for($i=0;$i<count($arr);$i++)
        {
            if(!isset($rAr[$arr[$i][$key]]))
            {
                $rAr[$arr[$i][$key]]=$arr[$i];
            }
        }
        $arr=array_values($rAr);
    }



    /**
     * 红包明细放入队列
     * @param $redpack_id 红包主键
     * @param $total 红包总数
     * @param $startTime 红包活动开始时间
     * @param $endTime 红包活动截止时间
     * @param $time 有效期 分钟
     */
    public function init_redpack($redpack_id, $total, $startTime, $endTime, $time){


        $redis = \Common\Common\RedisUtil\RedisClient::getInstance($this->config_redis);

        // 获取红包有效期
        $blessing_log_model = D('BlessingRedpack/BlessingRedpackLog');
        $conds = array(
            'redpack_id' => $redpack_id
        );
        //数据量大时，后期可优化分批次查询
        $redpack_log_arry = $blessing_log_model->list_by_conds($conds);

        //优化批量放入redis
        $pipe = $redis->multi(\Redis::PIPELINE);
        foreach ($redpack_log_arry as $_v) {
            $tmp_arry = array(
                "rid" => $_v['id'],
                "money" => $_v['money']
            );
            // 先序列化后在存入redis
            // 存入红包明细缓存
            $pipe->rPush($this->_redis_key . self::REDPACK_QUEUE_KEY.$redpack_id, serialize($tmp_arry));
            // 设置队列红包明细有效期
            $pipe->expire($this->_redis_key . self::REDPACK_QUEUE_KEY.$redpack_id, $time);

        }
        $pipe->exec();

        // 存入红包总数缓存
        $param = array(
            'total' => $total,
            'starttime' => $startTime,
            'endtime' => $endTime
        );

        $redis->set($this->_redis_key . self::REDPACK_SUM_KEY.$redpack_id, serialize($param), $time);


    }


    /**
     * 红包详情
     * @param $id 主键
     * @return mixed
     */
    public function get_redpack($id){

        $data = $this->_d->get($id);

        if(empty($data)){
            E('_ERR_BLESSING_REDPACK_DELETE_ERROR');
            return false;
        }

        $conds = array(
            'redpack_id' => $id
        );
        $log_model = D("BlessingRedpack/BlessingRedpackLog");
        $array = $log_model->count_redpacksum_by_conds($conds);
        $data['times'] = $array['times'];
        $data['remainder'] = $data['total'] - $array['timesmoney'];

        return $data;
    }


    /**
     * 处理领取对象，去重
     * @param $redpack
     * @return array
     */
    private function __count_mem_list(&$redpack){

        $mem_list = array();
        $member_Model = D("Common/Member");
        if($redpack['allCompany'] == BlessingRedpackLogModel::ALL_COMPANY){//全公司
            // 获取部门缓存数据
            $cache = &Cache::instance();
            $cache_departments = $cache->get('Common.department');
            foreach ($cache_departments as $_dp) {
                $departArr[] = $_dp['cd_id'];
                $tmp[0] = $_dp['cd_id'];
                //查询部门下所有人员

                $members = $member_Model->list_by_cdid_kws($tmp);
                // 遍历取人员信息
                foreach ($members as $_mem) {
                    $mem_list[] = array(
                        'm_uid' => $_mem['m_uid'],
                        'm_username' => $_mem['m_username']
                    );
                }
            }

        }else if($redpack['allCompany'] == BlessingRedpackLogModel::SPECIFIED){
            $all_company = true;
            /*取出所有部门下的所有人员*/
            $specifiedHiddenObj = $redpack['specifiedHiddenObj'];
            $departmentArr = $specifiedHiddenObj['departments'];
            $users = $specifiedHiddenObj['users'];
            //如果领取人员不为空
            if(!empty($users)) {
                $users_bool = true;
                foreach ($users as $uk => $uv) {
                    $mem_list[] = array(
                        'm_uid' => $uv['m_uid'],
                        'm_username' => $uv['m_username']
                    );
                }
            }

            //如果选择了领取部门
            if(!empty($departmentArr)){
                $department_bool = true;
                //所有部门ID
                foreach($departmentArr as $k => $v){
                    $departArr[] = $v['id'];
                    $tmp[0] = $v['id'];
                    //查询部门下所有人员
                    $members = $member_Model->list_by_cdid_kws($tmp);
                    // 遍历取人员信息
                    foreach ($members as $_mem) {
                        $mem_list[] = array(
                            'm_uid' => $_mem['m_uid'],
                            'm_username' => $_mem['m_username']
                        );
                    }
                }
            }


        }

        //去重
        self::__assoc_unique($mem_list,'m_uid');

        $redpack['mem_list'] = $mem_list;
        $redpack['departArr'] = $departArr;

        return true;
    }


    /**
     * 生成红包
     * @param $total 红包总金额
     * @param $redpacks 红包总数
     * @return bool
     */
    private function __count_redpack($total, $redpacks){

        Log::record("生成红包开始" . "总金额：" . $total . "总人数：" . $redpacks, Log::INFO);

        //从缓存的setting表中取出最小额、最大额
        $cache = &Cache::instance();
        $cache_bless_setting = $cache->get('BlessingRedpack.setting');
        $bonus_min = $cache_bless_setting['redpack_min'];
        $bonus_max = $cache_bless_setting['redpack_max'];

        /**
         * 算法生成红包时，如果红包总金额不满足每个红包都不小于配置的最小红包金额，
         * 或者每个人的最大金额不能大于配置的单个红包最大金额
         */
        //红包总数 * 单个红包最小金额大于红包总金额
        $tmpMoney = $redpacks * $bonus_min;
        $total = $total * 100;
        if($tmpMoney > $total){
            E('_ERR_BLESSING_REDPACK_GENERATE_ERROR');
            return false;
        }
        //红包总数 * 单个红包最大额小于红包总金额
        $tmpMoney = $total / $redpacks ;
        if($tmpMoney > $bonus_max){
            E('_ERR_BLESSING_REDPACK_MONEY_FAILD');
            return false;
        }

        try{
            //生成红包：红包总额, 红包个数, 每个小红包的最大额, 每个小红包的最小额
            $redpack_arr = BlessingRedpackPay::get_bonus($total, $redpacks, $bonus_max, $bonus_min);
        }catch (\Exception $e){
            E('_ERR_BLESSING_REDPACK_GENERATE_FAILD');
            return false;
        }

        Log::record("生成红包结束", Log::INFO);

        return $redpack_arr;
    }

    /**
     * 插入红包主表
     * @param $redpack 插入数据
     * @return bool|int|mixed
     */
    private function __insert_blessing_redpack($redpack){

        Log::record("插入红包主表开始", Log::INFO);

        //祝福人
        $persons = array();
        //祝福语
        $content = array();
        //序列化祝福人、祝福语
        $blessHiddenObj = $redpack['blessHiddenObj'];
        foreach($blessHiddenObj as $k => $v){
            $t = $v[0];
            $persons[] = array(
                'm_uid' => $t['m_uid'],
                'm_username' => $t['m_username']
            );
            $content[] = $v[0]['content'];
        }

        //红包发送人默认取第一个祝福人
        $sendname = $blessHiddenObj['bless_person_0'][0];

        /*用户没有上传背景图，使用系统默认*/
        $imgReceiveBg = $redpack['imgReceiveBg'];
        $imgChatBg = $redpack['imgChatBg'];
        if(empty($imgReceiveBg)){
            $imgReceiveBg = 0;
        }
        if(empty($imgChatBg)){
            $imgChatBg = 0;
        }

        //封装数据
        $bless_redpack_arr = array(
            'm_uid' => $redpack['login_uId'],//红包创建用户uid
            'm_username' => $redpack['login_username'],//红包创建用户名
            'actname' => $redpack['actname'],//活动主题
            'type' => $redpack['type'],//红包类型
            'money' => $redpack['money'] * 100,//红包单个金额(只有固定金额的红包时，此字段为必填), 单位: 分
            'total' => $redpack['total'] * 100,//红包活动总金额, 单位: 分
            'remainder' => $redpack['total'] * 100,//剩余红包总额, 单位: 分
            'redpacks' => $redpack['redpacks'],//红包总数
            'times' => 0,//红包已被领取个数
            'starttime' => $redpack['startTime'],//红包开始时间
            'endtime' => $redpack['endTime'],//活动结束时间
            'sendname' => $sendname['m_username'],//红包发送者名称,默认第一个祝福人
            'wishing' => $redpack['wishing'],//红包内容
            'persons' => serialize($persons),//祝福人
            'content' => serialize($content),//祝福语
            'receive_bg' => $imgReceiveBg,//领取页面背景图id
            'chat_bg' => $imgChatBg,//分享后的群聊页面背景图id
            'invite_content' => $redpack['inviteContent'],//邀请语
            'msg_status' => $redpack['msg_status'] //消息推送状态：0-未推送；1-已推送 注：自由红包类型默认为已推送
        );
        $id = 0;
        if(!$id = $this->_d->insert($bless_redpack_arr)){
            E('_ERR_INSERT_ERROR');
            return false;
        }

        Log::record("插入红包主表结束", Log::INFO);

        return $id;
    }


    /**
     * 插入红包明细表
     * @param $redpackLog 插入数据
     * @return bool
     */
    public function __inser_blessing_redpack_log($redpackLog){

        Log::record("插入红包明细表开始", Log::INFO);

        $bless_redpack_log_arr = array();

        //红包总数
        $redpacks = $redpackLog['redpacks'];

        //生成的红包总数
        $countRedpacks = count($redpackLog['count_redpacks']);

        //生成的红包数组
        $countRedpacksArr = $redpackLog['count_redpacks'];

        //如果是随机红包，且红包总数与算法随机生成的红包总数不一致
        if(($redpackLog['type'] == BlessingRedpackModel::TYPE_RAND) && ($redpacks != $countRedpacks)){
            E('_ERR_REDPACK_ERROR');
            return false;
        }

        //封装数据
        for($i=0; $i<$redpacks; $i++ ){
            $bless_redpack_log_arr[] = array(
                'redpack_id' => $redpackLog['redpack_id'],
                'redpack_status' => BlessingRedpackLogModel::REDPACK_WAIT,
                'money' => $redpackLog['money'] ? $redpackLog['single'] * 100 : $countRedpacksArr[$i]//固定金额或随机金额
            );
        }

        self::__batch_insert($redpacks, $bless_redpack_log_arr);

        Log::record("插入红包明细表结束", Log::INFO);

        return true;
    }


    /**
     * 插入红包部门表
     * @param $redpackDepartment
     * @return bool
     */
    private function __inser_blessing_redpack_department($redpackDepartment){

        Log::record("插入红包部门关联表开始", Log::INFO);

        $blessing_redPack_department_model = D('BlessingRedpack/BlessingRedpackDepartment');

        $depIdSerialize = 0;
        $allCompany = $redpackDepartment['allCompany'];
        $memList = $redpackDepartment['mem_list'];
        $departArr = $redpackDepartment['departArr'];

        if($redpackDepartment['type'] != BlessingRedpackModel::TYPE_FREE){//自由红包没有领取对象

            if($allCompany){
                //序列化部门
                if(!empty($departArr)){
                    $depIdSerialize = serialize($departArr);
                }
            }

            foreach ($memList as $uk => $uv) {
                $receive_uid_arr[] = $uv['m_uid'];
                $receive_uname__arr[] = $uv['m_username'];
            }
            //封装数据
            $bless_redpack_department_arr = array(
                'redpack_id' => $redpackDepartment['redpack_id'],
                'dep_id' => $depIdSerialize,
                'receive_uid' => $allCompany ? serialize($receive_uid_arr) : 0,
                'receive_uname' => $allCompany ? serialize($receive_uname__arr) : 0
            );
            if(!$dept_id = $blessing_redPack_department_model->insert($bless_redpack_department_arr)){
                E('_ERR_INSERT_ERROR');
                return false;
            }
        }

        Log::record("插入红包部门关联表结束", Log::INFO);

        return true;
    }

    /**
     * 发送消息
     * @param $redpack
     * @return bool
     */
    private function __send_msg($redpack) {

        Log::record('红包消息推送开始(service)------红包ID：' . $redpack['redpack_id'] . '领取对象:' . $redpack['mem_list'], Log::INFO);

        try{
            $cache = &Cache::instance();
            $wxqyMsg = WxqyMsg::instance();
            $cache_setting = $cache->get('Common.setting');
            $cache_bless_setting = $cache->get('BlessingRedpack.setting');

            $url = 'http://' . $cache_setting['domain'] . $cache_bless_setting['redpack_url'] . '?id=' . $redpack['redpack_id'] . '&title=' . $redpack['actname'];
            $desc = "\n" . $redpack['inviteContent'];

            //如果给全公司发送
            if (!$redpack['allCompany']) {
                $uids = '@all';
                $result = $wxqyMsg->send_news($redpack['actname'], $desc, $url, $uids, '', '', $cache_bless_setting['agentid'], $cache_bless_setting['pluginid']);
                Log::record('红包消息推送全公司结束(service),result-------' . $result, Log::INFO);
                return true;
            }

            /*推送指定人员*/
            foreach ($redpack['mem_list'] as $v) {
                $uids[] = $v['m_uid'];
            }

            //每次插入100条
            $speed = 100;
            $batch_count = 0;
            $size = count($uids);
            do {

                $from = $batch_count * $speed;
                $batch_count++;
                $to = $batch_count * $speed;
                if ($to > $size) {
                    $to = $size;
                }

                $tmp = array_slice($uids, $from, $speed, true);

                $result = $wxqyMsg->send_news($redpack['actname'], $desc, $url, $tmp, '', '', $cache_bless_setting['agentid'], $cache_bless_setting['pluginid']);

                Log::record('红包消息推送指定人员结果(service),result-------' . $result, Log::INFO);

            } while ($size > ($speed * $batch_count));

        }catch (\Exception $e){
            Log::record('红包消息推送(service)异常：', Log::ERR);
            Log::record($e->getMessage(), Log::ERR);
            return false;
        }

        return true;
    }


    /**
     * 清空红包缓存旧数据，防止极端情况下删除表遗留垃圾数据
     * @param $redpackId
     * @return bool
     */
    private function __delete_cache($redpackId) {

        if(!empty($redpackId)){

            $redis = \Common\Common\RedisUtil\RedisClient::getInstance($this->config_redis);

            $t1 = $redis->get($this->_redis_key.AbstractService::REDPACK_SUM_KEY.$redpackId);
            if($t1){
                // 如果缓存中有旧数据就需要清空当前红包缓存
                // 清除主表cache
                $redis->delKey($this->_redis_key.AbstractService::REDPACK_SUM_KEY.$redpackId);

                // 清除队列key
                $redis->delKey($this->_redis_key.AbstractService::REDPACK_QUEUE_KEY.$redpackId);

                // 清除已抢到该红包的用户cache
                $tmp = $redis->keys($this->_redis_key.AbstractService::REDPACK_USER_KEY.$redpackId.'_');
                foreach ($tmp as $value) {
                    $redis->delKey($value);
                }
            }
        }

        Log::record("delete_cache ok", Log::INFO);

        return true;
    }

}
