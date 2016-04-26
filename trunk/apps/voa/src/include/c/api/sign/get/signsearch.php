<?php

/**
 * voa_c_api_sign_get_signsearch
 * 外勤查询
 * $Id$
 */
class voa_c_api_sign_get_signsearch extends voa_c_api_base {

	public function execute() {
		// 判断数据
		if (! $this->__execute()) {
			return false;
		}
		;
		
		// list获取
		$serv = &service::factory('voa_s_oa_sign_record');
		$serv_detail = &service::factory('voa_s_oa_sign_detail');
		$uid = startup_env::get('wbs_uid');
		if (! empty($this->_params['m_uid'])) {
			$uid = $this->_params['m_uid'];
		}
		// 当前时间
		$udate = rgmdate(startup_env::get('timestamp'), 'Y-m-d');
		
		if (! empty($this->_params['udate'])) {
			$udate = $this->_params['udate'];
		}
		$conds['m_uid'] = $uid;
		$conds['sr_signtime >= (?)'] = strtotime($udate);
		$conds['sr_signtime <= (?)'] = strtotime($udate) + 86400;
		
		$data = $serv->list_by_conds($conds);
		
		$data_list = array();
		$work_time = '';
		if (! empty($data)) {
			$this->format($data, $data_list, $work_time);
			$data = $data_list;
		}
		
		// 获取备注
		$this->__get_detail_list($data, $serv_detail, $detail_list);
		// 控制显示字段
		$this->__able_fields($data, $able_data);
			// 输出结果
		$this->_result = array('list' => $able_data, 'detail_list' => $detail_list, 'work_time' => $work_time);		
		return true;
	}

	/**
	 * 获取备注
	 * 
	 * @param $data
	 * @param $serv_detail
	 * @param $detail_list
	 * @return bool
	 */
	private function __get_detail_list($data, $serv_detail, &$able_detail) {

		$detail_list = array();
		$srid = array();
		
		if (! empty($data) > 0) {
			
			foreach ($data as $_val) {
				$srid[] = $_val['sr_id'];
			}
			$conds_detail['sr_id IN (?)'] = $srid;
			
			$detail_list = $serv_detail->list_by_conds($conds_detail);
			if (! empty($detail_list)) {
				$detail_list = $detail_list;
				// 格式显示数据
				$able_detail = array();
				foreach ($detail_list as $_detail) {
					$det = array();
					$det['sd_id'] = $_detail['sd_id'];
					$det['sr_id'] = $_detail['sr_id'];
					$det['sd_reason'] = $_detail['sd_reason'];
					$det['sd_id'] = $_detail['sd_id'];
					$able_detail[$_detail['sd_id']] = $det;
				}
			}
		}
		
		return true;
	}

	/**
	 * 判断数据
	 * 
	 * @return bool
	 */
	private function __execute() {
		// 需要的参数
		$fields = array(
			// 查询日期
			'udate' => array('type' => 'string', 'required' => false), 
			// 上报人员ID
			'm_uid' => array('type' => 'int', 'required' => false))

		;
		if (! $this->_check_params($fields)) {
			// 检查参数
			return false;
		}
		
		return true;
	}

	/**
	 * 格式数据
	 * 
	 * @param unknown $data
	 * @return unknown
	 */
	public function format($da, &$data, &$work_time) {

		foreach ($da as &$val) {
			$val['_signtime'] = rgmdate($val['sr_signtime'], 'H:i');
		}
		
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
			if ($work_time > 0) {
				$work_time = $this->tohm($work_time);
			} else {
				$work_time = ' ';
			}
		}
		$data = $da;
	}

	public function tohm($sec) {

		$hours = floor($sec / 3600);
		$remainSeconds = $sec % 3600;
		$minutes = floor($remainSeconds / 60);
		$seconds = intval($sec - $hours * 3600 - $minutes * 60);
		
		return $hours . '小时' . $minutes . '分钟';
	}

	/**
	 * 限制显示字段
	 * 
	 * @param unknown $data
	 * @param unknown $able_data
	 */
	private function __able_fields($data, &$able_data) {

		$able_data = array();
		// 可显示的字段
		foreach ($data as $_data) {
			$_able_data = array();
			$_able_data['sr_id'] = $_data['sr_id'];
			$_able_data['m_username'] = $_data['m_username'];
			$_able_data['sr_type'] = $_data['sr_type'];
			$_able_data['sr_address'] = $_data['sr_address'];
			$_able_data['sr_sign'] = $_data['sr_sign'];
			$_able_data['_signtime'] = $_data['_signtime'];
			$able_data[$_data['sr_id']] = $_able_data;
		}
	}

}
