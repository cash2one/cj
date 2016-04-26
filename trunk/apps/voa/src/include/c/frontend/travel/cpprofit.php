<?php
/**
 * cpprofit.php
 * 销售的提成页面
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_cpprofit extends voa_c_frontend_travel_base {

	public function execute() {

		$uda_to = new voa_uda_frontend_travel_turnover_get();
		// 取昨天的销售提成/业绩
		$params = array(
			'saleuid' => array(startup_env::get('wbs_uid')),
			'start_date' => rgmdate(startup_env::get('timestamp') - 86400, 'Y-m-d'),
			'end_date'	=>  rgmdate(startup_env::get('timestamp'), 'Y-m-d')
		);
		$to_yesterday = array();
		if (!$uda_to->execute($params, $to_yesterday)) {
			$this->_error_message('销售信息读取失败');
			return true;
		}

		// 本月销售提成
		$params = array(
			'saleuid' => array(startup_env::get('wbs_uid')),
			'start_date' => rgmdate(startup_env::get('timestamp'), 'Y-m-01')
		);
		$to_month = array();
		if (!$uda_to->execute($params, $to_month)) {
			$this->_error_message('销售信息读取失败');
			return true;
		}

		// 上月销售提成
		list($year, $month) = explode(",", rgmdate(startup_env::get('timestamp'), 'Y,m'));
		if (1 < $month) {
			$start_date = rgmdate(startup_env::get('timestamp'), "Y-".($month - 1)."-01");
		} else {
			$start_date = ($year - 1).'-'.($month - 1).'-01';
		}

		$params = array(
			'saleuid' => array(startup_env::get('wbs_uid')),
			'start_date' => $start_date,
			'end_date' => rgmdate(startup_env::get('timestamp'), 'Y-m-01')
		);
		$to_lastmonth = array();
		if (!$uda_to->execute($params, $to_lastmonth)) {
			$this->_error_message('销售信息读取失败');
			return true;
		}

		// 取所有的
		$params = array('saleuid' => array(startup_env::get('wbs_uid')));
		$to_total = array();
		if (!$uda_to->execute($params, $to_total)) {
			$this->_error_message('销售信息读取失败');
			return true;
		}

// 		$this->view->set('countorder', $countorder);
		$this->view->set('to_yesterday', $to_yesterday);
		$this->view->set('to_month', $to_month);
		$this->view->set('to_lastmonth', $to_lastmonth);
		$this->view->set('to_total', $to_total);

		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/cpprofit');
	}

}
