<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/9/17
 * Time: 下午4:30
 */
namespace Sign\Common;
use Think\Log;

class Handle extends \Com\Cache {
	// 地球半径, 单位(米) */
	const EARTH_RADIUS = 6378137;
	// 二维码签到 */
	const TYPE_QRCODE = 'qrcode';
	// 地理位置签到 */
	const TYPE_LOCATION = 'location';
	// ip地址 */
	const TYPE_IP = 'ip';
	// 错误号 */
	public $error;
	// 过期时间戳
	protected $_expires_ts;
	// 签到配置
	protected $_set;
	/** 最大过期时间间隔, 单位:秒 */
	const MAX_EXPIRES = 300;
	/** 迟到时间限制 */
	protected $_late_range = 0;
	/** 早退时间限制 */
	protected $_leave_early_range = 0;

	/** 上班状态值 */
	const TYPE_ON = 1;
	/** 下班状态值 */
	const TYPE_OFF = 2;
	/** 上报状态值 */
	const TYPE_UP = 3;

	/** 正常出勤 */
	const STATUS_WORK = 1;
	/** 迟到 */
	const STATUS_LATE = 2;
	/** 早退 */
	const STATUS_LEAVE = 4;
	/** 旷工 */
	const STATUS_ABSENT = 8;
	/** 请假 */
	const STATUS_OFF = 16;
	/** 出差 */
	const STATUS_EVECTION = 32;
	/** 删除 */
	const STATUS_REMOVE = 64;

	/** 实例化 */
	protected $_serv_weixinlocation = null;
	protected $_serv_record = null;
	/** 当前班次的部门ID */
	protected $_cdid = null;
	// 签到类型
	protected $_type = "";

    // 签到标识
    protected $_flag = "";

	// 用户信息
	protected $_user = array();

    // 排班id
    protected $_ssid = "";

    // 排班规则
    protected $_schedule_info = '';
    // 默认未早退
    protected $_is_workoff = false;

    // 当值等于1时，提交的强制签退
    protected $_forced = '';


	/**
	 * 签到/签退操作
	 * @param int    $record 签到/签退记录
	 * @param array  $user 用户信息
	 * @param string $type 打卡类型
	 * @param $sbid 当前班次的部门ID
	 * @return boolean
	 */
	public function sign(&$record, $user, $type, $location = array(), $info, $cdid, $flag, $_ssid, $schedule_info, $is_workoff, $forced) {

		$this->_serv_weixinlocation = D('Common/WeixinLocation', 'Service');
		$this->_serv_record = D('Sign/SignRecord', 'Service');
		// 当前签到的部门ID
		$this->_cdid = $cdid;
		/** 取打卡配置 */
		$this->_set = $info;
        // 迟到规则
		$this->_late_range = (int)$this->_set['come_late_range'];
        // 早退规则
		$this->_leave_early_range = (int)$this->_set['leave_early_range'];
		// 签到类型
		$this->_type = $type;
		// 用户信息
		$this->_user = $user;

        $this->_flag = $flag;

        $this->_ssid = $_ssid;

        $this->_schedule_info = $schedule_info;

        $this->_is_workoff = $is_workoff;

        $this->_forced = $forced;

		// 签到
		$this->_location_sign($record, $location, $info);

		return true;
	}

	// 地理位置签到
	protected function _location_sign(&$record, $location, $info) {

		// 存在经纬度信息且数据有效未过期，则尝试使用经纬度获取
		$record ['sr_address'] = $location['address'];
		$record ['sr_longitude'] = $location ['longitude'];
		$record ['sr_latitude'] = $location ['latitude'];

		//判断是否超出签到范围
		$record['sr_addunusual'] = 0;
		if (1 == $this->_schedule_info['range_on']) {
			if ($this->__gain_overrange($location, $this->_schedule_info) != 0) {
				$record ['sr_addunusual'] = 1;
			}
		}

		return $this->_record_sign($record, $info, $location);
	}

	/**
	 * 签到时 获取是否超出 签到设置位置
	 * @param $location 地址细心
	 * @param $info 班次信息
	 * @return int
	 */
	private function __gain_overrange($location, $info) {

		$sign_range = $info['address_range']; // 考勤范围

		$sr_longitude = $location['longitude'];
		$sr_latitude = $location['latitude'];
		$bat_longitude = $info['longitude'];
		$bat_latitude = $info['latitude'];

		// 坐标转换
		$maps = new \Com\Location();
		$maps->conver_to_baidu($sr_latitude, $sr_longitude);

		$sign = new \Sign\Common\Handle();
		$length = $sign->get_distance($sr_latitude, $sr_longitude, $bat_latitude, $bat_longitude);

        //Log::record('$length ' . $length);
        //Log::record('$sign_range ' . $sign_range);
		$overranger = 0;
		if ($length > $sign_range) {
			$overranger = 1;
		}

		return $overranger;
	}

    /**
     * 防止重复签到
     * @param $type
     * @return bool
     */
    protected function _check_sign_count($btime, $sbid, $type){
        $signs = $this->_serv_record->get_by_time($btime, $sbid, $this->_user['m_uid'], $type);
        if(count($signs) >= 1){
            // 防止部门变更，不能签到、签退
            if($signs[0]['schedule_id'] != $this->_ssid){
                E('_ERR_SIGN_DEPT_ERROR');
                return false;
            }

            // 今日已经签过到, 不能重复签
            if(self::TYPE_ON == $type){
                E('_ERR_SIGN_DUPLICATE');
            }else{
                E('_ERR_SIGN_OFF_DUPLICATE');
            }
            return false;
        }
        return $signs;
    }

    /**
     * 获取签到时间
     */
    protected function _get_sign_data($btime, $sbid, $type){
        $signs = $this->_serv_record->get_by_time($btime, $sbid, $this->_user['m_uid'], $type);
        return $signs;
    }

	/**
	 * 签到
	 * @param $record
	 * @param $info
	 * @return bool
	 */
	protected function _record_sign(&$record, $info, $location) {

        // 当前时间
        $ymd = rgmdate(NOW_TIME, 'Y-m-d');
        // 正常签退时间
        $etime = 0;
        // 允许签退的最大时间
        $_end_time = 0;
        // 加班时长
        $overtime = 0;
        $tmp_late_work_time = null; // 最晚上班时间 已格式化为当前时间戳
        $rep_late_time = null; // 迟到时长
        $rep_early_time = null; // 早退时长
        $rep_work_time = null; // 出勤时长

        // 旷工时长
        $sr_absenteeism_range_time = 0;
        $work_begin = $info['work_begin'];
        $work_end = $info['work_end'];



        // 签退规则处理
        $t_s = rgmdate($work_begin, 'Y-m-d');
        $t_s_h = rgmdate($work_begin, 'H:i');
        $t_e = rgmdate($work_end, 'Y-m-d');
        $t_e_h = rgmdate($work_end, 'H:i');
        // 隔天班次时间处理
        $sign_tomorrow_time = null;

        // 常规查询条件
        $trend_btime = rstrtotime($ymd . ' ' . $t_s_h) - $info['sign_start_range'];
        // 弹性查询条件
        $elastic_btime = rstrtotime($ymd . ' ' . $t_s_h);
        // 签到
        if ($this->_type == self::TYPE_ON) {
           // Log::record('111111');
            // 常规
            if($info['type'] == 1){
				//验证签到时间不能在打卡结束范围之外
                // 首先计算好签退时间(次日)
                if(differ_days($t_s, $t_e) > 0){
                    $over_m = rgmdate(rstrtotime($ymd) + 86400, 'Y-m-d');
                    $etime = rstrtotime($over_m . ' ' . $t_e_h) + $info['sign_end_range'];

                  //  Log::record('签到 ----flag ' . $this->_flag);
                    if($this->_flag == 2){ // 跨天
                        // 为了控制三班倒的情况，查询要把次日签退时间归到前日的签到班次中，把当前签到时间-1天保存
                        $sign_tomorrow_time = NOW_TIME - 86400;
                        $ymd = rgmdate($sign_tomorrow_time, 'Y-m-d');
                        // 隔天签到需要重置查询条件
                        $trend_btime = rstrtotime($ymd . ' ' . $t_s_h) - $info['sign_start_range'];
                    }else{
                        $sign_tomorrow_time = NOW_TIME;
                    }
                }else{
                    // 开始结束时间都在当天
                    // 计算最晚签退时间
                    $etime = rstrtotime($ymd . ' ' . $t_e_h)  + $info['sign_end_range'];
                    if($this->_flag == 2) { // 跨天
                        $sign_tomorrow_time = NOW_TIME - 86400;
                        $tomorrow_ymd = rgmdate($sign_tomorrow_time, 'Y-m-d');
                        $etime = rstrtotime($tomorrow_ymd . ' ' . $t_e_h)  + $info['sign_end_range'];

                        // 如果是当天班次，最晚签到时间跨天
                        if(differ_days($ymd, rgmdate($etime, 'Y-m-d')) > 0){
                            $sign_tomorrow_time = NOW_TIME;
                        }else{
                            // 签到时间和最晚签退时间在同一天，肯定是跨天
                            $sign_tomorrow_time = NOW_TIME - 86400;

                        }
                    }else{
                        $sign_tomorrow_time = NOW_TIME;
                    }

                }

				if(NOW_TIME > $etime){
					E('_ERR_SIGN_ON_TIME_ERROR');
					return false;
				}

                // 检测签到地址范围
                $this->check_sign_rule($record, $location);

                // 获取当天上班打卡记录
                $this->_check_sign_count($trend_btime, $info['sbid'], self::TYPE_ON);
                $tmp_status = $this->on_status(NOW_TIME, $t_s_h, $ymd);
                $status = $tmp_status['status'];
                $rep_late_time = $tmp_status['rep_late_time'];
            }else{ // 弹性
                // 首先计算好签退时间(次日)
                $etime = rstrtotime($ymd . ' ' . $t_e_h)  + $info['sign_end_range'];

                // 上下班时间跨天
                if(differ_days($t_s, $t_e) > 0){
                    if($this->_flag == 2) { // 跨天
                        $sign_tomorrow_time = NOW_TIME - 86400;
                        $ymd = rgmdate($sign_tomorrow_time, 'Y-m-d');

                        // 隔天签到需要重置查询条件
                        $elastic_btime = rstrtotime($ymd . ' ' . $t_s_h);
                    }else{
                        $sign_tomorrow_time = NOW_TIME;
                    }
                }else{

                    if($this->_flag == 2) { // 跨天
                        $sign_tomorrow_time = NOW_TIME - 86400;
                        $tomorrow_ymd = rgmdate($sign_tomorrow_time, 'Y-m-d');
                        $etime = rstrtotime($tomorrow_ymd . ' ' . $t_e_h)  + $info['sign_end_range'];
                        // 如果是当天班次，最晚签到时间跨天
                        if(differ_days($ymd, rgmdate($etime, 'Y-m-d')) > 0){
                            $sign_tomorrow_time = NOW_TIME;
                        }else{
                            // 签到时间和最晚签退时间在同一天，肯定是跨天
                            $sign_tomorrow_time = NOW_TIME - 86400;

                        }
                    }else{
                        $sign_tomorrow_time = NOW_TIME;
                    }
                }

				//验证是否超过打卡结束范围
				if(NOW_TIME > $etime){
					E('_ERR_SIGN_ON_TIME_ERROR');
					return false;
				}

                // 检测签到地址范围
                $this->check_sign_rule($record, $location);

                // 获取当天上班打卡记录
                $this->_check_sign_count($elastic_btime, $info['sbid'], self::TYPE_ON);

                // 启用最晚上班时间
                if($info['late_work_time_on'] == 1){
                    // 最晚上班时间小时分钟
                    $late_work_time = rgmdate($info['late_work_time'], 'H:i');
                    $tmp_late_work_time = $ymd . ' ' . $late_work_time;
                    $tmp_late_work_time = rstrtotime($tmp_late_work_time);
                    $tmp_status = $this->on_status_elastic(NOW_TIME, $tmp_late_work_time);
                    $status = $tmp_status['status'];
                    $rep_late_time = $tmp_status['rep_late_time'];
                }else{
                    // 正常出勤
                    $status = self::STATUS_WORK;
                }
            }
        } else { // 签退
            // 常规
            if($info['type'] == 1){
                // 首先计算好签退时间(次日)
                if(differ_days($t_s, $t_e) > 0){
                    if($this->_flag == 2) { // 跨天
                        // 为了控制三班倒的情况，查询要把次日签退时间归到前日的签到班次中，把当前签到时间-1天保存
                        $sign_tomorrow_time = NOW_TIME - 86400;
                        $ymd = rgmdate($sign_tomorrow_time, 'Y-m-d');
                        // 隔天签到需要重置查询条件
                        $trend_btime = rstrtotime($ymd . ' ' . $t_s_h) - $info['sign_start_range'];
                    }else{
                        $sign_tomorrow_time = NOW_TIME;
                    }

                    $over_m = rgmdate(rstrtotime($ymd) + 86400, 'Y-m-d');
                    $etime = rstrtotime($over_m . ' ' . $t_e_h) - $info['leave_early_range'];

                    $_end_time = rstrtotime($over_m . ' ' . $t_e_h) + $info['sign_end_range'];
                }else{
                    // 早退时间范围
                    $etime = rstrtotime($ymd . ' ' . $t_e_h) - $info['leave_early_range'];
                    // 结束签退时间
                    $_end_time = rstrtotime($ymd . ' ' . $t_e_h) + $info['sign_end_range'];

                    if($this->_flag == 2) { // 跨天
                        $sign_tomorrow_time = NOW_TIME - 86400;
                        $tomorrow_ymd = rgmdate($sign_tomorrow_time, 'Y-m-d');
                        $_end_time = rstrtotime($tomorrow_ymd . ' ' . $t_e_h)  + $info['sign_end_range'];

                        // 如果是当天班次，最晚签到时间跨天
                        if(differ_days($ymd, rgmdate($_end_time, 'Y-m-d')) > 0){
                            $sign_tomorrow_time = NOW_TIME;
                        }else{
                            // 签到时间和最晚签退时间在同一天，肯定是跨天
                            $sign_tomorrow_time = NOW_TIME - 86400;
                        }

                        // 班次不是跨天，但是签到时间达到跨天的条件，需要重置隔天签到的查询条件
                        $trend_btime = rstrtotime($tomorrow_ymd . ' ' . $t_s_h) - $info['sign_start_range'];
                    }else{
                        $sign_tomorrow_time = NOW_TIME;
                    }
                }

                $this->_check_sign_count($trend_btime, $info['sbid'], self::TYPE_OFF);

                // 判断打下班卡时间是否超过打卡时间
                if (NOW_TIME > $_end_time) {
                    E('_ERR_SING_END');
                    return false;
                }

                // 检测签到地址范围
                $this->check_sign_rule($record, $location);

                // 查询上一次的签到时间，和这次签退时间相减，得出出勤时长
                //Log::record('$trend_btime '. $trend_btime, Log::ALERT);
                $signs = $this->_get_sign_data($trend_btime, $info['sbid'], self::TYPE_ON);
                $rep_sr_signtime = $signs[0]['sr_signtime'];

                $tmp_status = $this->off_status(NOW_TIME, $etime);
                $status = $tmp_status['status'];
                // 符合签退规则(正常出勤)
                if ($status == 1) {
                    // 启用加班
                    if($info['late_range_on'] == 1){
                        // 签退时间
                        $_end_time = rstrtotime($ymd . ' ' . $t_e_h);
                        // 加班规则
                        $late_range = $info['late_range'];
                        // 签到时间小于班次的签退时间，才计算加班时长
                        if($rep_sr_signtime < $_end_time){
                            // 计算加班规则
                            $tmp_end_time = $_end_time +  $late_range;
                            // 计算加班时长
                            $overtime = NOW_TIME - $tmp_end_time;
                            if($overtime < 0){
                                $overtime = 0;
                            }
                        }
                    }
                }else{
                    $rep_early_time = $tmp_status['rep_early_time'];
                }
                // 计算出勤时长
                $rep_work_time = NOW_TIME - $rep_sr_signtime;
               // Log::record('$rep_sr_signtime '. $rep_sr_signtime, Log::ALERT);
            }else{ // 弹性
                // 首先计算好签退时间(次日)
                if(differ_days($t_s, $t_e) > 0){
                    if($this->_flag == 2) { // 跨天
                        $sign_tomorrow_time = NOW_TIME - 86400;
                        $ymd = rgmdate($sign_tomorrow_time, 'Y-m-d');
                        // 隔天签到需要重置查询条件
                        $elastic_btime = rstrtotime($ymd . ' ' . $t_s_h);
                    }else{
                        $sign_tomorrow_time = NOW_TIME;
                    }
                    $over_m = rgmdate(rstrtotime($ymd) + 86400, 'Y-m-d');
                    $_end_time = rstrtotime($over_m . ' ' . $t_e_h);
                }else{
                    // 当日签退时间
                    $_end_time = rstrtotime($ymd . ' ' . $t_e_h);

                    // 如果是当天班次，最晚签退时间跨天,需要验证签退时间是否跨天
                    if(differ_days($ymd, rgmdate($_end_time, 'Y-m-d')) > 0){
                        $sign_tomorrow_time = NOW_TIME;
                    }else{
                        // 如果上下班时间为同一天
                        if(differ_days($t_s, $t_e) == 0){
                            $sign_tomorrow_time = NOW_TIME;
                        }else{
                            $sign_tomorrow_time = NOW_TIME - 86400;
                        }
                    }
                }

                $this->_check_sign_count($elastic_btime, $info['sbid'], self::TYPE_OFF);

                // 判断打下班卡时间是否超过打卡时间
                if (NOW_TIME > $_end_time) {
                    E('_ERR_SING_END');
                    return false;
                }

                // 检测签到地址范围
                $this->check_sign_rule($record, $location);

                // 查询上一次的签到时间，和这次签退时间相减，得出工作时长
                $sign_data = $this->_get_sign_data($elastic_btime, $info['sbid'], self::TYPE_ON);
                $sr_signtime = $sign_data[0]['sr_signtime'];

                // 启用旷工规则
                if($info['absenteeism_range_on'] == 1){
                    $hours = $info['min_work_hours'];
                    // 最小工作时长百分比
                    if($info['absenteeism_range'] == 50){
                        $hours =  $info['min_work_hours'] * $info['absenteeism_range'] / 100;
                    }
                    $cur_hour = differ_hours($sr_signtime, NOW_TIME);
                    // 旷工时长
                    $sr_absenteeism_range_time = ($hours - $cur_hour) * 3600;

					$status = self::STATUS_WORK;
					if($sr_absenteeism_range_time > 0){//早退
						$status = self::STATUS_LEAVE;
					}

                }else{
                    // 正常出勤
                    $status = self::STATUS_WORK;
                }
                // 计算出勤时长
                $rep_work_time = NOW_TIME - $sr_signtime;
            }
        }

      //  Log::record('$work_begin ' . $work_begin, Log::ALERT);

      //  Log::record('$record begin ' . var_export($record, true), Log::ALERT);

        //打卡信息入库
        $record = array_merge($record, array(
            'm_uid' => $this->_user['m_uid'],
            'm_username' => $this->_user['m_username'],
            'cd_id' => $this->_cdid,
            'sr_signtime' => NOW_TIME,
            'sr_ip' => get_client_ip(),
            'sr_type' => $this->_type,
            'sr_sign' => $status,
            'sr_longitude' => $record['sr_longitude'],
            'sr_latitude' => $record['sr_latitude'],
            'sr_address' => $record['sr_address'],
            'sr_overtime' => $overtime,
            'sr_addunusual' => $record['sr_addunusual'],
            'sr_batch' => $info['sbid'],
            'sr_absenteeism_range_time' => $sr_absenteeism_range_time,
            'sr_sign_start_range' => $info['sign_start_range'],
            'sr_sign_end_range' => $info['sign_end_range'],
            'sr_come_late_range' => $info['come_late_range'],
            'sr_leave_early_range' => $info['leave_early_range'],
            'sr_late_range' => $info['late_range'],
            'sr_late_work_time' => $tmp_late_work_time,
            'ba_type' => $info['type'],
            'sr_absenteeism_range' => $info['absenteeism_range'],
            'sr_min_work_hours' => $info['min_work_hours'],
            'sr_work_status' => $info['work_status'],
            'sr_work_begin' => $info['work_begin'],
            'sr_work_end' => $info['work_end'],
            'rep_late_time' => $rep_late_time,
            'rep_early_time' => $rep_early_time,
            'rep_work_time' => $rep_work_time,
            'sr_created' => $sign_tomorrow_time,
            'schedule_id' => $this->_ssid,
            'sign_schedule_id' => $info['sign_schedule_id']
        ));

       // Log::record('$record end ' . var_export($record, true), Log::ALERT);

        $record_result = $this->_serv_record->insert_record($record);
        $record['sr_id'] = $record_result;
		return true;
	}

    /**
     * 检测签到条件是否正常
     */
    private function check_sign_rule($record, $location){
        // 非强制签到
        if($this->_forced != 1){
            // 开启考勤范围
            if (1 == $this->_schedule_info['range_on']) {
               // Log::record('_range_on '. $this->_range_on, Log::ALERT);
                if ($record ['sr_addunusual'] == 1 && $this->_is_workoff) {
                    // 当前签退时间为早退并且超出签到范围
                    E(L('_ERR_LEAVE_EARLY_OVER_SIGN_RANGE', array('address' => $location['address'])));
                    return false;
                } else if ($record['sr_addunusual'] == 1) {
                    // 超出签到范围
                    E(L('_ERR_OVER_SIGN_RANGE', array('address' => $location['address'])));
                    return false;
                }
            }
        }
    }



	/**
	 * 获取当前下班卡状态
	 * @param $ts 当前时间戳
	 * @param $work_end 签退记录
	 * @return int
	 */
	public function off_status($ts, $work_end) {
        // 默认正常出勤
		$status = 1;
		if ($ts < $work_end) {
            // 早退
			$status = self::STATUS_LEAVE;
            // 早退时长
            $rep_early_time = $work_end - $ts;
        }

        return array('status'=> $status, 'rep_early_time'=>$rep_early_time);
	}

    /**
 * 获取当前上班卡状态常规
 * @param $ts 当前
 * @param $t_s_h 签到时间 9:00 格式
     * @param $ymd 班次的年月日(昨天or当天)
 * @return int
 */
    public function on_status($ts, $t_s_h, $ymd) {
        // 默认正常
        $status = 1;
        $tmp_begin_time = rstrtotime($ymd . ' ' . $t_s_h) + $this->_late_range;
        if($ts > $tmp_begin_time){
            // 迟到
            $status = self::STATUS_LATE;
            // 迟到时长
            $rep_late_time = $ts - $tmp_begin_time;
        }

        return array('status'=> $status, 'rep_late_time'=>$rep_late_time);
    }


    /**
     * 获取当前上班卡状态（弹性）
     * @param $ts 当前时间
     * @param $work_begin 签到时间
     * @return int
     */
    public function on_status_elastic($ts, $tmp_late_work_time) {
        // 默认正常
        $status = 1;
        if($ts > $tmp_late_work_time){
            // 迟到
            $status = self::STATUS_LATE;
            // 迟到时长
            $rep_late_time = $ts - $tmp_late_work_time;
        }

        return  array('status'=> $status, 'rep_late_time'=>$rep_late_time);
    }

    /**
     * 将时间戳时分转换为当前日期的时分
     */
    protected function _to_current_time($ymd, $time){
        $tmp_start = rgmdate($time, 'H:i');
        $tmp_start = $ymd . ' ' . $tmp_start;
        return rstrtotime($tmp_start);
    }

	/** 把时间转成对应的秒数 */
	protected function _to_seconds($hi) {

		@list($h, $i) = explode(':', $hi);

		return $h * 3600 + $i * 60;
	}

	/**
	 * 格式数字为时间
	 * @param unknown $num
	 * @return string
	 */
	public function formattime($num) {

		if (strlen($num) == 0) {
			$time = '00:00';

			return $time;
		} elseif (strlen($num) == 1) {
			$time = '00:0' . $num;

			return $time;
		} elseif (strlen($num) == 2) {
			$time = '00:' . $num;

			return $time;
		} elseif (strlen($num) == 3) {
			$hour = substr($num, 0, 1);
			$min = substr($num, 1, 2);
			$time = '0' . $hour . ':' . $min;

			return $time;
		} else {
			$hour = substr($num, 0, 2);
			$min = substr($num, 2, 2);
			$time = $hour . ':' . $min;

			return $time;
		}
	}

	/**
	 * 角度 => 弧度
	 */
	function rad($dis) {

		return round($dis * (M_PI / 180), 6);
	}

	/**
	 * 根据IP获取地址信息
	 * @param string $ip
	 * @return string|boolean
	 */
	protected function _get_address_by_ip($ip) {

		$ip2address = new \Org\Net\Ip2address();
		if (!$ip2address->get($ip)) {
			return '无法获取地理位置';
		}

		if (empty ($ip2address->result ['address'])) {
			return '';
		}

		return $ip2address->result ['address'];
	}

	/**
	 * 计算经纬度之间的距离
	 * @param $lat1
	 * @param $lng1
	 * @param $lat2
	 * @param $lng2
	 * @return float|int
	 */
	function get_distance($lat1, $lng1, $lat2, $lng2) {
//         Log::record('签到 $lat1 '.$lat1, Log::ALERT);
//        Log::record('签到 $lng1 '.$lng1, Log::ALERT);
//
//        Log::record('设置 $lat2 '.$lat2, Log::ALERT);
//        Log::record('设置 $lng2 '.$lng2, Log::ALERT);

		$lat1 = round($lat1, 6);
		$lng1 = round($lng1, 6);
		$lat2 = round($lat2, 6);
		$lng2 = round($lng2, 6);
		$radLat1 = $this->rad($lat1);
		$radLat2 = $this->rad($lat2);
		$a = $radLat1 - $radLat2;
		$b = $this->rad($lng1) - $this->rad($lng2);
		$s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
		$s = round($s * self::EARTH_RADIUS, 0);

		return $s;
	}

	/**
	 * 将大于24点的时间格式化
	 * @param unknown $ymd
	 * @param unknown $num
	 * @return number
	 */
	public function totime($ymd, $num) {

		$time = $num;
		$h = substr($time, 0, 2);
		// 2015-08-15 25:23;
		if ($h - 24 > 0) {
			$diff = $h - 24;
			$m = substr($time, 3, 2);
			$formattime = strtotime('+1 day', strtotime($ymd)) + $diff * 3600 + $m * 60 - 8 * 3600;
		}

		return $formattime;
	}
}
