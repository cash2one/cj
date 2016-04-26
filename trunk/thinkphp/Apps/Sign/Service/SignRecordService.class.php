<?php
/**
 * SignRecordService.class.php
 * $author$
 */

namespace Sign\Service;
use Common\Common\Cache;
use Think\Log;
use Sign\Model\SignScheduleModel;
use Common\Common\Department;

class SignRecordService extends AbstractService {

	/*
	 * 状态 absent 0工作日
	 * 1正常， 2迟到， 4，早退
	 * 1旷工
	 * 2休息日
	 */
	private $weeks = array('日', '一', '二', '三', '四', '五', '六');

    private $_d_schedule = null;

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Sign/SignRecord");

        $this->_d_schedule = D("Sign/SignSchedule");
	}

	/**
	 * 获取某人某一天签到记录
	 *
	 * @param array $params 传入参数
	 * @return array $record 签到记录
	 */
	public function get_sign_record($params) {

		$record = $this->_d->get_sign_record($params);
		return $record;
	}

	/**
	 * 计算时间
	 *
	 * @param array $params 用户传入数据
	 * @return multitype:unknown $data
	 */
	public function get_cal($params) {

		if (empty($params['udate'])) {
            // 默认查询当月考勤
			$params['udate'] = rgmdate(NOW_TIME, 'Y-m-d');
		}
		// 获取时间月份
		$year = substr($params['udate'], 0, 4);
		$month = substr($params['udate'], 5, 2);
		$firstDay = mktime(0, 0, 0, $month, 1, $year);
        // 当月第一天
		$params['stime'] = rgmdate($firstDay, 'Y-m-d');

        // 当月最后一天
        $params['etime'] = rgmdate($firstDay, 'Y-m-t');

		// 视图需要的数据
		list($data, $stime, $etime) = $this->make_view($params);

		return array(0 => $data, 1 => $stime, 2 => $etime);
	}

	/**
	 * 获取数据
	 *
	 * @param $params 用户传入数据
	 * @return $data 签到数据 $params['stime'] 开始时间 $params['etime'] 结束时间
	 */
	public function make_view($params) {
		// 查询操作
		$data = array();
		$data = $this->_d->list_by_condition_new($params);
		return array(0 => $data, 1 => $params['stime'], 2 => $params['etime']);
	}

	/**
     * 处理公司、外出考勤核心方法
     * 统计出勤状态数
	 *
	 * @param $absent 有无签到记录标识
	 * @param array $data 用户签到数据
	 * @param string $stime 日期开始时间y-m-d
	 * @param string $etime 日期结束时间y-m-d
     * @param $cache_sign_setting 查看异常统计数据开关
     * @param $dep_id 部门id
	 * @return $result 格式后的数组
	 */
	public function dataformat($absent, $params, $data, $cache_sign_setting) {

		// 构造虚拟的日期数组
		$datelist = $this->get_datelist($params['stime'], $params['etime']);
		$result = array();
		// 公司考勤没有记录
		if ($absent == 'all') {
			// 查询外出考勤
            //$outData = $this->countOutData($params, $cache_sign_setting);
            // 没有外勤数据, 和之前的处理方式一样
           // if(empty($outData)){
                $result = $this->__no_data($datelist, $params['dep_id']);
          //  }else{
                // 有外勤数据
              //  $result = $this->__no_out_data($outData, $datelist);
          //  }
		} else {
            // 有公司考勤数据
            // 将签到数据，把日期作为key存储起来
			$list = array();
			// 整合已有的数据
			foreach ($data as $_data) {
				$datime = rgmdate($_data['sr_created'], 'Y-m-d');
				$list[$datime][] = $_data;
			}

			// 处理公司考勤数据
			foreach ($list as $datek => $_date) {
                // 把签到数据填充到封装的当月天数数组中
				foreach ($datelist as $_d) {
					// 小于今天日期
					if (rstrtotime($_d) < NOW_TIME) {
						$result = $this->__in_time($result, $_d, $list, $params['dep_id']);
					} else {
                        // 大于今天日期
						$result[$_d]['absent'] = 2; // 休息日
					}
				}
			}

            // 处理外出考勤数据
            $list_out = array();
            $outData = $this->countOutData($params, $cache_sign_setting);
           // Log::write("----1--- ".var_export($outData, true));
            if(!empty($outData)){
                // 公司考勤数据和外出考勤数据整合, 如果公司考勤数据异常，但是有外出考勤，则出勤+1
                foreach ($outData as $_data) {
                    $datime = rgmdate($_data['sl_signtime'], 'Y-m-d');
                    $list_out[$datime][] = $_data;
                }
                //Log::write("----2--- ".var_export($list_out, true));
                // 迭代公司考勤数据
                foreach ($result as $_k => $_v) {
                    // 有公司考勤数据，同时也有外出考勤的数据
                    if (isset($list_out[$_k])) {
                         $result[$_k]['absent'] = 0; // 工作日
                         $result[$_k]['flag'] = 9; // 当天有外勤记录
                    }
                }
            }
		}
       // Log::write("----3--- ".var_export($result, true));
		return $result;
	}


    /**
     * 检查班次信息
     * @param $dep_id
     * @return array
     */
    public function check_batch_info($dep_id){
        // 校验当前部门是否有排班信息, 查询非禁用中的排班
        $conds = array(
            'cd_id' => $dep_id,
            'enabled' => 1
        );

        // 查询当前部门的排班
        $bt_sc = $this->_d_schedule->get_schedule_for_deps($conds);
        if(empty($bt_sc)){
            // 当前部门排班不存在, 校验一下顶级部门有没有排班
            $parent_dept_ids = $this->list_parent_departments_by_cdid($dep_id);
            //查询所有上级部门的排班
            $schedule_list = $this->list_schedules_by_cdids($parent_dept_ids);
            if(!empty($schedule_list)){
                $bt_sc[] = $schedule_list[0];
            }else{
                // 顶级部门也没有排班 查询默认排班
                $conds['cd_id'] = 0;
                $bt_sc = $this->_d_schedule->get_schedule_for_deps($conds);
               // if(empty($bt_sc)){
                  //  E('_ERR_SIGN_SCHEDULE_NOEXIST_ERROR');
                   // return false;
               // }
            }
        }
        return $bt_sc;
    }


    /**
     * 统计外出考勤数
     * @param $params
     *
     */
    public function countOutData($params, $cache_sign_setting){

        // 判断后台是否开启 外出考勤计为出勤 开关
        if(!empty($cache_sign_setting['out_sign_include']) && $cache_sign_setting['out_sign_include'] == 2){
            // 查询外出考勤数
            $signLocation = D("Sign/SignLocation", "Service");
            $outData = $signLocation->list_by_condition_new($params);
        }
        return $outData;
    }

    /**
     * 找到今天是第几天的班次
     * @param $type
     * @param $date
     * @param $t 指定时间
     * @return int
     */
    public function find_today_batch_index($type, $date, $cycle_num, $t){
        $result = -1;
        if($type == SignScheduleModel::CYCLE_UNIT_DAY){
            $result = find_schedule_day($t, $date,$cycle_num);
        }elseif($type == SignScheduleModel::CYCLE_UNIT_WEEK){
			$week_array=array(7,1,2,3,4,5,6);
			$result = $week_array[rgmdate($t, "w")];
        }else{
            $d = rgmdate(rstrtotime($t), "d");
            $result = intval($d);
        }

        return $result;
    }

    /**
     * 验证是否在上班日（报表）
     * @param $schedule_everyday_detail
     * @param $batch_index
     * @param $remove_day 排除节假日
     * @param $add_work_day 增加上班日期
     * @param $cur_time 验证的时间戳
     * @param $batch_array 当员工没有考勤记录时，并且只有一个排班，报表中需要显示班次名称
     * @return bool false: 休息 true: 工作日
     */
    private function __is_in_workday($schedule_everyday_detail, $batch_index, $remove_day='', $add_work_day='', $cur_time='', &$batch_array) {
        // 排班明细时间段
        $schedule_everyday_detail = unserialize($schedule_everyday_detail);
        $t = $schedule_everyday_detail[$batch_index];
        $tmp_array = array_column($t, 'id');

       // if(rgmdate($cur_time, 'Y-m-d') == '2016-03-17'){
           // Log::record('------1------ ' . $batch_index);
          //  Log::record('------2------ ' . var_export($t, true));
           // Log::record('------3------ ' . var_export($tmp_array, true));
      //  }

        if(!empty($tmp_array)){
            // 如果没有考勤记录，需要获取当天所有排班对应的班次
            $signBatch_ser = D("Sign/SignBatch");
            $batch_array = $signBatch_ser->list_by_condition($tmp_array);

            // 已获取到排班，验证是否是休息日
            if($this->_today_in_remove_day($remove_day, $cur_time)){
                return false;
            }
        }else{
            // 未排班，验证是否在 增加的上班日中
            if($this->_check_in_add_work_day($add_work_day, $cur_time)){
                // 在上班日中
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * 检查是否在节假日增加上班日期里
     * @param $add_work_day
     * @return bool
     */
    protected function _check_in_add_work_day($add_work_day, $cur_time)
    {
        if(empty($add_work_day)){
            return false;
        }

        $add_work_day = unserialize($add_work_day);

        foreach ($add_work_day as $_awd) {
            $_st = rstrtotime($_awd['startTime']) . ' 00:00:00';
            $_et = rstrtotime($_awd['endTime']) . ' 23:59:59';

            if ($cur_time >= $_st && $cur_time <= $_et) {
                return true;
            }
        }
        return false;
    }

    /**
     * 今天是否在节假日里
     * @param $remove_work_day
     * @param $cur_date 要验证的时间戳
     * @return bool
     */
    protected function _today_in_remove_day($remove_work_day, $cur_date){

        if(empty($remove_work_day)){
            return false;
        }

        //是否在节假日里
        $remove_day = unserialize($remove_work_day);
        //法定节假日
        $public_day = $remove_day['public'];
        //用户自定义节假日
        $user_day = $remove_day['user'];
        foreach($public_day as $pd){
            $start = strtotime($pd['startTime'].' 00:00:00');
            $end = strtotime($pd['endTime'].' 23:59:59');
            if($cur_date>=$start && $cur_date <= $end){
                return true;
            }
        }
        foreach($user_day as $ud){
            $start = strtotime($ud['startTime'].' 00:00:00');
            $end = strtotime($ud['endTime'].' 23:59:59');
            if($cur_date>=$start && $cur_date <= $end){
                return true;
            }
        }

        return false;
    }

	/**
	 * 根据签到规则统计签到数据(公司考勤、外出考勤)
	 * absent: 0工作日，1旷工，2休息日
	 * @param array $result 追加数据的数组
	 * @param string $_d 日期
	 * @param array $list 签到的原始数据
     * @param $dep_id 部门id
	 * @return array result 继续追加的数组
	 */
	private function __in_time($result, $_d, $list, $dep_id) {
        // 获取班次
        $bp_info = $this->check_batch_info($dep_id);
        // 如果当天没有考情需要校验是旷工，还是休息
        if (!isset($list[$_d])) {
            // 获取指定时间的排班信息
            $conds = array(
                'time' => $_d,
                'cd_id' => $dep_id
            );

            $schedule_log_model = D('Sign/SignScheduleLog');
            // 查询最近一条排班变更记录
            $_data = $schedule_log_model->get_schedule_history($conds);
            $cycle_unit = '';
            $updated = '';
            $cycle_num = '';
            $schedule_everyday_detail = '';
            $remove_day = '';
            $add_work_day = '';
            //Log::record('$_data ' . $_d . ' -- ' . var_export($_data, true), Log::ALERT);
            //Log::record('$bp_info ' . $_d . ' -- ' . var_export($bp_info, true), Log::ALERT);
            if(empty($_data)){
                // 没查到变更规则记录时, 使用当前部门对应的排班规则
                $cycle_unit = $bp_info['cycle_unit'];
                $updated = $bp_info['schedule_begin_time'];
                $cycle_num = $bp_info['cycle_num'];
                $schedule_everyday_detail = $bp_info['schedule_everyday_detail'];
                $remove_day = $bp_info['remove_day'];
                $add_work_day =  $bp_info['add_work_day'];
            }else{
                $cycle_unit = $_data['cycle_unit'];
                $updated = $_data['init_time'];
                $cycle_num = $_data['cycle_num'];
                $schedule_everyday_detail = $_data['schedule_everyday_detail'];
                $remove_day = $_data['remove_day'];
                $add_work_day =  $_data['add_work_day'];
            }

            if(!empty($bp_info)) {
                // 获取到排班序号
                $cur_time = rstrtotime($_d);
                $batch_index = $this->find_today_batch_index($cycle_unit, $updated, $cycle_num, $cur_time);
                $batch_index -= 1;

                $bat_name_array = array();
                $status = $this->__is_in_workday($schedule_everyday_detail, $batch_index, $remove_day, $add_work_day, $cur_time, $bat_name_array);
                if ($status === false) {
                    // 休息
                    $result[$_d]['absent'] = 2;
                } else {
                    // 旷工
                    $result[$_d]['absent'] = 1;
                }
            }
        } else {
            // 有打卡记录
            $result[$_d]['absent'] = 0;

           // Log::record("------ " . var_export($list[$_d], true));
            // 获取当天的所有排班对应的班次
            $cycle_unit = $bp_info['cycle_unit'];
            $updated = $bp_info['init_time'];
            $cycle_num = $bp_info['cycle_num'];
            $schedule_everyday_detail = $bp_info['schedule_everyday_detail'];
            $remove_day = $bp_info['remove_day'];
            $add_work_day =  $bp_info['add_work_day'];

            // 获取到排班序号
            $cur_time = rstrtotime($_d);
            $batch_index = $this->find_today_batch_index($cycle_unit, $updated, $cycle_num, $cur_time);
            $batch_index -= 1;
            // 排班明细时间段
            $schedule_everyday_detail = unserialize($schedule_everyday_detail);
            $t = $schedule_everyday_detail[$batch_index];
            $tmp_array = array_column($t, 'id');

            $record_num = count($tmp_array) * 2; // 总的考勤记录数
            if(count($list[$_d]) < $record_num && $_d != rgmdate(NOW_TIME, 'Y-m-d')){
                // 有班次未签到、签退
                // 迟到、早退、未签退都属于异常
                $result[$_d]['unusual'] = 1;
            }else{
                // 上下班卡都打情况 sr_sign: 1正常，2迟到，4早退
                foreach ($list[$_d] as $likey => $_li) {
                    if (1 == $_li['sr_sign']) {
                        continue;
                    }

                    // 状态未知
                    if (0 == $_li['sr_sign']) {
                        if ($_li['sr_type'] == 1) { // 上班
                            $result = $this->__sign_on($result, $_li, $_d);
                            // 下班
                        } else {
                            $result = $this->__sign_off($result, $_li, $_d);
                        }
                    } else {
                        // 迟到、早退、未签退都属于异常
                        $result[$_d]['unusual'] = 1;
                    }
                    // 如果签到地理位置异常，考勤也属于异常
                    if (0 < $_li['sr_addunusual']) {
                        $result[$_d]['unusual'] = 1;
                    }

                }
                //未签退
                if(count($list[$_d]) < 2){
                    if(rgmdate(NOW_TIME, 'Y-m-d') != $_d){
                        $result[$_d]['unusual'] = 1;
                    }

                }
            }
        }

		return $result;
	}

	/**
	 * 上班情况
	 *
	 * @param 之前结果集 $result
	 * @param 一天的签到记录 $_li
	 * @return 之后结果集
	 */
	private function __sign_on($result, $_li, $_d) {

		// 迟到
		if ($_li['sr_sign'] == 2) {
			$result[$_d]['unusual'] = 1;
			// 正常
		}

		return $result;
	}

	/**
	 * 下班情况
	 *
	 * @param 之前结果集 $result
	 * @param 一天的签到记录 $_li
	 * @return 之后结果集
	 */
	private function __sign_off($result, $_li, $_d) {

		// 早退
		if ($_li['sr_sign'] == 4) {
			$result[$_d]['unusual'] = 1;
			// 正常
		}
		return $result;
	}

	/**
	 * 根据开始和结束时间返回日期数组
	 *
	 * @param string $stime 开始时间
	 * @param string $etime 结束时间
	 * @return array $datelist 日期数组
	 */
	public function get_datelist($stime, $etime) {

		// 构造循环日期数组
		$begin = rstrtotime($stime);
		$end = rstrtotime($etime);
		$datelist = array();
		// 循环当月的所有天数
		while ($begin <= $end) {
			$begin = rgmdate($begin, 'Y-m-d');
			$datelist[$begin] = $begin;
			$begin = rstrtotime($begin);
            // 累加天数, 按月拆分所有天放入数据中
            $begin += 86400;
		}

		return $datelist;
	}

	/**
	 * 返回所有异常的次数
	 *
	 * @param array $data
	 * @return multitype:number
	 */
	public function get_absent($data, $cache_sign_setting) {
		$absent = 0;
		$normal = 0;
		$unusual = 0;

		// 统计操作
		foreach ($data as $_da) {
            if(!is_array($_da)){
                continue;
            }
			// 旷工
			if ($_da['absent'] == 1) {
				$absent ++;
			}
			// 迟到/早退/未签退
			if($_da['absent'] == 0 && isset($_da['unusual'])){
				$unusual ++;
			}
            // 外出考勤开关统计已开
            if(!empty($cache_sign_setting['out_sign_include']) && $cache_sign_setting['out_sign_include'] == 2){
                // 有开启统计外勤开关，同时也有外勤数据
                //Log::write("aaaa  " . var_export($_da, true));
                if ($_da['absent'] === 0 && $_da['flag'] === 9) {
                    // 出勤
                    $normal ++;
                }
            }else{
                if ($_da['absent'] === 0 && !isset($_da['unusual'])) {
                    // 出勤
                    $normal ++;
                }
            }
		}
		// 返回数据
		$re_data = array(0 => $absent, 1 => $unusual, 2 => $normal);
		return $re_data;
	}

	/**
	 * 没有签到记录情况(获取当前部门的排班规则)
	 * @param array $datelist 日期数组
     * @param $dep_id 部门id
	 * @return $result 一个月都是旷工数组
	 */
	private function __no_data($datelist, $dep_id) {
        $schedule_log_model = D('Sign/SignScheduleLog');
		foreach ($datelist as $_d) {
            $flag = false; // 默认有排班
            $conds = array(
                'time' => $_d,
                'cd_id' => $dep_id
            );

            // 查询最近一条排班变更记录
            $cycle_unit = '';
            $updated = '';
            $cycle_num = '';
            $schedule_everyday_detail = '';
            $remove_day = ''; // 排除节假日
            $add_work_day = ''; // 增加上班日
            $_data = $schedule_log_model->get_schedule_history($conds);
            if(empty($_data)){
                // 没查到变更规则记录时, 使用当前部门对应的排班规则
                $_data_schedule = $this->check_batch_info($dep_id);
                if(empty($_data_schedule)){
                    $flag = true; // 没有排班
                }else{
                    $cycle_unit = $_data_schedule[0]['cycle_unit'];
                    $updated = $_data_schedule[0]['schedule_begin_time'];
                    $cycle_num = $_data_schedule[0]['cycle_num'];
                    $schedule_everyday_detail = $_data_schedule[0]['schedule_everyday_detail'];
                    $remove_day = $_data_schedule[0]['remove_day'];
                    $add_work_day =  $_data_schedule[0]['add_work_day'];
                }
            }else{
                $cycle_unit = $_data['cycle_unit'];
                $updated = $_data['init_time'];
                $cycle_num = $_data['cycle_num'];
                $schedule_everyday_detail = $_data['schedule_everyday_detail'];
                $remove_day = $_data['remove_day'];
                $add_work_day =  $_data['add_work_day'];
            }
            if(!$flag){
                // 获取到排班序号
                $cur_time = rstrtotime($_d);
                $batch_index = $this->find_today_batch_index($cycle_unit, $updated, $cycle_num, $cur_time);
                $batch_index -= 1;

                $bat_name_array = array();
                $status = $this->__is_in_workday($schedule_everyday_detail, $batch_index, $remove_day, $add_work_day, $cur_time, $bat_name_array);
                if($status === false){
                    // 休息
                    $result[$_d]['absent'] = 2;
                }else{
                    // 旷工
                    $result[$_d]['absent'] = 1;
                }
            }

		}

		return $result;
	}


    /**
     * 没有公司考勤数据时，同时有外出考勤数据处理情况
     * @param $outData 外出考勤数据
     * @param array $datelist 日期数组
     * @return $result
     */
    private function __no_out_data($outData, $datelist) {

        // 有外出考情时，就需要记录为正常考情，将签到数据，把日期作为key存储起来
        $list = array();
        // 整合已有的数据
        foreach ($outData as $_data) {
            $datime = rgmdate($_data['sl_signtime'], 'Y-m-d');
            $list[$datime][] = $_data;
        }

        // 因外出考勤没有班次信息，固采用系统默认处理，按1-5为工作日，6、7为休息日
        foreach ($datelist as $_d) {
            $week = rgmdate(rstrtotime($_d), 'w');
            // 周末
            $wee = array(6, 0);
            // 小于当天日期
            if (rstrtotime($_d) < NOW_TIME) {
                if (isset($list[$_d])) {
                    // 外出 工作日
                    $result[$_d]['absent'] = 0;
                    // 有外勤数据标识, 为了解决最终统计数据时, 开启了统计外勤出勤数时，如果没查询到外勤数据时，也会累加外勤数的问题
                    $result[$_d]['flag'] = 9;
                }else{
                    if (!in_array($week, $wee)) {
                        $result[$_d]['absent'] = 1;
                    } else {
                        $result[$_d]['absent'] = 2;
                    }
                }
            } else {
                $result[$_d]['absent'] = 2;
            }

        }

        return $result;
    }

	/**
	 * 格式数据
	 *
	 * @param array $data 记录
	 * @return $data $work_time 工作时间
	 */
	public function format_worktime($da, &$data, &$work_time) {

		// 格式时间
		foreach ($da as &$val) {
			$val['_signtime'] = rgmdate($val['sr_signtime'], 'H:i');
		}
		// 记录数大于2条才有工作时间
		if (count($da) >= 2) {
			foreach ($da as $_daa) {
				if ($_daa['sr_type'] == 1) {
					$mor_time = $_daa['sr_signtime'];
				}
				if ($_daa['sr_type'] == 2) {
					$aff_time = $_daa['sr_signtime'];
				}
			}
			$work_time = $aff_time - $mor_time;
			// 只有当$work_time大于0才符合条件
			if ($work_time > 0) {
				$work_time = $this->tohm($work_time);
			} else {
				$work_time = ' ';
			}
		}

		$data = $da;
	}

	/**
	 * 将秒数转化为小时：分钟
	 *
	 * @param int $sec 秒数
	 * @return string 分钟
	 */
	public function tohm($sec) {
		// 计算出时分秒
		$hours = floor($sec / 3600);
		$remainSeconds = $sec % 3600;
		$minutes = floor($remainSeconds / 60);
		$seconds = intval($sec - $hours * 3600 - $minutes * 60);

		return $hours . '小时' . $minutes . '分钟';
	}

	/**
	 * 根据时间段 获取 关于某人的签到记录
	 *
	 * @param $btime 签到时间
	 * @param $batch_id
	 * @param $m_uid
     * @param $_ssid 排班id
	 * @return array
	 */
	public function get_by_time($btime, $batch_id, $m_uid, $sr_type) {

		$records = $this->_d->get_by_time($btime, $batch_id, $m_uid, $sr_type);
		// 如果没有记录
		if (! $records) {
			$records = array();
		}

		return $records;
	}

	/**
	 * 格式化数据
	 *
	 * @param array $list
	 * @return boolean
	 */
	public function sign_record_list(&$list) {

		foreach ($list as &$sign) {
			$this->__sign_record($sign);
		}

		return true;
	}

	/**
	 * 格式化数据
	 *
	 * @param $sign
	 * @return bool
	 */
	private function __sign_record(&$sign) {

		$sign['_updated'] = rgmdate($sign['sr_updated'], 'Y-m-d H:i');
		$sign['_signtime'] = rgmdate($sign['sr_signtime'], 'Y-m-d H:i');
		$sign['_signtime_hi'] = rgmdate($sign['sr_signtime'], 'H:i:s');

		return true;
	}

	/**
	 * 计算签退状态
	 *
	 * @param array $data_list 计算数据
	 * @return $data_list 计算后数组
	 */
	public function off_type($data_list) {

        // 只有一条记录
        if (count($data_list) < 2) {
            $data_list[] = array('off_type' => 'no_off', 'sr_type' => 2);
            // 有两条记录
        } elseif (count($data_list) >= 2) {
            // 遍历设置为签退
            foreach ($data_list as &$_d) {
                // 下班记录
                if ($_d['sr_type'] == 2) {
                    $_d['off_type'] = 'have_off';
                }
            }
        }

		return $data_list;
	}

	/**
	 * 插入签到记录
	 *
	 * @param $data
	 * @return mixed
	 */
	public function insert_record($data) {

		return $this->_d->insert($data);
	}



	/**
	 * [list_by_conds 根据条件获取信息上班]
	 * @param  [type] $conds [description]
	 * @return [type]        [description]
	 */
	public function list_by_qdtime_on() {

		return $this->_d->list_by_qdtime_on();
	}

	/**
	 * [list_by_conds 根据条件获取信息下班]
	 * @param  [type] $conds [description]
	 * @return [type]        [description]
	 */
	public function list_by_qdtime_off() {

		return $this->_d->list_by_qdtime_off();
	}

	/**
	 * [get_over_qd_muid  已班次为基准 获取所有的该班次下的已经进行签到过的用户m_uid数据]
	 * @param  [type] $true_sbids [传递的班次数组]
	 * @return [type]             [返回的数据]
	 */
	public function get_over_qd_muid($true_sbids) {

		// 首先取出这些班次的信息
		$batch_info = $this->get_batch_info($true_sbids);

		$nedd_array = array();
		foreach($batch_info as $k=>$v) {
			$nedd_array[$v['sbid']]['work_begin'] = $v['work_begin'];
			$nedd_array[$v['sbid']]['work_end'] = $v['work_end'];
		}
		// 以班次为基准查询 该班次下的已经签到的数据
		$sbid_qd_muid = $this->get_sbid_qd_muid($nedd_array);

		return $sbid_qd_muid;
	}

	/**
	 * [get_over_qd_muid_off  已班次为基准 获取所有的该班次下的已经进行签到过的用户m_uid数据]
	 * @param  [type] $true_sbids [传递的班次数组]
	 * @return [type]             [返回的数据]
	 */
	public function get_over_qd_muid_off($true_sbids) {

		// 首先取出这些班次的信息
		$batch_info = $this->get_batch_info($true_sbids);

		$nedd_array = array();
		foreach($batch_info as $k=>$v) {
			$nedd_array[$v['sbid']]['work_begin'] = $v['work_begin'];
			$nedd_array[$v['sbid']]['work_end'] = $v['work_end'];
		}
		// 以班次为基准查询 该班次下的已经签到的数据

		$sbid_qd_muid = $this->get_sbid_qd_muid_off($nedd_array);

		return $sbid_qd_muid;
	}



	/**
	 * [get_batch_info 根据班次id获取班次信息]
	 * @param  [type] $true_sbids [传递的班次di数组]
	 * @return [type]             [返回的数据]
	 */
	protected function get_batch_info($true_sbids) {

		$d = D('Sign/SignBatch');
		return $d->get_batch_info($true_sbids);
	}

	/**
	 * [get_sbid_qd_muid 获取各个班次的已经签到的muid数据]
	 * @param  [type] $nedd_array [传递的数据]
	 * @return [type]             [返回的数据]
	 */
	protected function get_sbid_qd_muid($nedd_array) {

		$d = D('Sign/SignRecord');
		$sb_qd_array = array();
		// 循环查询每个班次的数据
		foreach ($nedd_array as $k => $v) {
			$data = $d->get_sbid_qd_muid($k, $v);
			$sb_qd_array[$k] = array_column($data, 'm_uid');
		}

		return $sb_qd_array;
	}


	/**
	 * [get_sbid_qd_muid_off 获取各个班次的已经签到的muid数据]
	 * @param  [type] $nedd_array [传递的数据]
	 * @return [type]             [返回的数据]
	 */
	protected function get_sbid_qd_muid_off($nedd_array) {

		$d = D('Sign/SignRecord');
		$sb_qd_array = array();
		// 循环查询每个班次的数据
		foreach ($nedd_array as $k => $v) {
			$data = $d->get_sbid_qd_muid_off($k, $v);
			$sb_qd_array[$k] = array_column($data, 'm_uid');
		}
		return $sb_qd_array;
	}

    /**
     * 查询最近一点公司考勤的打卡记录
     * @param $params
     * @return array
     */
    public function get_by_condition_new($params) {
        return $this->_d->get_by_condition_new($params);
    }

    /**
     *
     * @param $conds
     * @param null $page_option
     * @param array $order_option
     */
    public function list_by_conds_for_record($conds, $page_option = null, $order_option = array()){
        return $this->_d->list_by_conds_for_record($conds, $page_option, $order_option);
    }


    /**
     * 根据条件读取数据数组
     * @param array $conds 条件数组
     * @param array $order_option 排序
     * @throws service_exception
     */
    public function list_by_conds_for_export($conds, $page_option = null, $order_option = array()) {
        return $this->_d->list_by_conds_for_export($conds, $page_option, $order_option);
    }

    /**
     * 验证是否是工作日
     * @param $res
     * @param $_d 日期
     * @param $cd_id 部门id
     * @param $schedule_info 排班信息
     * @param $username 用户名
     */
    public function check_work_day($_res, $conds, $_data_schedule, $username){
        // 查询最近一条排班变更记录
        $cycle_unit = '';
        $updated = '';
        $cycle_num = '';
        $schedule_everyday_detail = '';
        $remove_day = ''; // 排除节假日
        $add_work_day = ''; // 增加上班日
        $schedule_log_model = D('Sign/SignScheduleLog');
        $_data = $schedule_log_model->get_schedule_history($conds);
        if(empty($_data)){
            // 没查到变更规则记录时, 使用当前部门对应的排班规则
            $cycle_unit = $_data_schedule[0]['cycle_unit'];
            $updated = $_data_schedule[0]['schedule_begin_time'];
            $cycle_num = $_data_schedule[0]['cycle_num'];
            $schedule_everyday_detail = $_data_schedule[0]['schedule_everyday_detail'];
            $remove_day = $_data_schedule[0]['remove_day'];
            $add_work_day =  $_data_schedule[0]['add_work_day'];
        }else{
            $cycle_unit = $_data['cycle_unit'];
            $updated = $_data['init_time'];
            $cycle_num = $_data['cycle_num'];
            $schedule_everyday_detail = $_data['schedule_everyday_detail'];
            $remove_day = $_data['remove_day'];
            $add_work_day =  $_data['add_work_day'];
        }
        // 验证考勤状态
        $cur_time = rstrtotime($conds['time']);
        $batch_index = $this->find_today_batch_index($cycle_unit, $updated, $cycle_num, $cur_time);
        $batch_index -= 1;
        $batch_array = array(); //  班次名称数组
        $status = $this->__is_in_workday($schedule_everyday_detail, $batch_index, $remove_day, $add_work_day, $cur_time, $batch_array);
        if($status === false){
            // 休息
            $_res['status'] = 2;
        }else{
            // 旷工
            $_res['status'] = 1;
        }

        if(!empty($batch_array)){
            $b_name = array();
            foreach($batch_array as $tmp_b){
                $b_name[] = $tmp_b['name'];
            }
            $_res['rep_batch_name'] = implode("|", $b_name);

        }
        $_res['username'] = $username;
        return $_res;
    }


    /**
     * 检查时间范围是否是次日,返回次日时间(H:i)
     */
    public function check_time_range($work_begin, $work_end){
        // 验证结束时间是否是次日时间, 如果是次日时间，需要重新组合结束时间次日年月日 + 下班的小时、分钟
        $t_s = rgmdate($work_begin, 'Y-m-d');
        $t_e = rgmdate($work_end, 'Y-m-d');
        $t_e_h = rgmdate($work_end, 'H:i');
        $etime = '';
        // 次日
        if(differ_days($t_s, $t_e) > 0){
            $etime = '次日'.$t_e_h;
        }else{
            $etime = $t_e_h;
        }

        return $etime;
    }

	public function get_sign_record_groupby_sbid($params){
		return $this->_d->get_sign_record_groupby_sbid($params);
	}

	public function get_sign_record_by_sbid($params){
		return $this->_d->get_sign_record_by_sbid($params);
	}


    /**
     * 获取当前用户的所有顶级部门排班
     * @param $departments
     * @return bool
     */
    public function get_up_deps_schedule($departments){
        $schedule_data = array();
        foreach($departments as $deps){
            //当前部门的所有上级部门
            $parent_dept_ids = $this->list_parent_departments_by_cdid($deps['cd_id']);
            //查询所有上级部门的排班
            $schedule_list = $this->list_schedules_by_cdids($parent_dept_ids);
            if(!empty($schedule_list)){
                $schedule_data[] = $schedule_list[0];
                continue;
            }
        }
      //  Log::record('$schedule_data'.var_export($schedule_data,true));
       // Log::record('$dept_array'.var_export($dept_array,true));

        return $schedule_data;
    }


    /**
     * 获取部门对应的排班
     * @param $cdid_array
     * @return mixed
     */
    public function list_schedules_by_cdids($cdid_array){
        $conds = array(
            'cdid_array' => $cdid_array,
            'enabled' => 1
        );
        return $this->_d_schedule->list_schedule_by_params($conds);
    }

    /**
     * 封装部门id、name
     * @param $cdid_array
     * @param $dept_array
     */
    public function formate_department_name($cdid_array, &$dept_array){

        // 获取部门缓存数据
        $cache = &Cache::instance();
        $cache_departments = $cache->get('Common.department');

        foreach ($cache_departments as $_dp) {
            if (in_array($_dp['cd_id'], $cdid_array)) {
                $dept_array[$_dp['cd_id']] = $_dp['cd_name'];
            }
        }
    }

    /**
     * 获取用户当前部门的所有上级部门
     * @param $cd_id
     * @return array
     */
    public function list_parent_departments_by_cdid($cd_id){
        $result = array();
        //获取用户当前部门的所有上级部门
        $dept_service = Department::instance();
        $dept_service->list_parent_cdids($cd_id, $result);
        return $result;
    }

    /**
     * 是否启用了全公司排班
     * @return bool true-是 false-否
     */
    private function __if_all_commpany_on(&$all_company_data){

        // 全公司标识
        $conds = array(
            'cd_id' => 0
        );
        $all_company_data = $this->_d_schedule->get_by_conds($conds);

        if(empty($all_company_data)){
            E('_ERR_SECHEDULE_DATA_ERROR');
            return false;
        }

        //如果全公司已启用
        if ($all_company_data['enabled'] != 1) {
            return true;
        }

        return false;
    }

}
