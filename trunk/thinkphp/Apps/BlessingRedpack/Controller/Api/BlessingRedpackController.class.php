<?php
/**
 * Created by PhpStorm.
 * User: gaoyaqiu
 * Date: 15/11/13
 * Time: 下午2:37
 */
namespace BlessingRedpack\Controller\Api;

use BlessingRedpack\Service\AbstractService;
use BlessingRedpack\Model\BlessingRedpackLogModel;
use Common\Common\Cache;
use Common\Common\wepay;
use Common\Common\Wxqy\Service;
use Think\Log;
use Common\Common\RedisUtil\RedisClient;

class BlessingRedpackController extends AbstractController {
    // 实例化数据库
    protected $_serv_redpack = null;
    // 祝福语中职位标识
    const JOB_TAG = "[jobTag]";
    // 祝福语中姓名标识
    const USER_NAME_TAG = "[userNameTag]";
    //祝福语中姓名标识
    const DEPARTMENT_TAG = "[departmentTag]";

    // redis配置
    public $config_redis = array();
    // 缓存key前缀
    protected $_redis_key = null;

    public function before_action($action) {
        if (in_array($action, array('Share', 'Detail', 'GetShareSign', 'test', 'initRedpack'))) {
            $this->_require_login = false;
        }

        if(!parent::before_action()){
            return false;
        }

       $this->config_redis = array(
            'host'=> cfg('REDIS_HOST'),
            'port'=> cfg('REDIS_HPORT'),
            'pwd' => cfg("REDIS_PWD")
        );

        $this->_redis_key = $this->_setting['domain'];

        return true;
    }


    /**
     * 单元测试
     */
    public function test_get(){

//
//        $ $this->config_redis = array(
//            'host'=> cfg('REDIS_HOST'),
//            'port'=> cfg('REDIS_HPORT')
//        );
//
//        $redis = RedisClient::getInstance($this->config_redis);
//
//        $t1 = $redis->get("gao");
//        if($t1){
//            // 如果缓存中有旧数据就需要清空当前红包缓存
//
//            $redis->delKey("gao");
//
//            echo "ok";
//        }



        exit;

    }

    /**
     * 更新红包有效期(慎重使用)
     */
    public function update_cache_time()
    {

        // 将已抢过的红包用户，加入缓存访问
        $redpack_id = 0;

        if(!empty($redpack_id)){
            $_params = array(
                "id" => $redpack_id
            );
            $_serv_redpack_log = D('BlessingRedpack/BlessingRedpackLog', 'Service');
            $page_option = array(1, 9999);
            $redpack_list = $_serv_redpack_log->list_receive_for_update_time($_params, $page_option, array());
            if(count($redpack_list) > 0){
                $redis = RedisClient::getInstance($this->config_redis);

                foreach ($redpack_list[0] as $_k => $_v) {

                    // 保存抢到的红包信息到缓存(有效期为1天)
                    $tmp_arry = array(
                        "rid" => $_v['id'],
                        "money" => $_v['money'],
                        "ranking" => $_v['ranking'] ? $_v['ranking'] : 0,
                        "status" => $_v['redpack_status']
                    );
                    // 为了区分用户抢的红包是在不同活动下的，需要对key做特殊处理
                    $redis->set($this->_redis_key.AbstractService::REDPACK_USER_KEY.$redpack_id.'_'.$_v['m_uid'], serialize($tmp_arry), 3600 * 24);
                }
            }
        }
        echo "update_cache_time ok";
        exit;
    }

    /**
     * 清空红包缓存，用于初始化数据(慎重使用)
     */
    public function delete_cache()
    {

        $redpack_id = 0;

        if(!empty($redpack_id)){

            $redis = RedisClient::getInstance($this->config_redis);

            $t1 = $redis->get($this->_redis_key.AbstractService::REDPACK_SUM_KEY.$redpack_id);
            if($t1){
                // 如果缓存中有旧数据就需要清空当前红包缓存
                // 清除主表cache
                $redis->delKey($this->_redis_key.AbstractService::REDPACK_SUM_KEY.$redpack_id);

                // 清除队列key
                $redis->delKey($this->_redis_key.AbstractService::REDPACK_QUEUE_KEY.$redpack_id);

                // 清除已抢到该红包的用户cache
                $tmp = $redis->keys($this->_redis_key.AbstractService::REDPACK_USER_KEY.$redpack_id.'_');
                foreach ($tmp as $value) {
                    $redis->delKey($value);
                }
            }
        }
        echo "delete_cache ok";
        exit;
    }


    /**
     * 初始化红包明细队列 （小心测试使用）
     */
    public function initRedpack_get(){

        $redpack_id = 0;
        if(!empty($redpack_id)){
            $redis = RedisClient::getInstance($this->config_redis);
            // 清除old缓存数据
            $redis->delKey($this->_redis_key.AbstractService::REDPACK_QUEUE_KEY.$redpack_id);

            // 获取红包明细
            $_serv_redpack_log = D('BlessingRedpack/BlessingRedpackLog', 'Service');
            $_model_redpack_log = D('BlessingRedpack/BlessingRedpackLog');
            $_params = array(
                "redpack_id" => $redpack_id,
                "redpack_status" => 9
            );
            // 查询待抢的红包
            $redpack_list = $_serv_redpack_log->list_by_conds($_params);
            $sum = count($redpack_list);
            Log::record('待抢红包数: ' . $sum, Log::INFO);
            // 获取红包有效期
            $this->_serv_redpack = D('BlessingRedpack/BlessingRedpack', 'Service');
            $data = $this->_serv_redpack->get($redpack_id);
            // 计算缓存有效期
            $time = $data['endtime'] - time();
            Log::record('红包有效期'. $time, Log::INFO);
            // 有待抢红包
            if($sum > 0){
                foreach ($redpack_list as $_k => $_v) {
                    // 只把没抢的红包放入队列，待抢
                    $tmp_arry = array(
                        "rid" => $_v['id'],
                        "money" => $_v['money']
                    );
                    // 先序列化后在存入redis
                    // 存入红包明细缓存
                    $redis->rPush($this->_redis_key.AbstractService::REDPACK_QUEUE_KEY.$redpack_id, serialize($tmp_arry));
                    // 设置队列红包明细有效期
                    $redis->expire($this->_redis_key.AbstractService::REDPACK_QUEUE_KEY.$redpack_id, $time);
                }
            }

            // 存入红包基础缓存
            $tmp_redpack = array(
                'total' => $data['redpacks'],
                'starttime' => $data['starttime'],
                'endtime' => $data['endtime']
            );

            $redis->set($this->_redis_key.AbstractService::REDPACK_SUM_KEY.$redpack_id, serialize($tmp_redpack), $time);

            Log::record('存入红包基础缓存: '. $redpack_id, Log::INFO);

            // 将已抢过的红包用户，加入缓存访问
            $_params = array(
                "id" => $redpack_id
            );
            $page_option = array(0, 99999);
            // 查询已抢红包领明细列表
            $redpack_list = $_serv_redpack_log->list_receive_for_update_time($_params, $page_option, array());
            if(count($redpack_list) > 0){
                Log::record('开始放入已抢红包缓存', Log::INFO);

                foreach ($redpack_list[0] as $_k => $_v) {
                    // 保存抢到的红包信息到缓存(有效期为1天)
                    $tmp_arry = array(
                        "rid" => $_v['id'],
                        "money" => $_v['money'],
                        "ranking" => $_v['ranking'] ? $_v['ranking'] : 0,
                        "status" => $_v['redpack_status']
                    );
                    // 为了区分用户抢的红包是在不同活动下的，需要对key做特殊处理
                    $redis->delKey($this->_redis_key.AbstractService::REDPACK_USER_KEY.$redpack_id.'_'.$_v['m_uid']);
                    $redis->set($this->_redis_key.AbstractService::REDPACK_USER_KEY.$redpack_id.'_'.$_v['m_uid'], serialize($tmp_arry), $time);
                }
            }
            Log::record('初始化红包缓存成功', Log::INFO);
        }
        return true;
    }

    /**
     * 抢红包接口
     */
    public function Robbed_get(){
        // 红包活动id
        $id = $this-> _check_params();

        // 先查看红包缓存中是否有红包信息
        $redis = RedisClient::getInstance($this->config_redis);
        // 验证当前活动是否开始
        $redpack_cache = $redis->get($this->_redis_key.AbstractService::REDPACK_SUM_KEY.$id);
        if(empty($redpack_cache)){
            Log::record("当前红包活动id为: [".$id."], 缓存有效期已过", Log::INFO);
            // 红包已失效
            E('_ERR_BLESSING_REDPACK_IS_EMPTY');
            return true;
        }

        $redpack_cache = unserialize($redpack_cache);
        $redpack_starttime = $redpack_cache['starttime'];
        $redpack_endtime = $redpack_cache['endtime'];
        $redpack_sum = $redpack_cache['total'];
        $cur_time = time();

        if($cur_time < $redpack_starttime){
            // 活动尚未开始
            E('_ERR_BLESSING_REDPACK_IS_NOT_START');
            return true;
        }

        // 实例化
        $this->_serv_redpack = D('BlessingRedpack/BlessingRedpack', 'Service');
        // 得到当前用户信息
        $_user = $this->_login->user;
        $uid = $_user['m_uid'];
        // 验证当前用户是否已抢过红包
        $redpack_user = $redis->get($this->_redis_key.AbstractService::REDPACK_USER_KEY.$id.'_'.$uid);

        if(!$redpack_user){
            // 对于没抢过红包的用户，需要验证活动是否已过期，已抢过红包的用户则可以查看
            if($cur_time > $redpack_endtime){
                // 红包活动已过期
                E('_ERR_BLESSING_REDPACK_IS_EXPIRED');
                return true;
            }

            // 验证红包是否抢完
            $length =  $redis->lLen($this->_redis_key.AbstractService::REDPACK_QUEUE_KEY.$id);
            if($length == 0){
                E('_ERR_BLESSING_REDPACK_IS_OVER');
                return true;
            }

            // 未抢过红包,  开始抢红包
            $serialize_redpack = $redis->lPop($this->_redis_key.AbstractService::REDPACK_QUEUE_KEY.$id);
            if(!empty($serialize_redpack)){
                $redpack_info = unserialize($serialize_redpack);
                // 红包明细id
                $redpack_id = $redpack_info['rid'];
                // 红包金额
                $redpack_money = $redpack_info['money'];

                // 记录抢红包排名
                $ranking = ($redpack_sum - $length) + 1;

                // 订单号
                $order_no = self::billno($uid);

                Log::record("当前红包活动id为: [".$id."],红包为: " . $redpack_id . "已排到: " .$ranking, Log::INFO);
                // 保存抢到的红包信息到缓存
                $tmp_arry = array(
                    "rid" => $redpack_id,
                    "money" => $redpack_money,
                    "ranking" => $ranking,
                    "status" => BlessingRedpackLogModel::REDPACK_OPEN,
                    "order_no" => $order_no
                );

                // 计算缓存有效期
                $time = $redpack_endtime - $cur_time;
                // 为了区分用户抢的红包是在不同活动下的，需要对key做特殊处理
                $redis->set($this->_redis_key.AbstractService::REDPACK_USER_KEY.$id.'_'.$uid, serialize($tmp_arry), $time);

                // 获取部门名称
                $cache = &Cache::instance();
                $cache_departments = $cache->get('Common.department');
                foreach ($cache_departments as $k => $v) {
                    if($k == $_user['cd_id']){
                        $dep_name = $v['cd_name'];
                    }
                }

                $update_data = array(
                    "m_uid" => $uid,
                    "m_username" => $_user['m_username'],
                    "dep_id" => $_user['cd_id'],
                    "dep_name" => $dep_name,
                    "money" => $redpack_money,
                    "ip" => get_client_ip(),
                    "redpack_status" => BlessingRedpackLogModel::REDPACK_OPEN,
                    "redpack_time" => time(),
                    "ranking" => $ranking,
                    "mch_billno" => $order_no
                );
                // 更新抢红包信息到红包明细表(为了优化访问速度，此操作可以放入异步方法处理)
                $_serv_redpack_log = D('BlessingRedpack/BlessingRedpackLog', 'Service');
                $_serv_redpack_log->update($redpack_id, $update_data);

                $tmp_array = self::get_first_blessing_user($id, $_user);
                $this->_result = $tmp_array;
            } else {
                // 红包已领完
                E('_ERR_BLESSING_REDPACK_IS_OVER');
                return true;
            }
        } else {

            // 已拆红包或者支付成功则直接显示红包金额和排名
            $redpack_user = unserialize($redpack_user);
            Log::record($_user['m_username']."已抢过红包, 当前状态: ". $redpack_user['status'], Log::INFO);
            if($redpack_user['status'] == BlessingRedpackLogModel::REDPACK_OK ||
                $redpack_user['status'] == BlessingRedpackLogModel::REDPACK_PAY_SUCCESS){

                $tmp_array = self::get_first_blessing_user($id, $_user);
                $tmp_array["num"] = $redpack_user["ranking"];
                $tmp_array["money"] = $redpack_user["money"];
                $this->_result = $tmp_array;
            } else {
                // 未拆红包处理
                if($redpack_user['status'] == BlessingRedpackLogModel::REDPACK_OPEN){
                    $tmp_array = self::get_first_blessing_user($id, $_user);
                    $this->_result = $tmp_array;
                }
            }
        }

        return true;
    }


    /**
     * 拆红包接口(只有已抢到红包的用户才可以拆)
     */
    public function Open_post(){
        $id = I('post.id');
        if(empty($id)){
            E('_ERR_BLESSING_ID_IS_EMPTY');
            return true;
        }

        // 超时等待时间
        $timeout = 5;

        // 得到当前用户信息
        $_user = $this->_login->user;
        Log::record("[".$_user['m_username']."]开始拆活动[".$id."]的红包", Log::INFO);
        $redis = new \Common\Common\RedisUtil\RedisLock($this->config_redis);
        $redpack_info = $redis->get($this->_redis_key.AbstractService::REDPACK_USER_KEY.$id.'_'.$_user['m_uid']);
        if(!$redpack_info){
            Log::record("[".$_user['m_username']."]拆活动[".$id."]的红包失败---活动有效期已过", Log::INFO);
            // 有效期已过
            E('_ERR_BLESSING_REDPACK_IS_EXPIRED');
            return true;
        }

        $redpack = unserialize($redpack_info);
        $rid = $redpack['rid'];

        // 未支付
        if($redpack['status'] != BlessingRedpackLogModel::REDPACK_PAY_SUCCESS){
            Log::record("[".$_user['m_username']."]拆活动[".$id."]的红包成功,开始支付", Log::INFO);

            // 加锁
//            if (!$redis->lock($this->_redis_key."redpack".$rid, $timeout)) {
//                Log::record("[".$_user['m_username']."]拆活动[".$id."]的红包失败---系统繁忙", Log::INFO);
//                // 系统繁忙, 等前一个用户操作完后，才可发起支付操作
//                E('_ERR_BLESSING_REDPACK_SYSTEM_BUSY');
//                return true;
//            }

            $pay = new wepay\BlessingRedpackPay();
            // 首先验证用户wx_openid
            if(empty($_user['pay_openid'])){
                // 获取pay_openid
                $pay_openid = $pay->get_wx_pay_openid($_user);
                if(empty($pay_openid)){
                    //解锁
                    $redis->unlock($this->_redis_key."redpack".$rid);
                    Log::record("[".$_user['m_username']."]拆活动[".$id."]的红包失败---获取openid失败", Log::INFO);
                    E('_ERR_WX_PAY_OPENID_IS_NULL');
                    return true;
                }
                // 更新用户支付的openid
                $up_data = array(
                    'pay_openid' => $pay_openid
                );
                $serv_mem = D('Common/Member', 'Service');
                $serv_mem->update($_user['m_uid'], $up_data);

            }else{
                $pay_openid = $_user['pay_openid'];
            }

            // 发起微信支付操作 check_name参数选项备注：
            //NO_CHECK：不校验真实姓名
            //FORCE_CHECK：强校验真实姓名（未实名认证的用户会校验失败，无法转账）
            ///OPTION_CHECK：针对已实名认证的用户才校验真实姓名（未实名认证用户不校验，可以转账成功）
            $options = array(
                'openid' => $pay_openid,
                'check_name' => "NO_CHECK",
                're_user_name' => $_user['m_username'],
                'amount' => $redpack['money'],
                'desc' => "红包活动",
                'spbill_create_ip' => get_client_ip(),
                'partner_trade_no' => $redpack['order_no']
            );

            if($pay->send($options, $send_result, $redis, $rid)){
                // 支付成功
                // 更新缓存中拆红包状态为已支付
                $redpack['status'] = BlessingRedpackLogModel::REDPACK_PAY_SUCCESS;
                $ttl = $redis->ttl($this->_redis_key.AbstractService::REDPACK_USER_KEY.$id.'_'.$_user['m_uid']);
                $redis->set($this->_redis_key.AbstractService::REDPACK_USER_KEY.$id.'_'.$_user['m_uid'], serialize($redpack), $ttl);

                $_serv_redpack_log = D('BlessingRedpack/BlessingRedpackLog', 'Service');

                $update_data = array(
                    "redpack_status" => BlessingRedpackLogModel::REDPACK_OK,
                    "payment_no" => $send_result['payment_no'],
                    'return_code' => $send_result['return_code'], //返回状态码
                    'return_msg' => $send_result['return_msg'], //返回信息
                    'result_code' => $send_result['result_code'], //业务结果
                    'err_code' => $send_result['err_code'], //错误代码
                    'err_code_des' => $send_result['err_code_des'] //错误代码描述
                );

                $_serv_redpack_log->update($redpack['rid'], $update_data);
                // 更新红包明细表
                if($_serv_redpack_log === false) {
                    Log::record("[".$_user['m_username']."]拆活动[".$id."]的红包更新红包明细表失败", Log::INFO);
                }
                Log::record("[".$_user['m_username']."]拆活动[".$id."]的红包支付成功", Log::INFO);
            }else{
                //解锁
                $redis->unlock($this->_redis_key."redpack".$rid);
                // 支付失败
                Log::record("[".$_user['m_username']."]拆活动[".$id."]的红包支付失败", Log::INFO);
                E('_ERR_WX_PAY_ERROR');
                return true;
            }
        }else{
            Log::record("[".$_user['m_username']."]拆活动[".$id."]的红包失败---已支付成功", Log::INFO);
        }

        // 本地测试放开 begin
//        $redpack['status'] = BlessingRedpackLogModel::REDPACK_PAY_SUCCESS;
//        $ttl = $redis->ttl($this->_redis_key.AbstractService::REDPACK_USER_KEY.$id.'_'.$_user['m_uid']);
//        $redis->set($this->_redis_key.AbstractService::REDPACK_USER_KEY.$id.'_'.$_user['m_uid'], serialize($redpack), $ttl);

//        $update_data = array(
//            "redpack_status" => BlessingRedpackLogModel::REDPACK_OK,
//            "ranking" => $redpack['ranking']
//        );
//        $_serv_redpack_log = D('BlessingRedpack/BlessingRedpackLog', 'Service');
//        $_serv_redpack_log->update($redpack['rid'], $update_data);
        // 本地测试放开 end

        //解锁
        $redis->unlock($this->_redis_key."redpack".$rid);

        // 根据支付返回状态，需做异常处理，待续
        $this->_result = array(
            'id' => $id,
            'uid' => $_user['m_uid'],
            'num' => $redpack['ranking'],
            'money' => $redpack['money']
        );

        return true;
    }



    /**
     * 获取第一祝福人相关信息
     * @param $id 红包活动id
     * return array
     */
    public function get_first_blessing_user($id, $_user){
        $data = $this->_serv_redpack->get($id);
        $cache = &Cache::instance();

        $setting = $cache->get('Common.setting');
        // 拼接图片相对路径,如果没有上传背景图，返回0,前段取默认图片
        if($data['receive_bg'] == 0){
            $receive_bg = '';
        }else{
            $receive_bg = "http://" . $setting['domain'] . '/attachment/read/'.$data['receive_bg'];
        }

        $uid = current(unserialize($data['persons']));
        $blessing_uid =  $uid['m_uid'];
        // 获取第一个祝福人头像
        $face = \Common\Common\User::instance()->avatar($blessing_uid);
        $uname =  current(unserialize($data['persons']));
        $uname =  $uname['m_username'];

        // 祝福语
        $content = unserialize($data['content']);
        $cj_id = $_user['cj_id'];
        $job_ser = D('Common/CommonJob', 'Service');
        $job = $job_ser->get($cj_id);
        $content_res = $content[0];
        if(!empty($job)){
            $job_name = $job['cj_name'];
            if(!empty($job_name)){
                $content_res = str_replace(self::JOB_TAG, $job_name, $content[0]);
            }else{
                $content_res = str_replace(self::JOB_TAG, '', $content[0]);
            }
        }else{
            $content_res = str_replace(self::JOB_TAG, '', $content[0]);
        }

        // 替换userNameTag
        $userName = $_user['m_username'];
        if(empty($userName)){
            $content_res = str_replace(self::USER_NAME_TAG, '', $content_res);
        }else{
            $content_res = str_replace(self::USER_NAME_TAG, $userName, $content_res);
        }

        // 替换departmentTag
        $department_all = $cache->get('Common.department');
        $cd_id = $_user['cd_id'];
        $department = $department_all[$cd_id];

        $departmentName = $department['cd_name'];
        if(empty($departmentName)){
            $content_res = str_replace(self::DEPARTMENT_TAG, '', $content_res);
        }else{
            $content_res = str_replace(self::DEPARTMENT_TAG, $departmentName, $content_res);
        }

        return array(
            'id' => $id,
            'uid' => $_user['m_uid'],
            'title' => $data['actname'],
            'uname' => $uname,
            'face' => $face,
            'content' => $content_res,
            'bgUrl' => $receive_bg
        );
    }


    /**
     * 锁屏数据显示接口
     * @return bool
     */
    public function Lock_get() {

        $id = $this-> _check_params();

        // 实例化
        $this->_serv_redpack = D('BlessingRedpack/BlessingRedpack', 'Service');
        // 根据条件查询数据
        $data = $this->_serv_redpack->get($id);
        if(!empty($data)){
            // 获取第一个祝福人名称及邀请语
            $uname =  current(unserialize($data['persons']));
            $uname =  $uname['m_username'];
            $invite = $data['invite_content'];

            $this->_result = array(
                'id' => $data['id'],
                'title' => $data['actname'],
                'uname' => $uname,
                'invite' =>  $invite
            );
        }

        return true;
    }

    /**
     * 群聊祝福语查询接口
     * @return bool
     */
    public function Blessings_get() {
        $id = $this-> _check_params();

        // 实例化
        $this->_serv_redpack = D('BlessingRedpack/BlessingRedpack', 'Service');
        // 根据条件查询数据
        $data = $this->_serv_redpack->get($id);
        if(!empty($data)){
            // 获取第一个祝福人名称
            $uname =  current(unserialize($data['persons']));
            $uname =  $uname['m_username'];

            $data = $this->_serv_redpack->get($id);
            $cache = &Cache::instance();
            $setting = $cache->get('Common.setting');
            //拼接图片相对路径,如果没有上传背景图，返回0,前段取默认图片
            if($data['receive_bg'] == 0){
                $chat_bg = '';
            }else{
                $chat_bg = "http://" . $setting['domain'] . '/attachment/read/'.$data['receive_bg'];
            }

            // 参与人数组
            $persons = unserialize($data['persons']);
            foreach($persons as $values){
                // 取用户名称
                $res_persons[] = $values['m_username'];
                // 取用户头像
                $uids[] = $values['m_uid'];
            }

            $serv_mem = D('Common/Member', 'Service');
            $users = $serv_mem->list_by_pks($uids);
            \Common\Common\User::instance()->push($users);

            // 祝福语数组
            $content = unserialize($data['content']);
            $blessings = array();

            // 获取祝福人名称数组
            $this->_get_content($persons, $content, $blessings, '');

            $this->_result = array(
                'id' => $data['id'],
                'title' => $data['actname'],
                'uname' => $uname,
                "rpConten" => $data['wishing'],
                "bgUrl" => $chat_bg,
                "persons" => $res_persons,
                "blessings" => $blessings
            );
        }else{
            E('_ERR_BLESSING_REDPACK_IS_EMPTY');
            return true;
        }

        return true;
    }


    /**
     * 查看分享红包接口
     * @return bool
     */
    public function Share_get() {

        $id = $this-> _check_params();

        $uid = I('get.uid');
        if(empty($uid)){
            E('_ERR_BLESSING_UID_IS_EMPTY');
            return true;
        }

        // 实例化
        $this->_serv_redpack = D('BlessingRedpack/BlessingRedpack', 'Service');
        // 根据条件查询数据
        $data = $this->_serv_redpack->get($id);
        if(!empty($data)){
            // 获取用户抢到的红包金额
            $_serv_redpack_log = D('BlessingRedpack/BlessingRedpackLog', 'Service');
            $_model_redpack_log = D('BlessingRedpack/BlessingRedpackLog');
            $_params = array(
                "redpack_id" => $id,
                "m_uid" => $uid,
                "status" => $_model_redpack_log::ST_CREATE
            );

            $money = $_serv_redpack_log->get_by_conds($_params);
            if(empty($money)){
                E('_ERR_BLESSING_USER_IS_EMPTY');
                return true;
            }

            $data = $this->_serv_redpack->get($id);
            $cache = &Cache::instance();
            $setting = $cache->get('Common.setting');
            //拼接图片相对路径,如果没有上传背景图，返回0,前段取默认图片
            if($data['receive_bg'] == 0){
                $chat_bg = '';
            }else{
                $chat_bg = "http://" . $setting['domain'] . '/attachment/read/'.$data['receive_bg'];
            }

            $persons = current(unserialize($data['persons']));
            $blessing_uid =  $persons['m_uid'];
            // 获取第一个祝福人头像
            $face = \Common\Common\User::instance()->avatar($blessing_uid);

            // 获取第一个祝福人名称
            $sendName =  current(unserialize($data['persons']));
            $sendName = $sendName['m_username'];

            // 获取抢红包人名称
            $serv = D('Common/Member', 'Service');
            $_user = $serv->get($uid);

            // 第一人获取祝福语
            $content = unserialize($data['content']);
            $cj_id = $_user['cj_id'];
            $job_ser = D('Common/CommonJob', 'Service');
            $job = $job_ser->get($cj_id);
            $content_res = $content[0];
            if(!empty($job)){
                $job_name = $job['cj_name'];
                if(!empty($job_name)){
                    $content_res = str_replace(self::JOB_TAG, $job_name, $content_res);
                }else{
                    $content_res = str_replace(self::JOB_TAG, '', $content_res);
                }
            }else{
                $content_res = str_replace(self::JOB_TAG, '', $content_res);
            }

            // 替换userNameTag
            $userName = $_user['m_username'];
            if(empty($userName)){
                $content_res = str_replace(self::USER_NAME_TAG, '', $content_res);
            }else{
                $content_res = str_replace(self::USER_NAME_TAG, $userName, $content_res);
            }

            // 替换departmentTag
            $department_all = $cache->get('Common.department');
            $cd_id = $_user['cd_id'];
            $department = $department_all[$cd_id];

            $departmentName = $department['cd_name'];
            if(empty($departmentName)){
                $content_res = str_replace(self::DEPARTMENT_TAG, '', $content_res);
            }else{
                $content_res = str_replace(self::DEPARTMENT_TAG, $departmentName, $content_res);
            }

            $this->_result = array(
                'id' => $data['id'],
                'uid' => $uid,
                'uname' => $_user['m_username'],
                'title' => $data['actname'],
                "sendName" => $sendName,
                'face' => $face,
                'bgUrl' => $chat_bg,
                'money' => $money['money'],
                'content' => $content_res
            );
        }else{
            E('_ERR_BLESSING_ID_IS_EMPTY');
            return true;
        }

        return true;
    }

    /**
     * 查看总办祝福接口
     * @return bool
     */
    public function Detail_get() {

        $id = $this-> _check_params();

        $uid = I('get.uid');
        if(empty($uid)){
            E('_ERR_BLESSING_UID_IS_EMPTY');
            return true;
        }

        // 实例化
        $this->_serv_redpack = D('BlessingRedpack/BlessingRedpack', 'Service');
        // 根据条件查询数据
        $data = $this->_serv_redpack->get($id);
        if(!empty($data)){
            // 验证用户是否有抢到红包
            $_serv_redpack_log = D('BlessingRedpack/BlessingRedpackLog', 'Service');
            $_model_redpack_log = D('BlessingRedpack/BlessingRedpackLog');
            $_params = array(
                "redpack_id" => $id,
                "m_uid" => $uid
            );

            $user = $_serv_redpack_log->get_by_conds($_params);
            if(empty($user)){
                E('_ERR_BLESSING_USER_IS_EMPTY');
                return true;
            }

            $data = $this->_serv_redpack->get($id);
            $cache = &Cache::instance();
            $setting = $cache->get('Common.setting');
            // 拼接图片相对路径
            if($data['chat_bg'] == 0){
                $chat_bg = '';
            }else{
                $chat_bg = "http://" . $setting['domain'] . '/attachment/read/'.$data['chat_bg'];
            }

            // 祝福人数组
            $tmp_persons = unserialize($data['persons']);
            // 取用户头像
            $uids = array_keys($tmp_persons);
            $serv_mem = D('Common/Member', 'Service');
            $users = $serv_mem->list_by_pks($uids);
            \Common\Common\User::instance()->push($users);

            // 祝福语数组
            $content = unserialize($data['content']);
            $blessings = array();

            $this->_get_content($tmp_persons, $content, $blessings, $uid);

            $this->_result = array(
                'id' => $data['id'],
                'uid' => $uid,
                'title' => $data['actname'],
                "bgUrl" => $chat_bg,
                "blessings" => $blessings
            );
        }
        return true;
    }

    /**
     * 根据职位、姓名标识解析祝福语内容
     */
    public function _get_content($persons, $contents, &$blessings, $uid){
        $i = 0;
        foreach ($persons as $_k => $_v) {
            $content_res = $contents[$i];
            if(!empty($content_res)){
                // 得到当前用户信息
                if(!empty($uid)){
                    $serv = D('Common/Member', 'Service');
                    $_user = $serv->get($uid);
                }else{
                    $_user = $this->_login->user;
                }

                // 存在需要替换的标识符
                if(strpos($content_res, self::JOB_TAG) !== false){
                    $cj_id = $_user['cj_id'];
                    if(!empty($cj_id)){
                        $job_ser = D('Common/CommonJob', 'Service');
                        $job = $job_ser->get($cj_id);
                        if(!empty($job)){
                            $job_name = $job['cj_name'];
                            if(!empty($job_name)){
                                $content_res = str_replace(self::JOB_TAG, $job_name, $content_res);
                            }else{
                                $content_res = str_replace(self::JOB_TAG, '', $content_res);
                            }
                        }else{
                            $content_res = str_replace(self::JOB_TAG, '', $content_res);
                        }

                    }else{
                        $content_res = str_replace(self::JOB_TAG, '', $content_res);
                    }
                }

                // 替换userNameTag
                $userName = $_user['m_username'];
                if(empty($userName)){
                    $content_res = str_replace(self::USER_NAME_TAG, '', $content_res);
                }else{
                    $content_res = str_replace(self::USER_NAME_TAG, $userName, $content_res);
                }

                // 替换departmentTag
                $cache = &Cache::instance();
                $department_all = $cache->get('Common.department');
                $cd_id = $_user['cd_id'];
                $department = $department_all[$cd_id];

                $departmentName = $department['cd_name'];
                if(empty($departmentName)){
                    $content_res = str_replace(self::DEPARTMENT_TAG, '', $content_res);
                }else{
                    $content_res = str_replace(self::DEPARTMENT_TAG, $departmentName, $content_res);
                }

                // 重组新的array
                $tmp_array = array(
                    "name" => $_v['m_username'],
                    "face" => \Common\Common\User::instance()->avatar($_v['m_uid']),
                    "content" => $content_res,
                );
                $i++;
                array_push($blessings, $tmp_array);
            }
        }
    }




    /**
     * 公共的校验请求参数方法
     */
    public function _check_params(){
        // 获取提交数据
        $id = I('get.id');
        if(empty($id)){
            E('_ERR_BLESSING_ID_IS_EMPTY');
            return true;
        }

        return $id;
    }

    /**
     * 统计分享数
     * @return bool
     */
    public function CountShare_post() {
        $id = I('post.id');
        if(empty($id)){
            E('_ERR_BLESSING_ID_IS_EMPTY');
            return true;
        }

        // 实例化
        $this->_serv_redpack = D('BlessingRedpack/BlessingRedpack', 'Service');
        // 根据条件查询数据
        $data = $this->_serv_redpack->get($id);
        if(!empty($data)){
            $update_data = array(
                "share_num" => $data['share_num'] + 1,
            );
            $result = $this->_serv_redpack->update($id, $update_data);
            if($result === false) {
                Log::record("统计红包活动[".$id."]的分享数失败", Log::INFO);
                E('_ERR_BLESSING_REDPACK_COUNT_SHARE_ERROR');
                return true;
            }
        }
        return true;
    }

    /**
     * 统计活动分享查看数
     * @return bool
     */
    public function CountDetail_post() {
        $id = I('post.id');
        if(empty($id)){
            E('_ERR_BLESSING_ID_IS_EMPTY');
            return true;
        }

        // 实例化
        $this->_serv_redpack = D('BlessingRedpack/BlessingRedpack', 'Service');
        // 根据条件查询数据
        $data = $this->_serv_redpack->get($id);
        if(!empty($data)){
            $update_data = array(
                "see_num" => $data['see_num'] + 1,
            );
            $result = $this->_serv_redpack->update($id, $update_data);
            if($result === false) {
                Log::record("统计红包活动[".$id."]的查看数失败", Log::INFO);
                E('_ERR_BLESSING_REDPACK_COUNT_DETAIL_ERROR');
                return true;
            }
        }
        return true;
    }

    /**
     * 获取红包二次分享签名信息
     * @return bool
     */
    public function GetShareSign_get(){
        // 取jsapi授权签名相关
        $serv = &Service::instance();
        $jscfg = array();
        $serv->jsapi_signature_for_share($jscfg, I('get.url'));

        $this->_result = array('jscfg' => $jscfg);
        return true;
    }

    /** 生成红包单号
     * @param string $muid 用户id
     * @return string
     */
    private static function billno($muid) {
        return substr($muid, 0, 10) . rgmdate(time(), 'YmdHi') . random(6);
    }
}
