<?php

/**
 * voa_c_api_sign_get_cal
 * 外勤查询
 * $Id$
 */
class voa_c_api_sign_get_cal extends voa_c_api_sign_base {
	/*状态 absent	0工作日
	 * 				1正常，	2迟到，	4，早退
	 * 			1旷工
	 * 			2休息日
	*/
	private $weeks = array('日', '一', '二', '三', '四', '五', '六');

	public function execute() {
		// 需要的参数
		$fields = array(
			// 查询日期开始
			'udate' => array('type' => 'string', 'required' => false), 'm_uid' => array('type' => 'int', 'required' => false));
		if (! $this->_check_params($fields)) {
			// 检查参数
			return false;
		}
		
		if ($this->_params['udate'] == ' ') {
			$this->_params['udate'] = date('Y-m-d', time());
		}
		$this->_params['udate'] = date('Y-m-d', time());
		// 获取时间月份
		$year = substr($this->_params['udate'], 0, 4);
		$month = substr($this->_params['udate'], 5, 2);
		$firstDay = mktime(0, 0, 0, $month, 1, $year);
		$days = date('t', $firstDay);
		$this->_params['udate_s'] = date('Y-m-d', $firstDay);
		$this->_params['udate_e'] = date('Y-m-d', strtotime('+ ' . $days . 'day', $firstDay));
		
		// list获取
		$serv = &service::factory('voa_s_oa_sign_record');
		$reslut = array();
		if (empty($this->_params['m_uid'])) {
			$this->_params['m_uid'] = startup_env::get('wbs_uid');
		}
		
		// 视图需要的数据
		list($absent, $late, $early, $normal, $data) = $this->make_view($serv);
		// 输出结果
		$this->_result = array('list' => $data, 'late' => $late, 'early' => $early, 'absent' => $absent, 'normal' => $normal);	
		return true;
	}

	/**
	 * 格式数据
	 * @param unknown $data
	 * @return unknown
	 */
	public function format($data) {

		foreach ($data as &$val) {
			$val['_signtime'] = rgmdate($val['sr_signtime'], 'H:i');
		}
		
		return $data;
	}

	/**
	 *
	 * @param unknown $m_uid 格式显示数据
	 * @param unknown $data
	 * @param unknown $stime
	 * @param unknown $etime
	 */
	public function dataformat($m_uid, $data, $stime, $etime) {

		$serv_mem = &service::factory('voa_s_oa_member_department');
		$serv_department = &service::factory('voa_s_oa_sign_department');
		$serv_batch = &service::factory('voa_s_oa_sign_batch');
		$conds_mem['m_uid'] = $m_uid;
		$dep_list = $serv_mem->fetch_all_by_conditions($conds_mem);
		$absent = '';
		if (! empty($data)) { // 对应多个部门
			$batch = 0;
			foreach ($data as $_val) {
				$batch = $_val['sr_batch'];
			}
			
			$info = $serv_batch->get($batch);
		} else { // 单个部门
			$absent = 'all';
		}
		
		// 构造循环日期数组
		$begin = strtotime($stime);
		$end = strtotime($etime);
		$datelist = array();
		while ($begin < $end) {
			$begin = date('Y-m-d', $begin);
			$datelist[$begin] = $begin;
			$begin = strtotime($begin);
			$begin = strtotime("+1 day", $begin);
		}
		$result = array();
		
		if ($absent == 'all') { // 没有记录记为全部旷工
			foreach ($datelist as $_d) {
				$week = rgmdate(strtotime($_d), 'w');
				$wee = array(6, 0); // 周末
				if (strtotime($_d) < startup_env::get('timestamp')) {
					if (! in_array($week, $wee)) {
						$result[$_d]['absent'] = 1;
					} else {
						$result[$_d]['absent'] = 2;
					}
				} else {
					$result[$_d]['absent'] = 2;
				}
			}
		} else { // 有记录有班次
			$work_days = unserialize($info['work_days']);
			foreach ($data as &$_da) {
				$_da['_sr_signtime'] = rgmdate($_da['sr_signtime'], 'Y-m-d');
			}
			$list = array();
			// 整合已有的数据
			foreach ($data as $_data) {
				$datime = $_data['_sr_signtime'];
				$list[$datime][] = $_data;
			}
			// 输出数据
			foreach ($list as $datek => $_date) {
				foreach ($datelist as $_d) {
					$week = rgmdate(strtotime($_d), 'w');
					if (strtotime($_d) < startup_env::get('timestamp')) {
						if (in_array($week, $work_days)) { // 在工作日内
						                                   // 大于今天日期
							if (! isset($list[$_d])) {
								$result[$_d]['absent'] = 1; // 旷工
							} else {
								$result[$_d]['absent'] = 0;
								// 判断打卡设置
								if ($info['sb_set'] == 1) {
									// $result [$_d] ['late'] = $list [$_d] [0] ['sr_sign'] == 2 ? '2' : '1';
									foreach ($list[$_d] as $likey => $_li) {
										$able = 1;
										if ($_li['sr_type'] == 1) { // 上班
											if ($_li['sr_sign'] == 2) {
												$result[$_d]['late'] = 2;
											} else {
												$result[$_d]['late'] = 1;
											}
										}
									}
									// 旷工情况
									if (! isset($able)) {
										$result[$_d]['absent'] = 1;
									}
								}
								// 只打下班卡
								if ($info['sb_set'] == 2) {
									foreach ($list[$_d] as $likey => $_li) {
										
										if ($_li['sr_type'] == 2) { // 下班
											if ($_li['sr_sign'] == 4) {
												$result[$_d]['early'] = 4; // 早退
											} else {
												$result[$_d]['early'] = 1;
											}
										} else {
											$result[$_d]['early'] = '4'; // 更改班次后
										}
									}
								}
								// 上下班卡都打情况
								if ($info['sb_set'] == 3) {
									foreach ($list[$_d] as $likey => $_li) {
										if ($_li['sr_type'] == 1) { // 上班
											if ($_li['sr_sign'] == 2) {
												$result[$_d]['late'] = 2;
											} else {
												$result[$_d]['late'] = 1;
											}
										} else { // 下班
											if ($_li['sr_sign'] == 4) {
												$result[$_d]['early'] = 4;
											} else {
												$result[$_d]['early'] = 1;
											}
										}
									}
									if (! isset($result[$_d]['early'])) {
										$result[$_d]['early'] = '未签退';
									}
								}
							}
						} else {
							$result[$_d]['absent'] = 2;
						}
					} else {
						$result[$_d]['absent'] = 2;
					}
				}
			}
		}
		
		return $result;
	}

	/**
	 * 参数检查
	 *
	 * @return boolean
	 */
	public function check_para() {
		// 需要的参数
		$fields = array(
			// 查询日期开始
			'udate' => array('type' => 'string', 'required' => false), 'm_uid' => array('type' => 'int', 'required' => false));
		if (! $this->_check_params($fields)) {
			// 检查参数
			return false;
		}
	}
	
	// 生产视图数据
	public function make_view($serv) {

		$stime = strtotime($this->_params['udate_s']);
		$etime = strtotime($this->_params['udate_e']) + 86400;
		// 查询条件
		$conds['sr_signtime > ?'] = $stime;
		$conds['sr_signtime < ?'] = $etime;
		$conds['m_uid'] = $this->_params['m_uid'];
		// 查询操作
		$data = array();
		$data = $serv->list_by_conds($conds);
		// 处理数据
		$data = $this->dataformat($this->_params['m_uid'], $data, $this->_params['udate_s'], $this->_params['udate_e']);
		$absent = 0;
		$normal = 0;
		$late = 0;
		$early = 0;
		// 统计操作
		foreach ($data as $_da) {
			if ($_da['absent'] == 1) {
				$absent ++;
			}
			if ($_da['absent'] == 0 && isset($_da['late']) && $_da['late'] == 2) {
				$late ++;
			}
			if ($_da['absent'] == 0 && isset($_da['early']) && $_da['early'] == 4) {
				$early ++;
			}
			if ($_da['absent'] == 0) {
				$normal ++;
			}
		}
		// 返回数据
		$re_data = array(0 => $absent, 1 => $late, 2 => $early, 3 => $normal, 4 => $data);
		
		return $re_data;
	}

}
