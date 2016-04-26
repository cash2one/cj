<?php
/**
 * cpindex.php
 * 销售的个人主页
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_cpindex extends voa_c_frontend_travel_base {

	/**
	 * _before_action
	 *
	 * @param mixed $action
	 * @access protected
	 * @return void
	 */
	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	public function execute() {

		$uda_to = new voa_uda_frontend_travel_turnover_get();
		// 取今天的销售提成/业绩
		$params = array(
			'saleuid' => array(startup_env::get('wbs_uid')),
			'start_date' => rgmdate(startup_env::get('timestamp'), 'Y-m-d')
		);
		$to_day = array();
		if (!$uda_to->execute($params, $to_day)) {
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

		// 获取销售订单总数
		$uda_count = new voa_uda_frontend_travel_ordergoods_countsaleorder();
		$params = array(
			'saleuid' => array(startup_env::get('wbs_uid')),
			'start_date' => rgmdate(startup_env::get('timestamp'), 'Y-m-d')
		);
		$countorder = 0;
		if (!$uda_count->execute($params, $countorder)) {
			$this->_error_message('订单总数读取失败');
			return true;
		}

		// 应用默认标题栏名称
		// 应用模板顶部也可以自定义 {$navtitle = '应用名称'}会覆盖掉此默认的名称
		//$this->view->set('navtitle', $this->_plugin['cp_name']);

		$this->view->set('to_total', $to_total);
		$this->view->set('countorder', $countorder);
		$this->view->set('to_day', $to_day);
		$this->view->set('countorder', $countorder);

		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/cpindex');
	}

}
