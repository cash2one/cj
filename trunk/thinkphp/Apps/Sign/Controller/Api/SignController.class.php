<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace Sign\Controller\Api;

use Common\Common\Cache;
use Think\Log;
use Sign\Model\SignScheduleModel;

class SignController extends AbstractController {
	// 人员 部门关联表
	protected $_serv_mem_department = null;
	// 人员部门关联数据库
	protected $_serv_member = null;
	// 班次表
	protected $_serv_batch = null;
    // 排班表
    protected $_serv_schedule = null;
	// 班次部门关联表
	protected $_serv_department = null;
    // 考勤记录表
    protected $_serv_record = null;
	// 提交的班次id值
	protected $_sbid = "";
    // 提交的排班id值
    protected $_ssid = "";
	// 签到 类型
	protected $_type = "";
    // 部门id
    protected $_cd_id = "";
	// 提交的经度
	protected $_longitude = null;
	// 提交的维度
	protected $_latitude = null;
	// 所有人信息和班次信息
	protected $_all_member = null;

	const FORCED_PASS = 1; // 强制签到
	const WORK_ON = 1; // 签到
	const WORK_OFF = 2; // 签退

    public function before_action($action = '') {

        if (in_array($action, array('test'))) {
            $this->_require_login = false;
        }

        if(!parent::before_action($action)){
            return false;
        }

        // 实例化
        $this->_serv_mem_department = D('Common/MemberDepartment', 'Service');
        $this->_serv_member = D('Common/MemberDepartment', 'Service');
        $this->_serv_batch = D('Sign/SignBatch', 'Service');
        $this->_serv_schedule = D('Sign/SignSchedule', 'Service');
        $this->_serv_department = D('Sign/SignDepartment', 'Service');
        $this->_serv_record = D('Sign/SignRecord', 'Service');

        return true;
    }

    public function test_get(){

//        $st = '2016-03-17';
//
//        $et = '2016-03-16';
//
//        $tmp = get_conds_for_date();
//
//        echo rgmdate($tmp, 'm');


        //31.165970,121.403680
        // 经度
        $sr_longitude = 121.40375912189484;

        // 纬度
        $sr_latitude = 31.167117782670815;

//        $a=array('1','3','55','99');
//        $pos = array_search(max($a), $a);
//        echo $a[$pos];
//        echo "\n";
//        $a=array('1','3','55','99');
//        $pos = array_search(min($a), $a);
//        echo $a[$pos];
//
//        echo "\n";
       // $lng = 121.41076396058;
       // $lat = 31.171405643876;

        //121.410313,31.171877
        // 坐标转换
//        $maps = new \Com\Location();
//        $maps->conver_to_baidu($sr_latitude, $sr_longitude);
////
//        echo $sr_latitude;
//        echo "\n";
//        echo $sr_longitude;

//        $t = rgmdate(1457139600, 'H:i');
//        echo $t;
//        echo "\n";
//        $etime = rstrtotime('2016-03-05 ' . $t); //
//
//        echo $etime;

//        $t1 = 1456966800;
//        $t2 = 1456974000;
//        echo differ_hours($t1, $t2);
//        echo "\n";
//
//        $serv_department = D('Common/MemberDepartment', 'Service');
//        $departments = $serv_department->list_by_uid(90);
//
//        echo var_export($departments, true);
//        $tmp_dep = array();
//        foreach($departments as $d){
//            $tmp_dep[] = $d['cd_id'];
//        }
//
//        $params = array(
//            'cdid_array' => $tmp_dep,
//            'enabled' => 1
//        );
//        $sign_schedule_service = D('Sign/SignSchedule', 'Service');
//        $_data_schedule = $sign_schedule_service->list_schedule($params);
//
//        echo var_export($_data_schedule, true);
//        // 模拟各种签到时间
//        $t1 = 1457044608;
//
//
//        // 模拟当天时间 27号
//        $tmp = 1456526208;
//        $tmp_s = rgmdate($tmp, 'Ymd');
//
//        // 模拟第一天签到时间 27号
//        $t2 = 1456526208;
//        $t2_s = rgmdate($t2, 'Ymd');
//
//        $t1_s = rgmdate($t1, 'Ymd');
//
//        // 周期
//        $z = 7;
//
//        //  模拟第一天签到 27 号
//        if($tmp_s == $t2_s){
//            echo "第一天，默认查询第一个规则";
//            echo "\n";
//        }
//
//        // 28号
//        if($tmp_s != $t1_s){
//            $a = (($t1 - $tmp) / 86400 % $z) + 1;
//            echo $a;
//        }
       // date_default_timezone_set('Asia/Shanghai');

        // 加班规则
//        $late_range = 60 / 60;
//        echo '2015-2-25 16:14:50';
//        echo "\n";
//
//        echo $late_range;
//
//        echo "\n";
//
//        $t = '2015-2-25 16:14:50';
//
//        $tmp_end_time = strtotime("$t+$late_range minute");
//
//        echo $tmp_end_time;
//
//        echo "\n";
//
//
//        echo rgmdate($tmp_end_time, 'Y-m-d H:i');

//        $week_array=array(7,1,2,3,4,5,6);
//
//        $t = '2016-03-17';
//
//        $t = rstrtotime($t);

       // $week_array=array(7,1,2,3,4,5,6);
       // $result = $week_array[date("w", $t)];


        //$t = strtotime(date('Y-m-',time().(date('m',time())-1).' 00:00:00'));
       // echo  rgmdate($t, 'Y-m-d');
      //  echo date("w");

    }



	/**
	 * 签到 提交，使用的是最新的排班、班次
     * @param sbid 班次ID
     * @param type  1=签到 2=签退
	 */
	public function Signin_post() {
		// 班次id
		$this->_sbid = I('post.sbid');
		if (is_empty_variable($this->_sbid)) {
			E('_ERR_MISS_PARAMETER_SBID');
			return false;
		}

        // 签到状态
        $this->_type = I('post.type');
        if (is_empty_variable($this->_type)) {
            E('_ERR_MISS_PARAMETER_TYPE');
            return false;
        }

        // 排班id
        $this->_ssid = I('post.ssid');
         Log::record("ssid---" . $this->_ssid, Log::ALERT);
        if (is_empty_variable($this->_ssid)) {
            E('_ERR_MISS_PARAMETER_SSID');
            return false;
        }

        // 部门id
        $this->_cd_id = I('post.cd_id');
        if (is_empty_variable($this->_cd_id)) {
            E('_ERR_SIGN_DEP_IS_NOT_NULL');
            return false;
        }

        // 排班状态
        $_status = I('post.status');
        if (is_empty_variable($_status)) {
            E('_ERR_SIGN_STATUS_IS_NOT_NULL');
            return false;
        }

        // 获取班次信息
        $batch_info = $this->_serv_batch->get($this->_sbid);
        if(empty($batch_info)){
            E('_ERR_BATCH_NOT_FOUND');
			return false;
        }

        // 签到标识 用于验证是否是昨天的班次
        $flag = I('post.flag');
        if(empty($flag)){
            E('_ERR_SIGN_FLAG_IS_NULL_ERROR');
            return false;
        }

        $batch_info = $batch_info[0];

        $tmp_ssid = $this->_ssid;;
        // 休息日考勤计为加班
        if($_status == 3){
            $sign_schedule_id = I('post.sign_schedule_id');
            if(empty($sign_schedule_id)){
                E('_ERR_SIGN_SCHEDULE_ID_IS_NOT_NULL');
                return false;
            }

            $batch_info['sign_schedule_id'] = $sign_schedule_id;
            $batch_info['work_status'] = 1;

            $tmp_ssid = $sign_schedule_id;
        }else{
            $batch_info['sign_schedule_id'] = $this->_ssid;

        }

        // 获取排班信息 (查询非禁用的排班)
        $schedule_info = $this->_serv_schedule->list_batch_in_schedule($this->_sbid, $tmp_ssid);
        if(empty($schedule_info)){
            E('_ERR_SIGN_SCHEDULE_NOEXIST_ERROR');
            return false;
        }

        $schedule_info = $schedule_info[0];
        // 校验排班结束时间是否过期
        if(!empty($schedule_info['schedule_end_time']) && NOW_TIME > $schedule_info['schedule_end_time']){
            E('_ERR_SIGN_SCHEDULE_IS_EXPIRED');
            return false;
        }

        // 校验是否得到定位信息
        $this->__gain_location();

        // 检测班次合法性
        $this->__check_sbid($this->_cd_id);

        $batch_index = I('post.batch_index');

        // 排班循环周期不存在
        if (is_empty_variable($batch_index)) {
            E('_ERR_SIGN_SCHEDULE_CYCLE_IS_NOT_NULL');
            return false;
        }

        // 获取地理位置信息
		$location = array();
		$this->__get_address($location);

        // 默认未早退
        $is_workoff = false;
        //默认未到签到时间
        $sign_start_time_bool = false;
        // 签退时间,用于校验是否早退
        $sign_out_time = '';
		// 当值等于1时，提交的强制签退
		$forced = I('post.forced');
//		Log::record('$forced----------------'.$forced);
//        Log::record('$is_workoff----------------'.$is_workoff);
//        Log::record('$flag----------------'.$flag);

        // 非强制签到
		if ($forced != self::FORCED_PASS) {
            $now_date = rgmdate(NOW_TIME, 'Y-m-d');
            // 签退时分
            $sign_end_time = rgmdate($batch_info['work_end'],'H:i');
            // 校验是否是昨日的班次,签到时间是否已过
            if($flag == 2){  // 昨天班次标识
                // 昨天年月日
                $yesterday_time = rgmdate(NOW_TIME - 86400,'Y-m-d');
                // 昨天班次的签退时间戳
                $sign_out_time = rstrtotime($yesterday_time . ' ' . $sign_end_time);

                // 跨天
                if(differ_days(rgmdate($batch_info['work_begin'],'Y-m-d'), rgmdate($batch_info['work_end'],'Y-m-d')) > 0){
                    $sign_out_time = rstrtotime($now_date . ' ' . $sign_end_time);
                }

                //如果是常规班次
                if($batch_info['type'] == 1){
                    // 最晚签退时间
                    $latest_time = $sign_out_time + $batch_info['sign_end_range'];
                }else{
                    $latest_time = $sign_out_time;
                }

                // 签到
                if ($this->_type == self::WORK_ON) {
                    // 最晚签到时间已过，不能签到
                    if(NOW_TIME > $latest_time){
                        E('_ERR_SIGN_ON_TIME_ERROR');
                        return false;
                    }

                }else{ // 签退
                    if(NOW_TIME > $latest_time){
                        // 已过了签退时间
                        E('_ERR_SING_END');
                        return false;
                    }
                }

                $sign_start_time_bool = true; // 可以签到
            }else{ // 当天班次
                //判断是否到签到时间
                $sign_start_time = rgmdate($batch_info['work_begin'],'H:i');
                $sign_start_time = rstrtotime($now_date .' '. $sign_start_time) - $batch_info['sign_start_range'];
                $sign_out_time = rstrtotime($now_date . ' ' . $sign_end_time);
                //如果是常规班次
                if($batch_info['type'] == 1){
                    //签到时间 = 上班时间 - 上班时间点前XX分钟开始签到（考勤时间范围）
                    // 当天开始时间
                    $sign_now_time = rstrtotime($now_date .' 00:00:00');
                    if($sign_start_time < $sign_now_time && NOW_TIME > $sign_start_time){
                        $sign_start_time_bool = true; // 可以签到
                    }else if(NOW_TIME > $sign_start_time){
                        $sign_start_time_bool = true; // 可以签到
                    }
                }else{
                    if(NOW_TIME > $sign_start_time){
                        $sign_start_time_bool = true; // 可以签到
                    }
                }
            }

			// 判断是否早退 (常规班次才有)
            if($batch_info['type'] == 1){
                $is_workoff = $this->__is_time_to_checkout($batch_info, $sign_out_time);
            }
		}else{
            // 强制签退时，需要设置为true才能签退
            $sign_start_time_bool = true;
        }
		// 判断状态
	     if ($is_workoff) {
            // 当前签退时间为早退状态
			E('_ERR_LEAVE_EARLY');
			return false;
		}else if(!$sign_start_time_bool){
            //未到签到时间，不能签到
            E('_ERR_SIGN_START_TIME_FAILD');
            return false;
        }

		// 签到
		$record = array();
		$this->__sign($location, $batch_info, $record, $this->_cd_id, $flag, $schedule_info, $is_workoff, $forced);

		// 构造返回值
		$this->_result = array(
			'id' => $record['sr_id'],
			'signtime' => $record['sr_signtime'],
			'ip' => $record['sr_ip'],
			'type' => $record['sr_type'],
			'longitude' => $record['sr_longitude'],
			'latitude' => $record['sr_latitude'],
			'address' => $record['sr_address'],
			'time' => rgmdate(NOW_TIME, 'H:i')
		);

		return true;
	}

    /**
     * 每次列表查询 更新禁用中的状态,将禁用中的状体修改为禁用
     * @param $data
     */
    public function refresh_schedule_disable(&$data){
        $now_timestamp = NOW_TIME;
        $update_timestamp = $data['updated'];
        if($data['enabled'] == SignScheduleModel::SCHEDULE_DISABLING){
            if(differ_days(rgmdate($update_timestamp,'Y-m-d'), rgmdate($now_timestamp,'Y-m-d')) > 0){
                $conds = array(
                    'enabled' => SignScheduleModel::SCHEDULE_DISABLE
                );
                $this->_serv_schedule->update($data['id'] , $conds);
                unset($conds);

                // 排班已禁用
                E('_ERR_SIGN_BATCH_IS_STOP');
                return false;
            }
        }
    }

	/**
	 * 检测班次的合法性, 用于校验当前操作人所在部门和排班部门是否一致，防止签到别的部门
	 * @return bool
	 */
	private function __check_sbid($cd_id) {

		// 获取当前员工所在部门
		$_member_data = $this->_get_all_member_department($this->_login->user['m_uid']);
        $d = $_member_data['cd_ids'];
       // Log::record("111..." . $cd_id, Log::ALERT);
       // Log::record("444..." . $req_cd_id, Log::ALERT);

        if (!in_array($cd_id, $d) && $cd_id != 0) {
            // 班次不合法 (部门和班次不符)
            if(!$this->get_up_deps($cd_id)){
                E('_ERR_BATCH_WRONGFUL');
                return false;
            }
        }

		return true;
	}

    /**
     * 根据指定部门获取当前用户的所有顶级部门排班
     * @param $dep_id
     * @return bool
     */
    public function get_up_deps($dep_id){
        //当前部门的所有上级部门
        $parent_dept_ids = $this->_serv_record->list_parent_departments_by_cdid($dep_id);
        if (!in_array($dep_id, $parent_dept_ids)) {
            return false;
        }

        return true;
    }

	/**
	 * 是否在工作日内
	 * @param $info 班次信息
     * @param $batch_index 循环周期数
     * @param $add_work_day 增加上班日期
	 * @return bool
	 */
	private function __is_in_workday($info, $batch_index, &$batch_info, $add_work_day) {
        // 排班明细时间段
        $schedule_everyday_detail = unserialize($info['schedule_everyday_detail']);
        $t = $schedule_everyday_detail[$batch_index];
        $tmp_array = array_column($t, 'type', 'id');
        $type =  $tmp_array[$this->_sbid];

        Log::record("休息吗 " . $type, Log::ALERT);
        // 休息
        if($type == 2){
            // 查看后台是否开启 休息日允许考勤开关
            $cache = &Cache::instance();
            $cache_setting = $cache->get('Sign.setting');
            $rest_day_sign = $cache_setting['rest_day_sign'];

            // 验证是否在 增加的上班日中
            if($this->_serv_record->_check_in_add_work_day($add_work_day, NOW_TIME) === false && $rest_day_sign == 1){
               //  休息日不允许考勤
                E('_ERR_NOT_ON_WORKDAY');
                return false;
            }else{
                // 休息日打卡，计为加班
                $batch_info['work_status'] = 1;
            }
        }

		return true;
	}

	/**
	 * 如果没有获取到经纬度, 给出提示
	 * @return bool
	 */
	private function __gain_location() {

		// 获取经纬度
		$this->_longitude = I('post.longitude');
		$this->_latitude = I('post.latitude');

		// 如果提交的经纬度是空的
		if (empty($this->_longitude) || empty($this->_latitude)) {
            // 获取地理位置失败，请稍后再试
            E('_ERR_LATI_LONG_IS_NOT_NULL');
            return false;
		}

		return true;
	}

	/**
	 * 获取位置信息 (为了位置定位精确，关闭使用IP获取位置的处理方法)
	 * @param $location 位置信息
	 * @return bool
	 */
	private function __get_address(&$location) {

        $this->_get_address($this->_longitude, $this->_latitude, $location);

		return true;
	}

	/**
	 * 自给定的经纬度获取位置信息, 如果没有地址, 返回false 去用ip地址获取
	 * @param float  $longitude 经度
	 * @param float  $latitude 纬度
	 * @param string $location (应用)地址信息
	 * @return boolean
	 */
	protected function _get_address($longitude, $latitude, &$location) {

		// 根据经纬度获取位置信息 (优先查看本地数据库)
		$address = $this->_get_address_by_lnglat($longitude, $latitude);

		if (empty($address)) {
            // 根据经纬度通过百度api获取地址失败
            E('_ERR_CANT_GAIN_LOCATION');
			return false;
		}

		$location = array(
			'longitude' => $longitude,
			'latitude' => $latitude,
			'address' => $address
		);

		return true;
	}

	/**
	 * 根据经纬度获取位置信息
	 * @param float $longitude 经度
	 * @param float $latitude 纬度
	 * @return string
	 */
	protected function _get_address_by_lnglat($longitude, $latitude) {

		// 查询数据库是否有地址记录
		$serv_record_location = D('Sign/SignRecordLocation', 'Service');
        $conds = array(
            'longitude' => floatval($longitude),
            'latitude' => floatval($latitude)
        );
		$result = $serv_record_location->get_by_conds($conds);

		// 如果数据库里没有存储地址数据, 根据经纬度获取地址, 并存储
		if (empty($result)) {
			// 获得地理位置
			$maps = new \Com\Location();
			$result = $maps->get_address($longitude, $latitude);
			if ($result == false || empty ($result ['address'])) {
				// 无法获取地址位置信息
				return '';
			} else {
				// 存储数据
				$serv_record_location->insert_location($longitude, $latitude, $result['address']);
			}
		} else {
			return $result['address'];
		}

		return $result['address'];
	}

	/**
	 * 判断是否在签到范围内
	 * @param $location GPS信息
     * @param $info 排班信息
	 * @return bool
	 */
	private function __is_over_sign_area($location, $info) {

		$sign_range = $info['address_range']; // 考勤范围
        // 签到gps信息
		$sr_longitude = $location ['longitude'];
		$sr_latitude = $location ['latitude'];

        // 排班gps信息
		$bat_longitude = $info ['longitude'];
		$bat_latitude = $info ['latitude'];

		// 坐标转换
		$maps = new \Com\Location();
		$maps->conver_to_baidu($sr_latitude, $sr_longitude);

		$sign = new \Sign\Common\Handle();
		$length = $sign->get_distance($sr_latitude, $sr_longitude, $bat_latitude, $bat_longitude);
		if ($length > $sign_range) {
			// 用来提示用户当前的签到地址,已经超出签到位置
			return true;
		}

		return false;
	}

	/**
	 * 判断当前签退时间是不是早退
	 * @param $info 班次信息
     * @param $etime 下班时间
	 * @return bool
	 */
	private function __is_time_to_checkout($info, $etime) {

		// 如果没有设置下班值 或者值为空 或者是签到 那么就不继续 往下判断
		if (!isset($info['work_end']) || empty($info['work_end']) || I('post.type') == self::WORK_ON) {
			return false;
		}

		if (NOW_TIME < $etime) {
			return true;
		}

		return false;
	}

	/**
	 * 地理位置签到
	 * @param $location
	 * @param $info 排班信息
	 * @param $record
	 * @return bool
	 */
	private function __sign($location, $info, &$record, $department_id, $flag, $schedule_info, $is_workoff, $forced) {

		$sign = new \Sign\Common\Handle();

		//地理位置签到
		if (!$sign->sign($record, $this->_login->user, $this->_type, $location, $info, $department_id, $flag, $this->_ssid, $schedule_info, $is_workoff, $forced)) {
			E('_ERR_SIGN_FAILED');
			return false;
		}

		// 如果返回了空值
		if (empty($record)) {
			E('_ERR_SIGN_DUPLICATE');
			return false;
		}

		return true;
	}

	/**
	 * 获取当前班次的部门ID
	 * @param $sbid 班次ID
	 * @return mixed
	 */
	private function __batch_user_in_department($sbid) {

		$department = '';
		// 判断班次里的 班次ID和提交的班次ID 相同的,取出部门ID
		foreach ($this->_member_data['batch'] as $k => $v) {
			if ($v == $sbid) {
				$department = $k;
			}
		}

		return $department;
	}

	/**
	 * 获取签到记录列表
	 */
	public function Get_list() {

		$serv_record = D('Sign/SignRecord', 'Service');
		$list = $serv_record->list_recordsf();

		return $this->_response(array($list));
	}

	/**
	 * 获得某人某一天的公司考勤记录
	 *
	 * @return 记录列表
	 */
	public function Sign_record_get() {

		// 接受数据
		$params = I('get.');

		if (empty($params['cd_id'])) {
			E('_ERROR_PARAM_VAL_NULL');
			return false;
		}

		if (empty($params['udate'])) {
			$params['udate'] = rgmdate(NOW_TIME, 'Y-m-d');
		}

        // 用户id
        $params['m_uid'] = $this->_login->user['m_uid'];

		// 公司考勤数据
		$sign_list = $this->__get_sign($params);

		// 获取外勤数据
		list($record, $limit, $page) = $this->__get_location($params);

		// 返回数据
		$this->_result = array(
			'sign_list' => $sign_list,
			'location' => array('limit' => $limit, 'page' => $page, 'record' => $record)
		);

		//Log::record('考勤记录结果：'.var_export($this->_result,true));
	}

	/**
	 * 找到今天是第几天的班次
	 * @param $type
	 * @param $today_time
	 * @param $date
	 * @return int
	 */
	protected function _find_today_batch_index($type,$today_time, $date, $cycle_num)
	{
		$result = -1;
		if ($type == SignScheduleModel::CYCLE_UNIT_DAY) {
			$result = find_schedule_day($today_time, $date, $cycle_num);
		} elseif ($type == SignScheduleModel::CYCLE_UNIT_WEEK) {
			$week_array = array(7, 1, 2, 3, 4, 5, 6);
			$result = $week_array[date("w")];
			//$result = rgmdate(NOW_TIME, 'w');
		} else {
			$result = intval(date("d"));
		}

		return $result;
	}

	/**
	 * 公司考勤方法
	 * @param array $params 接收参数
	 * @return multitype:string multitype: unknown
	 */
	private function __get_sign($params) {

		$serv_sign = D('Sign/SignRecord', 'Service');
		$serv_detail = D('Sign/SignDetail', 'Service');
		$serv_batch = D('Sign/SignBatch', 'Service');
		$serv_schedule = D('Sign/SignSchedule', 'Service');
		$serv_schedule_log = D('Sign/SignScheduleLog', 'Service');

//		$conds = array(
//			'cd_id' => $params['cd_id']
//		);
//		$schedule_data = $serv_schedule->get_schedule_for_dep($conds);
//		if(empty($schedule_data)){
//			E('_ERR_SIGN_SCHEDULE_NOEXIST_ERROR');
//			return false;
//		}

		$result = array();

	/*	foreach($schedule_data as $sd){


			//判断今天在排班历史变更表
			$schedule_log_data = $serv_schedule_log->get_schedule_history();

			if(empty($schedule_log_data)){
				// 没查到变更规则记录时, 使用当前部门对应的排班规则
				$cycle_unit = $sd['cycle_unit'];
				$updated = $sd['updated'];
				$cycle_num = $sd['cycle_num'];
				$schedule_everyday_detail = unserialize($sd['schedule_everyday_detail']);
				//找到今天的排班
				$today = $serv_sign->find_today_batch_index($cycle_unit, $updated, $cycle_num, $params['udate']);
				$today_index = $today-1;
				//今天所有班次
				$today_batchs = $schedule_everyday_detail[$today_index];
				$today_sbids = array_column($today_batchs, 'id');
				//没有班次
				if(empty($today_sbids)){
					//判断今天是否在增加上班日里
					if(!empty($sd['add_work_day'])){
						$serv_sign->_check_in_add_work_day();
					}

				}
			}else{
				$cycle_unit = $schedule_log_data['cycle_unit'];
				$updated = $schedule_log_data['init_time'];
				$cycle_num = $schedule_log_data['cycle_num'];
				$schedule_everyday_detail = unserialize($schedule_log_data['schedule_everyday_detail']);
				//找到今天的排班
				$today = $serv_sign->find_today_batch_index($cycle_unit, $updated, $cycle_num, $params['udate']);
				$today_index = $today-1;
				//今天所有班次
				$today_batchs = $schedule_everyday_detail[$today_index];
			}


//			$batch = array(
//				'sign_data' =>
//			);
//			foreach($today_batchs as $tb){
//				$serv_sign->get_sign_record_by_sbid($tb['']);
//			}



		}*/



		//当天打卡的所有班次,包括全公司的记录
		$record_batch_ids = $serv_sign->get_sign_record_groupby_sbid($params);
       // Log::record('当前部门班次----------------'.var_export($record_batch_ids,true));
        // 如果当前部门没有排班，需要查询顶级部门的排班
        if(empty($record_batch_ids)){
            // 当前部门排班不存在, 校验一下顶级部门有没有排班
            $parent_dept_ids = $this->_serv_record->list_parent_departments_by_cdid($params['cd_id']);

           // Log::record('所有顶级部门---------------- '. $params['cd_id'] . ' ' . var_export($parent_dept_ids,true));
            //查询所有上级部门的排班
            $schedule_list = $this->_serv_record->list_schedules_by_cdids($parent_dept_ids);
            if(!empty($schedule_list)) {
                $record_batch_ids[] = $schedule_list[0];
            }
        }
       // Log::record('顶级部门班次----------------'.var_export($record_batch_ids,true));
		foreach($record_batch_ids as $rbi){
			$params['sr_batch'] = $rbi['sr_batch'];
			//包含全公司
			$data = $serv_sign->get_sign_record($params);
			//Log::record('打卡记录----------------'.var_export($data,true));
            if(!empty($data)){
                $format_data = $this->_handle_record_data($serv_sign, $data, $params, $serv_detail, $serv_batch);
                $result[] = $format_data;
            }
		}
		//Log::record('$result-------------------:'.var_export($result,true));
		return $result;
	}

	/**
	 * 封装签到数据
	 * @param $serv_sign
	 * @param $params
	 * @param $serv_detail
	 */
	protected function _handle_record_data($serv_sign, $data, $params,$serv_detail, $serv_batch){
		$res = array();
		$data_list = array();
		$work_time = '';
		// 有签到数据尝试计算工作时间，查看是否有备注
        // 计算工作时间
        $serv_sign->format_worktime($data, $data_list, $work_time);

        // 计算签退状态
        $data_list = $serv_sign->off_type($data_list);
        //格式输出信息
        foreach ($data_list as $_data) {
            $tmp = array();
            // 封装返回的签到数据
            if (!isset($_data['off_type']) || $_data['off_type'] != 'no_off') {
                $tmp['sr_id'] = $_data['sr_id'];
                $tmp['m_username'] = $_data['m_username'];
                $tmp['sr_type'] = $_data['sr_type'];
                $tmp['sr_address'] = $_data['sr_address'];
                $tmp['sr_sign'] = $_data['sr_sign'];
                $tmp['_signtime'] = $_data['_signtime'];
            }
            // 封装返回的签退数据
            if (isset($_data['off_type'])) {
                $tmp['off_type'] = $_data['off_type'];
                $tmp['sr_type'] = $_data['sr_type'];
            }
            $data_format[$_data['sr_type']] = $tmp;
        }
        $data = $data_format;
        // 获取备注
        $srids = array();
        // 遍历数据获取所有记录的srid
        foreach ($data as $_val) {
            $srids[] = $_val['sr_id'];
        }
        // 查询所有的备注
        $detail_list = $serv_detail->get_detail_list($srids, $params['udate']);

        // $record_detail_model = D('Sign/SignDetail');
        //查询签到备注信息
//            $cd = array(
//                'sr_id' => $sd['sr_id']
//            );
//            $detail = $record_detail_model->list_by_conds($cd);


        // 整合备注到签到记录
        if (!empty($detail_list)) {
            foreach ($data as &$_form) {
                $_form['detail_list'] = array();
                foreach ($detail_list as $_detail) {
                    // 绑定的签到记录id等于当前记录id
                    if ($_form['sr_id'] == $_detail['sr_id']) {
                        $_form['detail_list'][] = $_detail;
                    }
                }
            }
        } else {
            //备注设置为空
            foreach ($data as &$_da) {
                $_da['detail_list'] = array();
            }
        }

        $data = array_values($data);
        $res['sign_data'] = $data;
        $res['work_time'] = $work_time;
        $res['batch_name'] = '未知班次';
        $batch = $serv_batch->get($params['sr_batch']);
        //Log::record('班次信息:'.var_export($batch,true));
        if(!empty($batch)){
            $res['batch_name'] = $batch[0]['name'];
        }

		return $res;
	}

	/**
	 * 获取外部考勤方法
	 * @param array $params 接收参数
	 */
	private function __get_location($params) {

		$serv_location = D('Sign/SignLocation', 'Service');
		$serv_att = D('Sign/SignAttachment', 'Service');
		$page = $params['page'];
		$limit = $params['limit'];
		// 判断是否为空
		if (empty($params['page'])) {
			$page = 1;
			$params['page'] = 1;
		}
		if (empty($params['limit'])) {
			$limit = 10;
			$params['limit'] = 10;
		}
		// 分页参数
		list($start, $limit, $page) = page_limit($page, $limit);
		// 分页参数
		$page_option = array($start, $limit);
		$record = $serv_location->get_out_record($params, $page_option);
		// 如果有数据判断有无关联图片
		if (!empty($record)) {
			$record = $serv_att->out_img($record);
			//格式输出信息
			foreach ($record as $_record) {
				$tmp = array();
				$tmp['sl_id'] = $_record['sl_id'];
				$tmp['m_username'] = $_record['m_username'];
				$tmp['_sl_signtime'] = rgmdate($_record['sl_signtime'], 'H:i:s');
				$tmp['sl_address'] = $_record['sl_address'];
				$tmp['attachs'] = $_record['attachs'];
				$tmp['sl_note'] = $_record['sl_note'];
				$record_format[] = $tmp;
			}
		} else {
			$record_format = array();
		}

		return array($record_format, $limit, $page);
	}


	/**
	 * 获取一个月的签到情况
	 * @return 一个月格式后数据
	 */
	public function Cal_get() {

		// 实例化
		$params = I('get.');
		// 用户id
        $params['m_uid'] = $this->_login->user['m_uid'];
        $dep_id = $params['dep_id'];
        if(empty($dep_id)){
            E('_ERR_SIGN_DEP_IS_NOT_NULL');
            return false;
        }

        // 验证传入的部门是否是用户所在的部门
        $this->__check_sbid($dep_id);


		list($data, $stime, $etime) = $this->_serv_record->get_cal($params);

		$absent = '';
		// 查询用户签到信息
		if (empty($data)) {
            // 没有签到记录
            $absent = 'all';
		}

		// 处理签到数据
        $params['stime'] = $stime;
        $params['etime'] = $etime;

        $cache = &Cache::instance();
        // 判断后台是否开启 外出考勤计为出勤 开关
        $cache_sign_setting = $cache->get('Sign.setting');
		$result = $this->_serv_record->dataformat($absent, $params, $data, $cache_sign_setting);

        //Log::write("----1--- ".var_export($result, true));
		// 统计结果
		list($absent, $unusual, $normal) = $this->_serv_record->get_absent($result, $cache_sign_setting);

        $cache = &Cache::instance();
        $cache_departments = $cache->get('Common.department');
        $dep_name = '';
        foreach ($cache_departments as $k => $v) {
            if($k == $dep_id){
                $dep_name = $v['cd_name'];
                break;
            }
        }

		$this->_result = array('list' => $result, 'absent' => $absent, 'unusual' => $unusual, 'normal' => $normal, 'dep_id' => $dep_id, 'dep_name' => $dep_name);

        //Log::write("----5--- ".var_export($this->_result, true));
		return true;
	}


    public function get_deps(){
        // 登录人 信息
        $serv_mem = D('Common/Member', 'Service');
        $member = $serv_mem->get($this->_login->user['m_uid']);


        $cache = &Cache::instance();
        $cache_departments = $cache->get('Common.department');

        // 人员 部门 关联表
        $serv_department = D('Common/MemberDepartment', 'Service');
        $departments = $serv_department->list_by_uid($this->_login->user['m_uid']);

        $res = array();
        // 去 匹配关联的部门ID
        foreach ($departments as $_k => $_v) {
            if ($member['m_uid'] == $_v['m_uid']) {
                $t = array(
                    'cd_id' => $_v['cd_id']
                );
                foreach ($cache_departments as $k => $v) {
                    if($k == $_v['cd_id']){
                        $t['cd_name'] = $v['cd_name'];
                        break;
                    }
                }
                $res[]= $t;

            }
        }

        $this->_result =  array('list'=>$res);

        return true;
    }


}


