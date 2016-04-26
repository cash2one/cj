<?php
/**
 * SignCpController.class.php
 * 考勤后台控制器
 * @author: 还有谁
 * @createTime: 2016/02/29 15:48
 * @version: $Id$ 
 * @copyright: 畅移信息
 */
namespace Sign\Controller\Apicp;
use Common\Common\Cache;
use Think\Log;
use Com\Excel;

class SignCpController extends AbstractController {
    protected $_server_batch = null;

    protected $_server_record = null;

    protected $_server_schedule = null;

    protected $_server_schedule_log = null;

    // 班次id
    protected $_batch_id = null;

    // 每页最大个数
    const MAX_LIMIT = 50;

    // xls 横坐标
    protected $_letter = array();

    public function before_action($action = '') {

        if (in_array($action, array('test'))) {
            $this->_require_login = false;
        }

        if(!parent::before_action($action)){
            return false;
        }

        $this->_server_batch = D('Sign/SignBatch', 'Service');
        $this->_server_record = D('Sign/SignRecord', 'Service');
        $this->_server_schedule = D('Sign/SignSchedule', 'Service');
        $this->_server_schedule_log = D('Sign/SignScheduleLog', 'Service');
        return true;
    }

    public function test(){
//        $t = "6.5";
//        $t_s = 1456886090;
//        $t_e = 1456904090;
//        $h = ($t_e - $t_s) / 3600;
//        echo round($h, 1);
//        echo "\n";
//
//        echo $t-4.3;


//        $tmp = $this->get_conds_for_date(rstrtotime($st), rstrtotime($et));
//        var_export($tmp, true);
//        var_dump($tmp);
    }

    /**
     * 获取xx年的法定假日接口
     * @param year 年份
     */
    public function getLegalDates_get(){

        $params = I('get.');
        // 先写死, 数据通过json文件获取
        $year = date("Y");
//        if(!empty($params['year'])){
//            $year = $params['year'];
//        }

        $filename = APP_PATH . 'Sign/Common/LegalDate.json';
        $json_string = file_get_contents($filename);

        // 返回数据
        $this->_result = json_decode($json_string);
    }


    /**
     * 新增班次
     */
    public function add_post(){
        // 消除时间差
        date_default_timezone_set('Asia/Shanghai');

        $params = I('post.');

        // 班次名称
        if(empty($params['name'])){
            E('_ERR_NO_BATCH_NAME');
            return false;
        }

        // 验证班次名称是否重名
        $conds = array(
            "name" => $params['name']
        );
        $batch_data = $this->_server_batch->get_by_conds($conds);
        if(!empty($batch_data)){
            E('_ERR_BTACH_NAME_IS_EXIST');
            return false;
        }

        // 校验数据格式
        $this->__deal_function_data($params);


        try{
            //开启事务
           // $this->_server_batch->start_trans();

            $id = $this->_server_batch->insert($params);

            $this->_batch_id = $id;

            // 验证是否需要通过计划任务推送消息
            // 启用签到提醒
            if($params['sign_on'] == 1){
                $sign_type = 'sign_on';
                // 签到提醒时间
                $tmp_remind_on_rage = $params['remind_on_rage'] / 60; //分钟
                $tmp_remind_on = $params['remind_on'];

                // 弹性
                if($params['type'] === 2){
                    // 启用最晚上班时间
                    if($params['late_work_time_on'] == 1){
                        // 计算执行任务时间(早于最晚上班时间点前（）分钟提醒)
                        // 最晚上班时间 - 签到提醒时间
                        $tmp_late_work_time = rgmdate($params['late_work_time'], 'Y-m-d H:i:s');

                        $tmp_exec_time = rstrtotime("$tmp_late_work_time-$tmp_remind_on_rage minute");
                        // 如果签到时间小于当前时间，任务计划执行时间天数需要+1，设定到第二天开始触发
                        $this->_calc_time($tmp_exec_time, $id, $sign_type, $tmp_remind_on);
                    }else{
                        // 晚于最早签到时间点后（）分钟提醒
                        // 上班时间 + 签到提醒时间
                        $tmp_work_begin = rgmdate($params['work_begin'], 'Y-m-d H:i:s');
                            $tmp_exec_time = rstrtotime("$tmp_work_begin+$tmp_remind_on_rage minute");
                        // 如果签到时间小于当前时间，任务计划执行时间需要+1，设定到第二天开始触发
                        $this->_calc_time($tmp_exec_time, $id, $sign_type, $tmp_remind_on);
                    }
                }else{
                    // 常规考勤
                    $tmp_work_begin = rgmdate($params['work_begin'], 'Y-m-d H:i:s');
                    $tmp_exec_time = rstrtotime("$tmp_work_begin-$tmp_remind_on_rage minute");
                    $this->_calc_time($tmp_exec_time, $id, $sign_type, $tmp_remind_on);
                }
            }

            // 签退提醒
            if($params['sign_off'] == 1){
                $sign_type = 'sign_off';
                $tmp_remind_off_rage = $params['remind_off_rage'] / 60; //分钟
                $tmp_work_end = rgmdate($params['work_end'], 'Y-m-d H:i:s');
                $tmp_remind_off = $params['remind_off'];
                // 弹性
                if($params['type'] === 2){
                    $tmp_exec_time = rstrtotime("$tmp_work_end-$tmp_remind_off_rage minute");
                    // 如果签退时间小于当前时间，任务计划执行时间天数需要+1，设定到第二天开始触发
                     $this->_calc_time($tmp_exec_time, $id, $sign_type, $tmp_remind_off);
                }else{
					//Log::record('签退时间：'.$tmp_work_end);
					//Log::record('签退提醒规则：'.$tmp_remind_off_rage);
                    $tmp_exec_time = rstrtotime("$tmp_work_end+$tmp_remind_off_rage minute");
					//Log::record('任务触发时间：'.$tmp_exec_time);
                    // 如果签退时间小于当前时间，任务计划执行时间天数需要+1，设定到第二天开始触发
                     $this->_calc_time($tmp_exec_time, $id, $sign_type, $tmp_remind_off);
                }
            }
           // $this->_server_batch->rollback();

            //提交事务
           // $this->_server_batch->commit();

        }catch (\Exception $e){
            Log::record('新增班次异常：');
            Log::record($e->getMessage());
           // $this->_server_batch->rollback();
            E('_ERR_SIGN_SYSTEM_BUSY');
            return false;
        }

        return true;
    }

    /**
     * 计算当前新增的班次是否需要第二天在开始推送签到提醒消息
     * @param $tmp_exec_time 执行时间
     * @param $id
     */
    private function _calc_time($tmp_exec_time, $id, $type, $content){
        // 如果签到时间小于当前时间，任务计划执行时间需要+1，设定到第二天开始触发
        if($tmp_exec_time < NOW_TIME){
            $tmp_exec_time = rgmdate($tmp_exec_time, 'Y-m-d H:i:s');
            $tmp_exec_time = rstrtotime("$tmp_exec_time+1 day");
        }

        $batch_id = array(
            'batch_id' =>  $this->_batch_id,
            'content' => $content,
            'type' => $type
        );

		$cache = &Cache::instance();
		$setting = $cache->get('Common.setting');

        $this->_server_batch->add_task(md5('sign_new'. $setting['domain']. $id), $tmp_exec_time, $type, $batch_id);
    }

	private function _calc_time_update($tmp_exec_time, $id, $type, $content){
		// 如果签到时间小于当前时间，任务计划执行时间需要+1，设定到第二天开始触发
		if($tmp_exec_time < NOW_TIME){
			$tmp_exec_time = rgmdate($tmp_exec_time, 'Y-m-d H:i:s');
			$tmp_exec_time = rstrtotime("$tmp_exec_time+1 day");
		}

		$batch_id = array(
			'batch_id' =>  $this->_batch_id,
			'content' => $content,
			'type' => $type
		);

		$cache = &Cache::instance();
		$setting = $cache->get('Common.setting');

		$this->_server_batch->del_task(md5('sign_new'.$setting['domain']. $id), $type);
		$this->_server_batch->add_task(md5('sign_new'.$setting['domain']. $id), $tmp_exec_time, $type, $batch_id);
	}


    /**
     * 校验提交数据
     * @param $in
     * @param $data
     * @return bool
     */
    public function __deal_function_data(&$data) {

        if(empty($data['type'])){
            E('_ERR_BTACH_TYPE_IS_NULL');
            return false;
        }

        // 工作开始时间
        if (empty($data['work_begin'])) {
            E('_ERR_WORK_BEGIN_TIME_IS_NULL');
            return false;
        }

        // 工作结束时间
        if (empty($data['work_end'])) {
            E('_ERR_WORK_END_TIME_IS_NULL');
            return false;
        }

        // 验证小时分钟的时间格式是否正确
        if(!preg_match('/^((1|0?)[0-9]|2[0-3]):([0-5]{0,1}[0-9]{0,1})$/', $data['work_begin'])){
            E('_ERR_BTACH_WORK_BEGIN_FORMAT');
            return false;
        }

        // 验证结束时间是否是次日
        $tmp_end = "";
        $tmp_time_data = null;
        if(strpos($data['work_end'],'次日') !== false){
            // 次日
            $tmp_end = rgmdate(NOW_TIME + 86400, "Y-m-d");
            $tmp_time_data = mb_substr($data['work_end'], 2, mb_strlen($data['work_end'],'utf8'), "utf-8");
        }else{
            $tmp_end = rgmdate(NOW_TIME, "Y-m-d");
            $tmp_time_data = $data['work_end'];
        }

        if(!preg_match('/^((1|0?)[0-9]|2[0-3]):([0-5]{0,1}[0-9]{0,1})$/', $tmp_time_data)){
            E('_ERR_BTACH_WORK_END_FORMAT');
            return false;
        }

        $tmp_end = $tmp_end . ' ' . $tmp_time_data;
        $tmp_end = rstrtotime($tmp_end);

        // 当日
        $tmp_start = rgmdate(NOW_TIME, "Y-m-d");
        $tmp_start = $tmp_start . ' ' .$data['work_begin'];
        $tmp_start = rstrtotime($tmp_start);

        // 将上下班时间格式化之后，以时间戳的方式存储进库中
        $data['work_begin'] = $tmp_start;
        $data['work_end'] = $tmp_end;

        // 工作结束时间必须大于开始时间
        if ($tmp_start > $tmp_end) {
            // 弹性提示 最晚签退时间不得早于最早签到时间
            if($data['type'] == 2){
                E('_ERR_WORK_TIME2');
                return false;
            }
            E('_ERR_WORK_TIME');
            return false;
        }

        // 两个时间不能相等
        if ($tmp_start > $tmp_end) {
            E('_ERR_SIGN_TIME_NOT_EQUAL_ERROR');
            return false;
        }

        // 上班总时长不能超过24小时
        if ($tmp_end - $tmp_start > 86400) {
            E('_ERR_BTACH_TOO_LONG');
            return false;
        }

        // 考勤规则
        if (empty($data['rule'])) {
            // 默认考勤规则
            $data['rule'] = 1;
        }

        // 班次参数类型校验
        $int_type = (int)$data['type'];

        // 非数字
        if(preg_match('/^[0-9]*$/', $int_type) && $int_type === 0){
            E('_ERR_PARAMS_TYPE');
            return false;
        }
        $data['type'] = $int_type;
        // 弹性班次
        if ($data['type'] === 2) {
            // 自定义考勤规则
            $data['rule'] = 2;
        }

        // 从系统参数中获取相关默认参数值 // 自定义考勤规则
        $this->check_rule($data['rule'], $data);

        return true;
    }


    /**
     * 校验考勤规则有效性(时间单位存储的都是秒)
     * @param $rule  1: 默认考勤规则, 2: 自定义考勤规则
     * 0: 停用 1：启用
     */
    public function check_rule($rule, &$data){
        // 弹性班次
        if ($data['type'] === 2) {
            // 最小工作时长
            if(empty($data['min_work_hours'])){
                E('_ERR_MIN_WORK_HOURS_IS_NULL');
                return false;
            }

            // 验证最小工作时长是否超出设定的考勤范围
            $hours =  ($data['work_end'] - $data['work_begin']) / 3600;
            if($data['min_work_hours'] > $hours){
                E(L('_ERR_WORK_DIFF_TIME', array('time' => round($hours, 1))));
                return false;
            }

            // 启用最晚上班时间
            if(!empty($data['late_work_time_on']) && (int)$data['late_work_time_on'] == 1){
                $tmp_late_work_time = $data['late_work_time'];
                // 校验最晚上班时间
                if(empty($tmp_late_work_time)){
                    E('_ERR_LATE_WORK_TIME_IS_NULL');
                    return false;
                }

                $str_late_work_time = strstr($tmp_late_work_time, '次日');
                $str_late_work_time_data = null;
                if(!empty($str_late_work_time)){
                    // 次日
                    $tmp_time = rgmdate(NOW_TIME + 86400, "Y-m-d");
                    $str_late_work_time_data = mb_substr($tmp_late_work_time, 2);
                }else{
                    $tmp_time = rgmdate(NOW_TIME, "Y-m-d");
                    $str_late_work_time_data = $tmp_late_work_time;
                }

                // 验证小时分钟的时间格式是否正确
                if(!preg_match('/^((1|0?)[0-9]|2[0-3]):([0-5]{0,1}[0-9]{0,1})$/', $str_late_work_time_data)){
                    E('_ERR_PARAMS_LATE_WORK_TIME');
                    return false;
                }

                $tmp_merge = $tmp_time . ' ' .$str_late_work_time_data;
                // 最晚上班时间戳
                $tmp_late_work_time = rstrtotime($tmp_merge);
                // 验证最晚上班时间是否在设定的上下班时间范围内
                if($tmp_late_work_time < $data['work_begin'] || $tmp_late_work_time > $data['work_end']){
                    E('_ERR_PARAMS_LATE_WORK_TIME_RULE');
                    return false;
                }
                $data['late_work_time'] = $tmp_late_work_time;

                // 启用迟到规则
                if(!empty($data['come_late_range_on']) && (int)$data['come_late_range_on'] == 1){
                    if(empty($data['come_late_range'])){
                        E('_ERR_COME_LATE_RANGE_IS_NULL');
                        return false;
                    }else{
                        $int_come_late_range = (int)$data['come_late_range'];

                        // 非数字
                        if(preg_match('/^[0-9]*$/', $int_come_late_range) && $int_come_late_range === 0){
                            E('_ERR_PARAMS_COME_LATE_RANGE');
                            return false;
                        }else{
                            $data['come_late_range'] = $int_come_late_range * 60;
                        }
                    }
                }else{
                    // 禁用迟到规则
                    $data['come_late_range_on'] = 0;
                }

            }
            // 启用旷工规则
            if(!empty($data['absenteeism_range_on']) && (int)$data['absenteeism_range_on'] === 1){
                // 实际工作时长少于最小工作时长算旷工,50%、100%
                if(empty($data['absenteeism_range'])){
                    // 默认50%
                    $data['absenteeism_range'] = 50;
                }
            }

            // 最早签到时间不得早于当日00:00分
            $_cur_t = rgmdate(NOW_TIME, "Y-m-d") . ' 00:00:00';
            $_sign_check_t = $data['work_begin'] - $data['sign_start_range'];
            if(rstrtotime($_cur_t) > $_sign_check_t){
                E('_ERR_SIGN_NOT_BEFORE_ERROR');
                return false;
            }
            // 最晚签退时间不得晚于次日23:59分
            $_cur_t = rgmdate(NOW_TIME + 86400, "Y-m-d") . ' 23:59:59';
            $_sign_check_t = $data['work_end'] + $data['sign_end_range'];
            if(rstrtotime($_cur_t) < $_sign_check_t){
                E('_ERR_SIGN_NOT_LATER_ERROR');
                return false;
            }
        }else{
            // 常规班次
            // 默认考勤规则
            if($rule === 1){
                $cache = &Cache::instance();
                $cache_setting = $cache->get('Sign.setting');

                $data['sign_start_range'] = ((int)$cache_setting['sign_start_range']) * 60;
                $data['sign_end_range'] = ((int)$cache_setting['sign_end_rage']) * 60;
                $data['come_late_range'] = ((int)$cache_setting['sign_come_late_range']) * 60;
                $data['leave_early_range'] = ((int)$cache_setting['sign_leave_early_range']) * 60;

                // 启用加班
                $data['late_range_on'] = 1;
                $data['late_range'] = ((int)$cache_setting['sign_late_range']) * 60;

                // 开启签到提醒
                $data['sign_on'] = 1;
                $data['remind_on_rage'] = ((int)$cache_setting['sign_remind_on_rage']) * 60;
                $data['remind_on'] = $cache_setting['sign_remind_on'];

                // 开启签退提醒
                $data['sign_off'] = 1;
                $data['remind_off_rage'] = ((int)$cache_setting['sign_remind_off_rage']) * 60;
                $data['remind_off'] = $cache_setting['sign_remind_off'];
            } else {
                // 上班时间点前XX分钟开始签到
                if(empty($data['sign_start_range'])){
                    E('_ERR_PARAMS_SIGN_START_RANGE_IS_NULL');
                    return false;
                }else{
                    $int_sign_start_range = (int)$data['sign_start_range'];

                    // 非数字
                    if(preg_match('/^[0-9]*$/', $int_sign_start_range) && $int_sign_start_range === 0){
                        E('_ERR_PARAMS_SIGN_START_RANGE');
                        return false;
                    }else{
                        $data['sign_start_range'] = $int_sign_start_range * 60;
                    }
                }

                // 下班时间点后XX分钟结束签退
                if(empty($data['sign_end_range'])){
                    E('_ERR_PARAMS_SIGN_END_RANGE_IS_NULL');
                    return false;
                }else{
                    $int_sign_end_range = (int)$data['sign_end_range'];

                    // 非数字
                    if(preg_match('/^[0-9]*$/', $int_sign_end_range) && $int_sign_end_range === 0){
                        E('_ERR_PARAMS_SIGN_END_RANGE');
                        return false;
                    }else{
                        $data['sign_end_range'] = $int_sign_end_range * 60;
                    }
                }

                // 迟到
                if(empty($data['come_late_range'])){
                     E('_ERR_PARAMS_COME_LATE_RANGE_IS_NULL');
                        return false;
                }else{
                    $int_come_late_range = (int)$data['come_late_range'];

                    // 非数字
                    if(preg_match('/^[0-9]*$/', $int_come_late_range) && $int_come_late_range === 0){
                        E('_ERR_PARAMS_COME_LATE_RANGE');
                        return false;
                    }else{
                        $data['come_late_range'] = $int_come_late_range * 60;
                    }
                }

                // 早退
                if(empty($data['leave_early_range'])){
                    E('_ERR_PARAMS_LEAVE_EARLY_RANGE_IS_NULL');
                    return false;
                }else{
                    $int_leave_early_range = (int)$data['leave_early_range'];

                    // 非数字
                    if(preg_match('/^[0-9]*$/', $int_leave_early_range) && $int_leave_early_range === 0){
                        E('_ERR_PARAMS_LEAVE_EARLY_RANGE');
                        return false;
                    }else{
                        $data['leave_early_range'] = $int_leave_early_range * 60;
                    }
                }

                // 启用加班
                if(!empty($data['late_range_on']) && $data['late_range_on'] == 1){
                    if(empty($data['late_range'])){
                        E('_ERR_PARAMS_LETE_RANGE_IS_NULL');
                        return false;
                    }else{
                        $int_late_range = (int)$data['late_range'];

                        // 非数字
                        if(preg_match('/^[0-9]*$/', $int_late_range) && $int_late_range === 0){
                            E('_ERR_PARAMS_LATE_RANGE');
                            return false;
                        }else{
                            $data['late_range'] = $int_late_range * 60;
                        }
                    }
                }
         }

             // 最早签到时间不得早于当日00:00分
            $_cur_t = rgmdate(NOW_TIME, "Y-m-d") . ' 00:00:00';
            $_sign_check_t = $data['work_begin'] - $data['sign_start_range'];
            if(rstrtotime($_cur_t) > $_sign_check_t){
                E('_ERR_SIGN_NOT_BEFORE_ERROR');
                return false;
            }
            // 最晚签退时间不得晚于次日23:59分
            $_cur_t = rgmdate(NOW_TIME + 86400, "Y-m-d") . ' 23:59:59';
            $_sign_check_t = $data['work_end'] + $data['sign_end_range'];
            if(rstrtotime($_cur_t) < $_sign_check_t){
                E('_ERR_SIGN_NOT_LATER_ERROR');
                return false;
            }

        // 开启签到提醒
        if(!empty($data['sign_on']) && $data['sign_on'] == 1){
            if(empty($data['remind_on_rage'])){
                E('_ERR_PARAMS_REMIND_ON_RAGE_IS_NULL');
                return false;
            }else{
                $int_remind_on_rage = (int)$data['remind_on_rage'];

                // 非数字
                if(preg_match('/^[0-9]*$/', $int_late_range) && $int_late_range === 0){
                    E('_ERR_PARAMS_REMIND_ON_RANGE');
                    return false;
                }else{
                    // 验证发起的消息提醒时间必须在考勤的时间范围内，防止没到签到的时间（区分常规、弹性）
                    if ($data['type'] === 2) {
                        // 启用最晚上班时间
                        if(!empty($data['late_work_time_on']) && (int)$data['late_work_time_on'] == 1){
                            $tmp_exec_time = rgmdate($data['late_work_time'], 'Y-m-d H:i:s');
                            $tmp_exec_time = rstrtotime("$tmp_exec_time-$int_remind_on_rage minute");
                            // 最晚上班时间 - 签到提醒时间 > 签到时间
                            if($tmp_exec_time < $data['work_begin']){
                                E('_ERR_BATCH_SIGN_ON');
                                return false;
                            }
                        }else{
                            $tmp_exec_time = rgmdate($data['work_begin'], 'Y-m-d H:i:s');
                            $tmp_exec_time = rstrtotime("$tmp_exec_time+$int_remind_on_rage minute");
                            // 签到时间必须在 签退、签到范围内
                            if($tmp_exec_time < $data['work_begin'] ||  $tmp_exec_time > $data['work_end']){
                                E('_ERR_BATCH_SIGN_ON');
                                return false;
                            }
                        }
                    }else{
                         // 验证常规考勤范围
                        // 上班签到时间
                        $tmp_exec_time = rgmdate($data['work_begin'], 'Y-m-d H:i:s');

                        // 签到范围时间
                        $tmp_min =  $data['sign_start_range'] / 60; // 分钟

                        // 签到范围开始时间
                        $tmp_sign_begin_time = rstrtotime("$tmp_exec_time-$tmp_min minute");

                        // 签到提醒时间
                        $tmp_sign_time = rstrtotime("$tmp_exec_time-$int_remind_on_rage minute");

                        // 签到时间必须在 签退、签到范围内
                        if($tmp_sign_time < $tmp_sign_begin_time ||  $tmp_sign_time > $data['work_begin']){
                            //Log::record('-----1：' . $tmp_sign_time);
                            //Log::record('-----2：' . $tmp_sign_begin_time);
                            //Log::record('-----3：' . $data['work_begin']);
                            E('_ERR_BATCH_SIGN_ON');
                            return false;
                        }
                    }

                    // 保存秒
                    $data['remind_on_rage'] = $int_remind_on_rage * 60;
                }
            }

            // 验证提醒语
            if(empty($data['remind_on'])){
                E('_ERR_PARAMS_REMINE_ON_IS_NULL');
                return false;
            }
        }

        // 是否开启签退提醒
        if(!empty($data['sign_off']) && $data['sign_off'] == 1){
            if(empty($data['remind_off_rage'])){
                E('_ERR_PARAMS_REMIND_OFF_RAGE_IS_NULL');
                return false;
            }else{
                $int_remind_off_rage = (int)$data['remind_off_rage'];

                // 非数字
                if(preg_match('/^[0-9]*$/', $int_remind_off_rage) && $int_remind_off_rage === 0){
                    E('_ERR_PARAMS_REMIND_OFF_RANGE');
                    return false;
                }else{
                    // 验证发起签退的消息提醒时间必须在考勤的时间范围内，防止无法签退 签退提醒时间 > (最晚签退时间 - 最早签到时间)
                    //（区分常规、弹性）
                    if ($data['type'] === 2) {
                        $tmp_exec_time = rgmdate($data['work_end'], 'Y-m-d H:i:s');
                        $tmp_exec_time = rstrtotime("$tmp_exec_time-$int_remind_off_rage minute");
                        if($tmp_exec_time < $data['work_begin'] ||  $tmp_exec_time > $data['work_end']){
                            E('_ERR_BATCH_SIGN_OUT');
                            return false;
                        }
                    }else{
                        // 验证常规考勤范围
                        // 计算签退范围
                        $tmp_exec_time = rgmdate($data['work_end'], 'Y-m-d H:i:s');
                        // 签退结束时间
                        $tmp_sign_end_time = rstrtotime("$tmp_exec_time+$int_remind_off_rage minute");

                        // 签退提醒开始时间
                        $tmp_sign_out_time = rstrtotime("$tmp_exec_time+$int_remind_off_rage minute");

                        // 签退时间必须在 上下班时间范围内
                        if($tmp_sign_out_time < $data['work_end'] ||  $tmp_sign_out_time > $tmp_sign_end_time){
                            E('_ERR_BATCH_SIGN_OUT');
                            return false;
                        }
                    }
                }

                $data['remind_off_rage'] = $int_remind_off_rage * 60;
            }

            // 验证提醒语
            if(empty($data['remind_off'])){
                E('_ERR_PARAMS_REMINE_OFF_IS_NULL');
                return false;
            }
        }
    }
}


    /**
     * 获取班次列表
     * @return bool
     */
    public function List_get() {
        // 班次名称
        $name = I('get.name');
        $page = I('get.page', 1, 'intval');
        $limit = I('get.limit', 10, 'intval');
        $limit = min($limit, self::MAX_LIMIT);

        $list = array();
        $cond = array();

        // 分页参数
        list($start, $limit, $page) = page_limit($page, $limit);
        // 分页参数
        $page_option = array($start, $limit);

        $count = 0;
        if (!empty($name)) {
            $cond['name'] = $name;

        }

        $list = $this->_server_batch->list_by_conds_for_like($cond, $page_option, array('flag' => 'DESC','created' => 'DESC'));
        foreach ($list as &$_key) {

            $t_s = rgmdate($_key['work_begin'], "H:i");
            $t_e = rgmdate($_key['work_end'], "H:i");
            //$tmp_cur_s = date("j", $_key['work_begin']);
            //$tmp_cur_e = date("j", $_key['work_end']);
            $tmp_cur_s = rgmdate($_key['work_begin'],'Y-m-d');
            $tmp_cur_e = rgmdate($_key['work_end'],'Y-m-d');
            // 常规
            if($_key['type'] == 1){
                $_key['_str_type'] = '常规';

                $_key['_str_work_begin'] = $t_s;

                if(differ_days($tmp_cur_s,$tmp_cur_e) > 0){
                    $_key['_str_work_end'] = '次日 ' . $t_e;
                }else{
                    $_key['_str_work_end'] = $t_e;
                }

            }else if($_key['type'] == 2){
                $_key['_str_type'] = '弹性';

                $_key['_str_work_begin'] = $t_s . ' (最早签到时间)';

                if(differ_days($tmp_cur_s,$tmp_cur_e) > 0){
                    $_key['_str_work_end'] = '次日 ' . $t_e . ' (最晚签到时间)';
                }else{
                    $_key['_str_work_end'] = $t_e . ' (最晚签到时间)';
                }
            }else{
                $_key['_str_type'] = '未知';
            }

        }
        $count = $this->_server_batch->count_by_conds_for_like($cond);
        $pages = ceil($count / $limit);

        $this->_result = array(
            'list' => $list,
            'page' => $page,
            'count' => $count,
            'pages' => $pages,
            'limit' => $limit,
        );

        return true;
    }

    /**
     * 获取所有班次
     * @return bool
     */
    public function getBatchs_get() {
        // 班次名称
        $page = I('get.page', 1, 'intval');
        $limit = I('get.limit', 10, 'intval');

        $limit = min($limit, self::MAX_LIMIT);

        $list = array();

        // 分页参数
        list($start, $limit, $page) = page_limit($page, $limit);
        // 分页参数
        $page_option = array($start, $limit);

        $count = 0;
        $list = $this->_server_batch->list_all($page_option, array('created' => 'DESC'));
        foreach ($list as &$_key) {
            $t_s = rgmdate($_key['work_begin'], "H:i");
            $t_e = rgmdate($_key['work_end'], "H:i");

//            $tmp_cur_s = date("j", $_key['work_begin']);
//            $tmp_cur_e = date("j", $_key['work_end']);
//            if($tmp_cur_e > $tmp_cur_s){
//                $_key['_str_time'] = $t_s . ' - ' . $t_e . '(次日)';
//            }else{
//                $_key['_str_time'] = $t_s . ' - ' . $t_e;
//            }
            $tmp_cur_s = rgmdate($_key['work_begin'],'Y-m-d');
            $tmp_cur_e = rgmdate($_key['work_end'],'Y-m-d');
            if(differ_days($tmp_cur_s,$tmp_cur_e) > 0){
                $_key['_str_time'] = $t_s . ' - ' . $t_e . '(次日)';
            }else{
                $_key['_str_time'] = $t_s . ' - ' . $t_e;
            }
        }

        $count = $this->_server_batch->count();
        $pages = ceil($count / $limit);

        $this->_result = array(
            'list' => $list,
            'page' => $page,
            'count' => $count,
            'pages' => $pages,
            'limit' => $limit,
        );

        return true;
    }


    /**
     * 查询班次详情
     * @param id 班次id
     * @param type 如果值为edit，则是处理编辑接口
     * @return bool
     */
    public function getBatchDetail_get() {
        // 班次名称
        $id = I('get.id');
        if(empty($id)){
            E('_ERR_MISS_PARAMETER_ID');
            return false;
        }

        $type = I('get.type');
        if($type == 'edit'){
            // 查询班次是否被排班,如果被排版则不允许编辑
            $sign_schedule_service = D('Sign/SignSchedule', 'Service');
            $schedule_data = $sign_schedule_service->list_batch_in_schedule($id);
            if(!empty($schedule_data)){
                E('_ERR_BATCH_USED');
                return false;
            }

            // 需要帮使用该班次的排班都删除掉
//            $schedule_data = $sign_schedule_service->list_batch_in_schedule($id, null, true);
//            if(!empty($schedule_data)){
//                E('_ERR_BATCH_DELETE_ERROR');
//                return false;
//            }
        }

        $data = $this->_server_batch->get($id);
        if(!empty($data)){
            $data = $data[0];
            // 格式化日期参数
            $t_s = rgmdate($data['work_begin'], "H:i");
            $t_e = rgmdate($data['work_end'], "H:i");

            $tmp_cur_s = rgmdate($data['work_begin'],'Y-m-d');
            $tmp_cur_e = rgmdate($data['work_end'],'Y-m-d');
            // 签到时间
            $data['_str_work_begin'] = $t_s;


            if(differ_days($tmp_cur_s,$tmp_cur_e) > 0){
                $data['_str_work_end'] = '次日' . $t_e;
            }else{
                $data['_str_work_end'] = $t_e;
            }

            // 最晚上班时间 启用
            if($data['late_work_time_on'] == 1){
                $data['_str_late_work_time'] = rgmdate($data['late_work_time'], "H:i");
            }

            // 迟到规则
            $data['_str_come_late_range'] =rgmdate($data['come_late_range'], "H:i");

            // 签到提醒
            if($data['sign_on'] == 1){
                $data['_str_remind_on_rage'] = $data['remind_on_rage'] / 60;
            }

            // 签到提醒
            if($data['sign_off'] == 1){
                $data['_str_remind_off_rage'] = $data['remind_off_rage'] / 60;
            }

            // 早退规则
            $data['_str_leave_early_range'] = $data['leave_early_range'] / 60;

            // 上班时间点前XX分钟开始签到
            $data['_str_sign_start_range'] = $data['sign_start_range'] / 60;
            // 下班时间点后XX分钟结束签退
            $data['_str_sign_end_range'] = $data['sign_end_range'] / 60;

            // 启用加班
            if($data['late_range_on'] == 1){
                $data['_str_late_range'] = $data['late_range'] / 60;
            }

            return  $this->_result = $data;
        }

        return true;
    }

    /**
     * 修改班次
     */
    public function update_post() {
        $params = I('post.');
        $id = $params['sbid'];
        if(empty($id)){
            E('_ERR_MISS_PARAMETER_ID');
            return false;
        }

        // 验证名称是否修改
        $old_data = $this->_server_batch->get($id);
		$this->_batch_id = $id;
        if(!empty($old_data)){
            if($params['name'] != $old_data[0]['name']){
                // 如果班次名称和库中的名称不一致，需要检测是否有重名的情况
                $conds = array(
                    "name" => $params['name']
                );
                $check_data = $this->_server_batch->get_by_conds($conds);
                if(!empty($check_data)){
                    E('_ERR_BTACH_NAME_IS_EXIST');
                    return false;
                }
            }

            // 校验数据格式
            $this->__deal_function_data($params);
			// 启用签到提醒
			if($params['sign_on'] == 1){
				$sign_type = 'sign_on';
				// 签到提醒时间
				$tmp_remind_on_rage = $params['remind_on_rage'] / 60; //分钟
				$tmp_remind_on = $params['remind_on'];

				// 弹性
				if($params['type'] === 2){
					// 启用最晚上班时间
					if($params['late_work_time_on'] == 1){
						// 计算执行任务时间(早于最晚上班时间点前（）分钟提醒)
						// 最晚上班时间 - 签到提醒时间
						$tmp_late_work_time = rgmdate($params['late_work_time'], 'Y-m-d H:i:s');

						$tmp_exec_time = rstrtotime("$tmp_late_work_time-$tmp_remind_on_rage minute");
						// 如果签到时间小于当前时间，任务计划执行时间天数需要+1，设定到第二天开始触发
						$this->_calc_time_update($tmp_exec_time, $id, $sign_type, $tmp_remind_on);
					}else{
						// 晚于最早签到时间点后（）分钟提醒
						// 上班时间 + 签到提醒时间
						$tmp_work_begin = rgmdate($params['work_begin'], 'Y-m-d H:i:s');
						$tmp_exec_time = rstrtotime("$tmp_work_begin+$tmp_remind_on_rage minute");
						// 如果签到时间小于当前时间，任务计划执行时间需要+1，设定到第二天开始触发
						$this->_calc_time_update($tmp_exec_time, $id, $sign_type, $tmp_remind_on);
					}
				}else{
					// 常规考勤
					$tmp_work_begin = rgmdate($params['work_begin'], 'Y-m-d H:i:s');
					$tmp_exec_time = rstrtotime("$tmp_work_begin-$tmp_remind_on_rage minute");
					$this->_calc_time_update($tmp_exec_time, $id, $sign_type, $tmp_remind_on);
				}
			}else{
				$cache = &Cache::instance();
				$setting = $cache->get('Common.setting');

				$this->_server_batch->del_task(md5('sign_new'.$setting['domain']. $id), 'sign_on');
			}

			// 签退提醒
			if($params['sign_off'] == 1){
				$sign_type = 'sign_off';
				$tmp_remind_off_rage = $params['remind_off_rage'] / 60; //分钟
				$tmp_work_end = rgmdate($params['work_end'], 'Y-m-d H:i:s');
				$tmp_remind_off = $params['remind_off'];
				// 弹性
				if($params['type'] === 2){
					$tmp_exec_time = rstrtotime("$tmp_work_end-$tmp_remind_off_rage minute");
					// 如果签退时间小于当前时间，任务计划执行时间天数需要+1，设定到第二天开始触发
					$this->_calc_time_update($tmp_exec_time, $id, $sign_type, $tmp_remind_off);
				}else{
					//Log::record('签退时间：'.$tmp_work_end);
					//Log::record('签退提醒规则：'.$tmp_remind_off_rage);
					$tmp_exec_time = rstrtotime("$tmp_work_end+$tmp_remind_off_rage minute");
					//Log::record('任务触发时间：'.$tmp_exec_time);
					// 如果签退时间小于当前时间，任务计划执行时间天数需要+1，设定到第二天开始触发
					$this->_calc_time_update($tmp_exec_time, $id, $sign_type, $tmp_remind_off);
				}
			}else{
				$cache = &Cache::instance();
				$setting = $cache->get('Common.setting');

				$this->_server_batch->del_task(md5('sign_new'.$setting['domain']. $id), 'sign_off');
			}

            $this->_server_batch->update($id, $params);

			//删除当天这个班次已发送的考勤消息提醒表记录
			$_server_alert = D('Sign/SignAlert', 'Service');
			$_params = array(
				'created' => rgmdate(NOW_TIME, 'Y-m-d'),
				'batch_id' => $this->_batch_id,
			);
			$_server_alert->delete_sign_alert_by_params($_params);

        }else{
            E('_ERR_BATCH_NOT_FOUND');
            return false;
        }

        return true;
    }

    /**
     * 删除
     */
    public function delete_post() {
        $params = I('post.');
        $id = $params['id'];

        if(empty($id)){
            E('_ERR_MISS_PARAMETER_ID');
            return false;
        }

        // 查询班次是否被排班,如果被排版则不允许删除
        $sign_schedule_service = D('Sign/SignSchedule', 'Service');
        $schedule_data = $sign_schedule_service->list_batch_in_schedule($id);
        if(!empty($schedule_data)){
            E('_ERR_BATCH_USED');
            return false;
        }

        $batch_data = $this->_server_batch->get($id);
        if(!empty($batch_data)){
            // 默认数据
            if($batch_data[0]['flag'] == 1) {
                E('_ERR_BATCH_DELETE_FAILD');
                return false;
            }
			$cache = &Cache::instance();
			$setting = $cache->get('Common.setting');

			$this->_server_batch->del_task(md5('sign_new'.$setting['domain']. $id), 'sign_off');
			$this->_server_batch->del_task(md5('sign_new'.$setting['domain']. $id), 'sign_on');
            $this->_server_batch->delete($id);
        }

        return true;
    }

    /**
     * 导出考勤记录
     */
    public function exportRecord_get(){
        // 查询的参数
        $searchDefault = array(
            'm_username' => '',
            'signtime_min' => '',
            'signtime_max' => '',
            'sr_type' => '',
            'sr_sign' => '',
            'cd_id' => '');

        list($searchBy) = $this->_search_condition_meger($searchDefault);
        $this->_search_sign_record_data($searchBy);
    }


    protected function _search_sign_record_data($searchBy){

        if (empty($searchBy['signtime_min']) || empty($searchBy['signtime_max'])) {
              E('_ERR_RECORD_DATE_IS_NULL');
              return false;
        }else{
            $begin = rstrtotime($searchBy['signtime_min']);
            $end = rstrtotime($searchBy['signtime_max']);
        }

        // 验证开始时间是否大于结束时间
        if(differ_days($searchBy['signtime_min'], $searchBy['signtime_max']) < 0){
            E('_ERR_RECORD_DATE_IS_ERROR');
            return false;
        }

        // 验证考勤导出日期范围不能超过31天(differ_days方法是计算日期相差的天数)
        if(differ_days($searchBy['signtime_min'], $searchBy['signtime_max']) > 30){
            E('_ERR_RECORD_DATE_CANT_NEXT_MONTH');
            return false;
        }

        // 构造虚拟的日期数组
        $datelist = $this->get_conds_for_date($begin, $end);

        $member_service = D('Common/Member', 'Service');

        // 只查已关注的人
        $searchBy['m_qywxstatus'] = 1;
        if(!empty($searchBy['m_username'])){
            $searchBy['keyword'] = $searchBy['m_username'];
        }

        // 获取企业总人数
        $total = $member_service->count_by_cdid_kws($searchBy['cd_id'], $searchBy);
        if ($total == 0) {
            // 没有可以导出的数据
            E('_ERR_SIGN_EXPORT_IS_NULL');
            return false;
        }

        // 实例化压缩类
        $zip = new \ZipArchive();
        $path = get_sitedir() . 'excel/';
        rmkdir($path);

        $out_file_name = rgmdate($begin, 'Y') . '年' . rgmdate($begin, 'm') . '月考勤记录';
        $zipname = $path . $out_file_name;
        $zip->open($zipname . '.zip', \ZipArchive::CREATE);

        // 一次最多查100人
        $speed = 100;
        // 每月最多31天
        //$days = 31;
        // 一次最多查询的考勤记录
        $count = 5000;

        // 循环次数
        $batch_count_user = 0;
        // 结果集
        $result = array();
        do{
            // 分页起始索引
            $from_user = $batch_count_user * $speed;
            $batch_count_user++;
            $page_option_user = array($from_user, $speed);

            // 获取员工数据
            $list = $member_service->list_by_cdid_kws($searchBy['cd_id'], $searchBy, $page_option_user);
            // 将员工id放入数组, 用于查询考勤
            $tmp_member = array();
            // 用户id做为key，存入数组，用于过滤这个用户是否有考勤记录
            $tmp_filter_member = array();
            // 当前用户当天没有考勤记录的情况，不是通过sql分组查询的情况
            $tmp_filter_record = array();
            foreach ($list as $tmp_data) {
                $tmp_member[] = $tmp_data['m_uid'];
                $tmp_filter_member[$tmp_data['m_uid']] = $tmp_data;
            }
            // 获取员工当月的考勤记录
            $page_option = array(0, $count);


            $orderby['sr_signtime'] = 'ASC';
            $searchBy['m_uid'] = $tmp_member;
            $tmp_record_data = $this->_server_record->list_by_conds_for_export($searchBy, $page_option, $orderby);
            if (!empty($tmp_record_data)) {
                $_tmp_rep_member = array(); // 保存已有考勤的用户，用于放入没有考勤时段的天数中
                // 有考勤的用户
                $_tmp_record_member = array();
                // 天---用户---班次--考勤记录
                foreach ($datelist as $_d) {
                    foreach ($tmp_record_data as $_record) {
                        // 从第一天开始循环加入考勤数据
                        if ($_d == $_record['signtime']) {
                            if(empty($_tmp_record_member[$_record['m_uid']])){
                                // 没用户考勤数据,放入数据
                                $_tmp_record_member[$_record['m_uid']][$_record['sr_batch']][] = $_record;
                            }else{
                                // 用户没这个班次
                                if(empty($_tmp_record_member[$_record['m_uid']][$_record['sr_batch']])){
                                    $_tmp_record_member[$_record['m_uid']][$_record['sr_batch']][] = $_record;
                                }else{
                                    // 向用户已有的班次中，追加数据
                                    $_tmp_record_member[$_record['m_uid']][$_record['sr_batch']][]= $_record;
                                }
                            }

                            if(empty($_tmp_rep_member['m_uid'])){
                                $_tmp_rep_member[$_record['m_uid']]['m_username'] = $_record['m_username'];
                            }

                            if(empty($_tmp_record_member[$_record['uid']])){
                                $_res = $this->_check_work_day($_record['uid'], $_d, $_record['username']);
                              //  Log::record("222--- " . $_d. ' --- ' . $_record['signtime'] . ' --- ' . var_export($_res, true), Log::ALERT);
                                $_tmp_record_member[$_record['uid']] = $_res;
                            }

                            continue;
                        }else{
                            // 如果用户有考勤记录，但是某一天没有记录时
                            if(empty($_tmp_rep_member['m_uid'])){
                                $_tmp_rep_member[$_record['m_uid']]['m_username'] = $_record['m_username'];
                            }
                        }
                        // sql中返回的用户，没有考勤数据，计算当天是否是休息日还是旷工
                        if(empty($_record['signtime']) && empty($_tmp_record_member[$_record['uid']])){
                            $_res = $this->_check_work_day($_record['uid'], $_d, $_record['username']);
                            $_tmp_record_member[$_record['uid']] = $_res;
                        }
                    }

                    $result[$_d] = $_tmp_record_member;
                    // 查看当天是否有用户考勤记录，没有则添加进数组，并且也要过滤是否是休息日or旷工
                    $_flag = false;
                    if(!empty($_tmp_rep_member)){
                        foreach ($_tmp_rep_member as $_rep_member_k => $_rep_member_v) {
                            if(empty($result[$_d][$_rep_member_k])){
                                $_res = $this->_check_work_day($_rep_member_k, $_d, $_rep_member_v['m_username']);
                                $_tmp_record_member[$_rep_member_k] = $_res;
                                $_flag = true;
                            }
                        }
                        // 有旷工数据
                        if($_flag){
                            $result[$_d] = $_tmp_record_member;
                        }
                    }
                    unset($_tmp_record_member);
                }
            }else {
                // 没有可以导出的数据
                E('_ERR_SIGN_EXPORT_IS_NULL');
                return false;
            }

            if($batch_count_user == 1){
                $filename = $out_file_name;
            }else{
                $filename = $out_file_name . '_' . $batch_count_user;
            }

            // 合并班次、汇总考勤时长
            $this->_merger_sign_data($result);

            $tmp_path = $this->_exportExcel($result, $searchBy, $filename);

            $convert_file_name = iconv("utf-8","gbk//IGNORE", $filename);
            $zip->addFile($tmp_path, $convert_file_name . ".xls");
            unset($result);

        }while ($total > ($speed * $batch_count_user));

        $zip->close();
        $this->__put_header($zipname . '.zip');
        // 清除缓存
        $this->__clear($path);

    }

    /**
     * 导出excel
     */
    protected function _exportExcel($result, $searchBy, $filename){
        // 生成 xls 横坐标
        for ($i = 0; $i <= 500; $i++) {
            if ($i < 26) {
                $this->_letter[] = chr($i + 65);
            } else {
                $ascii = floor($i / 26) - 1;
                $this->_letter[] = chr($ascii + 65) . chr(($i % 26) + 65);
            }
        }


        $excel = new Excel();
        // Excel表格式
        $letter = $this->_letter;
        // 表头数组
        $tableheader = array('考勤汇总表');
        // 填充表头信息
        for($i = 0; $i < count($tableheader); $i ++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
        }
        // 设置宽度,加粗
        $this->__set_width($excel);

        // 输出表格
        $data = $this->__get_data($result, $searchBy);
        // 填充表格信息
        for($i = 2; $i <= count($data) + 1; $i ++) {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $j ++;
            }
        }
        // 创建Excel输入对象
        $write = $this->__write($excel, $filename);
        // 设置存储路径
        $path = get_sitedir() . 'excel/';
        if (!is_dir($path)) {
            mkdir($path);
        }
        $path = $path . $filename . ".xls";
        $write->save($path);

        return $path;
    }


    /**
     * 汇总表 构造xls表输出数组
     * @param $result
     * @return array
     */
    private function __get_data($result, $searchBy) {
        // 标题
        $data[0] = array('统计日期:' . $searchBy['signtime_min'] . '~' . $searchBy['signtime_max']);
        // 子段
        $data[1] = array('序号', '姓名', '部门', '日期', '班次' , '考勤时间', '签到签退时间', '迟到(分钟)', '早退(分钟)', '加班时长(分钟)', '出勤时长(分钟)', '考勤地址');
        $i = 1;
        foreach ($result as $_result_k => $_result_v) {
            // 人员数组
            foreach ($_result_v as $user) {
                $data[] = array($i, $user['user_name'], $user['dep_name'], $_result_k, $user['rep_batch_name'], $user['rep_work_start_time'], $user['rep_sign_time'], $user['rep_late_time'], $user['rep_early_time'], $user['rep_sr_overtime'], $user['rep_work_time'], $user['rep_sing_address']);
                $i ++;
            }
        }

        return $data;
    }

    /**
     * 清理产生的临时文件
     */
    private function __clear($path) {
        $dh = opendir($path);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                unlink($path . $file);
            }
        }
    }

    /**
     * 下载输出至浏览器
     * @param $zipname
     */
    private function __put_header($zipname) {
        if (!file_exists($zipname)) {
            exit("下载失败");
        }

        $file = fopen($zipname, "r");
        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header( "Expires: 0" ); // 设置不缓存
        header("Accept-Length: " . filesize($zipname));
        if(strpos($_SERVER['HTTP_USER_AGENT'],"MSIE")){
            //如果是ie存为的名字要urlencode
            header('Content-Disposition: attachment; filename="'.urlencode($zipname).'"');
        }else{
            header("Content-Disposition: attachment; filename=" . basename($zipname));
        }
        echo fread($file, filesize($zipname));
        $buffer = 1024;
        while (! feof($file)) {
            $file_data = fread($file, $buffer);
            echo $file_data;
        }
        fclose($file);
    }

    /**
     * 创建Excel公用输入对象方法
     *
     * @param unknown $excel
     * @param unknown $name
     * @return PHPExcel_Writer_Excel5
     */
    private function __write($excel, $filename) {
        $encoded_filename = urlencode($filename);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);

        // 创建Excel输入对象
        $write = new \PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }

        header("Content-Transfer-Encoding:binary");

        return $write;
    }

    /**
     * 汇总表设置宽度
     */
    private function __set_width($excel) {
        // 居中，加粗
        $excel->getActiveSheet()->getStyle('A1:L3')->applyFromArray(array('font' => array('bold' => true), 'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        // 合并
        $excel->getActiveSheet()->mergeCells('A1:K1');
        $excel->getActiveSheet()->mergeCells('A2:K2');
        // 设置宽度
        $excel->setActiveSheetIndex(0);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('L')->setWidth(25);

        return true;
    }

    /**
     * 验证是否是工作日
     * 这里导出数据有几种问题
     * 1. 部门有多条数据分隔的情况可能是 用户部门对应已启用的排班下的部门，也可能是用户直接对用的部门
     * 2. 当考勤状态为空时，说明部门都没有排班，不确定使用哪个部门对应的排班规则
     * @param $uid 用户id
     * @param $username 用户名
     * @return bool
     */
    protected function _check_work_day($uid, $_d, $username){
        $_res = array();
        // 获得当前用户的所有部门
        $serv_department = D('Common/MemberDepartment', 'Service');
        $departments = $serv_department->list_by_uid($uid);

        // 查询部门对应的排班
        $tmp_dep = array();
        // 用户对用的所有部门信息
        $_dep_name = array();
        // 获取部门名称
        $cache = &Cache::instance();
        $cache_departments = $cache->get('Common.department');
        foreach($departments as $d){
            $tmp_dep[] = $d['cd_id'];
            $_dep_name[] = $cache_departments[$d['cd_id']]['cd_name'];
        }

        $_dep_name = implode(',', $_dep_name);
        if(empty($_dep_name)){
            $_dep_name = '尚未分配部门';
        }

        // 验证导出的系统当前日期，是否大于考勤日,如果大于考勤时间，所有的签到记录都已'-'填充
        $now_date = rgmdate(NOW_TIME, 'Y-m-d');
        if(differ_days($now_date,$_d) > 0){
            // 时间未到
            $_res['flag'] = true;
            $_res['username'] = $username;
            $_res['dep_name'] = $_dep_name;
            return $_res;
        }

        $params = array(
            'cdid_array' => $tmp_dep,
            'enabled' => 1
        );
        // 获取排班列表
        $_data_schedule = $this->_server_schedule->list_schedule($params);
       // Log::record(("获取排班  " .$username . " --- " . count($_data_schedule)));
        // 有排班记录
        if(!empty($_data_schedule)){
            // 如果部门是多个，启用多个排班，不计算考勤状态，把所有部门名称逗号拼接起来显示
            if(count($_data_schedule) > 1){
                $tmp_dep_name = array();
                foreach($_data_schedule as $d_s){
                    $tmp_dep_name[] = $d_s['cd_name'];
                }
                // 保存处理后的部门名称
                $_res['username'] = $username;
                $_res['dep_name'] = implode(',', $tmp_dep_name);
                $_res['flag'] = true; // 不显示考勤状态标志, 因多个部门，多个排班的情况下，无法确定使用哪一个排班
            }else{
                // 1. 如果多个部门，只有一个有启用的排班，还要去排班历史规则表，查一下，是用现有的考勤规则，还是用历史考勤规则
                $conds = array(
                    'time' => $_d,
                    'cd_id' => $_data_schedule[0]['cd_id']
                );
                $_res = $this->_server_record->check_work_day($_res, $conds, $_data_schedule, $username);
                $_res['dep_name'] = $_dep_name;
            }
        }else{
            // 2. 如果所在的部门没有启用部门排班，需要查顶级部门是否有排班
            $_up_dep_schedule  = $this->_server_record->get_up_deps_schedule($departments);
            if(empty($_up_dep_schedule)){
                //顶级部门也没有排班，在查是否开启默认排班(如果开启了，还需要去历史规则表过滤一遍)
                $params['cdid_array'] = array(0);
                // 获取默认排班
                $_data_schedule = $this->_server_schedule->list_schedule($params);
                if(empty($_data_schedule)){
                    // 当有这个标识，还有一种情况就是，当前部门没有排班，同时默认排班也没有开启的情况下都不显示考勤状态
                    $_res['flag'] = true;
                    $_res['username'] = $username;
                }else{
                    // 3. 用户当前所在部门没有启用排班，但是启用了默认的排班规则
                    $conds = array(
                        'time' => $_d,
                        'cd_id' => $_data_schedule[0]['cd_id']
                    );
                    $_res = $this->_server_record->check_work_day($_res, $conds, $_data_schedule, $username);
                    $_res['rep_batch_name'] = '系统默认班次';
                }
                $_res['dep_name'] = $_dep_name;
            }else{
                //  顶级部门有排班的情况
                $conds = array(
                    'time' => $_d,
                    'cd_id' => $_up_dep_schedule[0]['cd_id']
                );
                $_res = $this->_server_record->check_work_day($_res, $conds, $_data_schedule, $username);
                $_res['dep_name'] = $_dep_name;
            }

        }

        return $_res;
    }

    /**
     * 合并班次、汇总考勤时长
     * @param $result 当月的所有考勤记录
     */
    protected function _merger_sign_data(&$result){
        foreach($result as $tmp_result_key => $tmp_result_val){
            // 迭代所有人
            foreach($tmp_result_val as $tmp_user_key => $tmp_user_val){
                // 有考勤数据
                if(empty($tmp_user_val['dep_name'])){
                    $rep_batch_name = array(); // 合并班次名称
                    $rep_begin_time = array(); // 上班时间
                    $rep_end_time = array(); // 下班时间
                    $rep_sign_start_time = array(); // 签到时间
                    $rep_sign_end_time = array(); // 签退时间

                    $rep_sign_start_address = array(); // 考勤签到地址
                    $rep_sign_end_address = array(); // 考勤签退地址

                    $rep_late_time = array(); // 迟到时长
                    $rep_early_time = array(); // 早退时长
                    $rep_work_time = array(); // 出勤时长
                    $rep_sr_overtime = array(); // 加班时长
                    $rep_sing_address = ''; // 考勤地址
                    $rep_work_start_time = ''; // 上下班时间
                    $rep_sign_time = ''; // 考勤时间
                    $dep_name = ''; // 部门名称
                    $user_name = ''; // 用户名称

                    // 迭代所有班次
                    foreach($tmp_user_val as $tmp_user_data){
                        $tmp_b_name = ""; // 部门名称
                        $tmp_rep_late_time = 0;
                        $tmp_rep_early_time = 0;
                        $tmp_rep_work_time = 0;
                        $tmp_sr_overtime = 0;

                        if(count($tmp_user_data) > 1){
                            // 迭代班次下的考勤记录
                            foreach($tmp_user_data as $tmp_res_data){
                                $tmp_b_name = $tmp_res_data['batch_name'];
                                // 上班
                                if($tmp_res_data['sr_type'] == 1){
                                    $rep_begin_time[] = $tmp_res_data['sr_work_begin'];
                                    $rep_sign_start_time[] = $tmp_res_data['sr_signtime'];
                                    if(!empty($tmp_res_data['rep_late_time'])){
                                        $tmp_rep_late_time += $tmp_res_data['rep_late_time'];
                                    }

                                    $rep_sign_start_address[$tmp_res_data['sr_signtime']] = $tmp_res_data['sr_address'];
                                    $rep_end_time[] = $tmp_res_data['sr_work_end'];
                                }else{
                                    // 下班
                                    $rep_end_time[] = $tmp_res_data['sr_work_end'];
                                    $rep_sign_end_time[] = $tmp_res_data['sr_signtime'];

                                    if(!empty($tmp_res_data['rep_early_time'])){
                                        $tmp_rep_early_time += $tmp_res_data['rep_early_time'];
                                    }

                                    if(!empty($tmp_res_data['rep_work_time'])){
                                        $tmp_rep_work_time += $tmp_res_data['rep_work_time'];
                                    }

                                    if(!empty($tmp_res_data['sr_overtime'])){
                                        $tmp_sr_overtime += $tmp_res_data['sr_overtime'];
                                    }

                                    $rep_sign_end_address[$tmp_res_data['sr_signtime']] = $tmp_res_data['sr_address'];
                                }
                                $dep_name = $tmp_res_data['cd_name'];
                                $user_name = $tmp_res_data['username'];
                            }
                        }else{
                            $tmp_b_name = $tmp_user_data[0]['batch_name'];
                            // 上班
                            if($tmp_user_data[0]['sr_type'] == 1){
                                $rep_begin_time[] = $tmp_user_data[0]['sr_work_begin'];
                                $rep_sign_start_time[] = $tmp_user_data[0]['sr_signtime'];
                                if(!empty($tmp_res_data['rep_late_time'])){
                                    $tmp_rep_late_time += $tmp_user_data[0]['rep_late_time'];
                                }

                                $rep_sign_start_address[$tmp_user_data[0]['sr_signtime']] = $tmp_user_data[0]['sr_address'];

                                // 防止就签一个上班之后，导出报表时显示签退时长异常
                                $rep_end_time[] = $tmp_user_data[0]['sr_work_end'];
                            }else{
                                $rep_end_time[] = $tmp_user_data[0]['sr_work_end'];
                                $rep_sign_end_time[] = $tmp_user_data[0]['sr_signtime'];

                                if(!empty($tmp_user_data[0]['rep_early_time'])){
                                    $tmp_rep_early_time += $tmp_user_data[0]['rep_early_time'];
                                }

                                if(!empty($tmp_user_data[0]['rep_work_time'])){
                                    $tmp_rep_work_time += $tmp_user_data[0]['rep_work_time'];
                                }

                                if(!empty($tmp_user_data[0]['sr_overtime'])){
                                    $tmp_sr_overtime += $tmp_user_data[0]['sr_overtime'];
                                }

                                $rep_sign_end_address[$tmp_user_data[0]['sr_signtime']] = $tmp_user_data[0]['sr_address'];
                            }
                            $dep_name = $tmp_user_data[0]['cd_name'];
                            $user_name = $tmp_user_data[0]['username'];
                        }
                        // 报表中，多班次需要已|线分隔
                        $rep_batch_name[] = $tmp_b_name;
                        $rep_late_time[] = $tmp_rep_late_time;
                        $rep_early_time[] = $tmp_rep_early_time;
                        $rep_work_time[] = $tmp_rep_work_time;
                        $rep_sr_overtime[] = $tmp_sr_overtime;
                    }

                    // 获取上班时间最小值、下班时间最大值
                    $min_work_time = array_search(min($rep_begin_time), $rep_begin_time);
                    $max_work_time = array_search(max($rep_end_time), $rep_end_time);
                    $tmp_min_work_time = rgmdate($rep_begin_time[$min_work_time], 'H:i');
                    $max_work_time = $this->_server_record->check_time_range($rep_begin_time[$min_work_time], $rep_end_time[$max_work_time]);

                    // 获取签到最小值、签退时间最大值
                    $min_sign_time = array_search(min($rep_sign_start_time), $rep_sign_start_time);
                    $tmp_min_sign_time = rgmdate($rep_sign_start_time[$min_sign_time], 'H:i');

                    if(!empty($rep_sign_end_time)){
                        $tmp_max_sign_time = array_search(max($rep_sign_end_time), $rep_sign_end_time);
                        $max_sign_time = $this->_server_record->check_time_range($rep_sign_start_time[$min_sign_time], $rep_sign_end_time[$tmp_max_sign_time]);
                    }

                    // 拼接考勤地址
                    $rep_sign_start_address = $rep_sign_start_address[$rep_sign_start_time[$min_sign_time]];
                    $rep_sign_end_address = $rep_sign_end_address[$rep_sign_end_time[$tmp_max_sign_time]];
                    $rep_sing_address = $rep_sign_start_address . '-' . $rep_sign_end_address;

                    // 累加统计时长
                    $rep_late_time = array_sum($rep_late_time);
                    if($rep_late_time == 0){
                        $rep_late_time = '-';
                    }else{
                        $rep_late_time = round($rep_late_time / 60);
                    }

                    $rep_early_time = array_sum($rep_early_time);
                    if($rep_early_time == 0){
                        $rep_early_time = '-';
                    }else{
                        $rep_early_time = round($rep_early_time / 60);
                    }

                    $rep_work_time = array_sum($rep_work_time);
                    if($rep_work_time == 0){
                        $rep_work_time = '-';
                    }else{
                        $rep_work_time = round($rep_work_time / 60);
                    }

                    $rep_sr_overtime = array_sum($rep_sr_overtime);
                    if($rep_sr_overtime == 0 || $rep_sr_overtime < 0){
                        $rep_sr_overtime = '-';
                    }else{
                        $rep_sr_overtime = round($rep_sr_overtime / 60);
                    }

                    $rep_batch_name = implode("|",$rep_batch_name);
                    $rep_work_start_time = $tmp_min_work_time . ' - ' . $max_work_time;
                    if(empty($max_sign_time)){
                        $max_sign_time = '';
                    }

                    $rep_sign_time = $tmp_min_sign_time. ' - ' . $max_sign_time;

                }else{
                    $rep_batch_name = '-';
                    $rep_work_start_time = '-';
                    $rep_sing_address = '-';

                    $rep_sign_time = '-';
                    $rep_late_time = '-';
                    $rep_early_time = '-';
                    $rep_work_time = '-';
                    $rep_sr_overtime = '-';

                    $dep_name = $tmp_user_val['dep_name'];
                    // 无考勤数据，需要判断考勤状态，是显示休息、旷工还是不显示考勤结果
                    if($tmp_user_val['flag']){
                        // 无法确定使用哪一个部门排班(可能没有班次的情况)
                        $dep_name = $tmp_user_val['dep_name'];
                    }else{
                        // 有班次排班
                        if($tmp_user_val['status'] == 1){
                            // 旷工
                            $rep_work_start_time = '旷工';
                        }else{
                            // 休息
                            $rep_work_start_time = '休息';
                        }
                    }
                    if(!empty($tmp_user_val['rep_batch_name'])){
                        $rep_batch_name = $tmp_user_val['rep_batch_name'];
                    }
                    $user_name = $tmp_user_val['username'];
                }

                $result[$tmp_result_key][$tmp_user_key] = array(
                    'user_name' => $user_name,
                    'dep_name' => $dep_name,
                    'rep_batch_name' => $rep_batch_name,
                    'rep_work_start_time' => $rep_work_start_time,
                    'rep_sign_time' => $rep_sign_time,
                    'rep_late_time' => $rep_late_time,
                    'rep_early_time' => $rep_early_time,
                    'rep_sr_overtime' => $rep_sr_overtime,
                    'rep_work_time' => $rep_work_time,
                    'rep_sing_address' => $rep_sing_address
                );

                unset($rep_work_start_time);
                unset($rep_sign_time);
            }
        }
    }


    /**
     * 合并考勤查询条件
     * @param $searchBy
     */
    protected function _search_condition_meger($searchDefaults){
        $searchBy = array();

        $params = I('get.');
        // 没有条件查询参数
        if(empty($params)){
            $searchBy = $searchDefaults;
        }else {
            foreach ($searchDefaults as $_k => $_v) {
                if (isset($params[$_k]) && $params[$_k] != $_v) {
                    if ($params[$_k] != null) {
                        $searchBy[$_k] = $params[$_k];
                    } else {
                        $searchBy[$_k] = $_v;
                    }
                }
            }
            $searchBy = array_merge($searchDefaults, $searchBy);
        }

        return array(array_merge($searchDefaults, $searchBy));
    }

    /**
     * 根据时间范围生成日期数组
     * @param $begin
     * @param $end
     * @return array
     */
    public function get_conds_for_date($begin, $end) {
        // 构造日期数组
        $datelist = array();
        while ($begin <= $end) {
            $begin = rgmdate($begin, 'Y-m-d');
            $datelist[$begin] = $begin;
            $begin = rstrtotime($begin) + 86400;
        }

        return $datelist;
    }

}