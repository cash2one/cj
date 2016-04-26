<?php
/**
 * voa_c_api_travel_get_ordergoods
 * 获取订单中的商品信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_ordergoods extends voa_c_api_travel_goodsabstract {

	public function execute() {

		// 根据条件读取商品销售记录
		$uda_og = &uda::factory('voa_uda_frontend_travel_ordergoods_list');
		$list = array();
		$params = $this->request->getx();

		if ($params['page'] == 1) {
			// 读取业绩
			$this->_get_turnover();
		}

		$params['saleuid'] = $this->_member['m_uid'];
		$type = (string)$this->request->get('ty');

		if ($type == 'today' || empty($type)) {
			$params['start_date'] = rgmdate(startup_env::get('timestamp'), 'Y-m-d');
		}elseif ($type == 'yesterday'){
			$params['start_date'] = rgmdate(startup_env::get('timestamp') - 86400, 'Y-m-d');
			$params['end_date'] =   rgmdate(startup_env::get('timestamp'), 'Y-m-d');
		}elseif ($type == 'week'){
			$params['start_date'] = rgmdate(startup_env::get('timestamp') - 86400*7, 'Y-m-d');
		}elseif ($type == 'month'){
			$params['start_date'] = rgmdate(startup_env::get('timestamp') - 86400*30, 'Y-m-d');
		}


		if (!$uda_og->execute($params, $list)) {
			$this->_error_message($uda->errmsg);
			return true;
		}

		$this->_result['list'] = $list;
	}

	// 统计当天业绩
	protected function _get_turnover() {

		$page = (int)$this->request->get('page');
		if (!empty($page) && 1 < $page) {
			return true;
		}
		$type = (string)$this->request->get('ty');
		if ($type == 'today' || empty($type)) {
			$start_date = rgmdate(startup_env::get('timestamp'), 'Y-m-d');
		}elseif ($type == 'yesterday'){
			$start_date = rgmdate(startup_env::get('timestamp') - 86400, 'Y-m-d');
			$end_date =   rgmdate(startup_env::get('timestamp'), 'Y-m-d');
		}elseif ($type == 'week'){
			$start_date = rgmdate(startup_env::get('timestamp') - 86400*7, 'Y-m-d');
		}elseif ($type == 'month'){
			$start_date = rgmdate(startup_env::get('timestamp') - 86400*30, 'Y-m-d');
		}

		$uda_to = new voa_uda_frontend_travel_turnover_get();
		// 取销售提成/业绩
		$params = array(
			'saleuid' => array($this->_member['m_uid']),
			'start_date' => $start_date
		);
		if (!empty($end_date)) {
			$params['end_date'] = $end_date;
		}

		$to_total = array();
		if (!$uda_to->execute($params, $to_total)) {
			$this->_error_message('销售信息读取失败');
			return true;
		}

		if (!empty($to_total)) {
			$this->_result['tj'] = $to_total;
		}

		return true;
	}

}
