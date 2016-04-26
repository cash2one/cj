<?php
/**
 * cporderdetail.php
 * 订单详情
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_cporderdetail extends voa_c_frontend_travel_base {

	public function execute() {
		$this->view->set('refer', get_referer('/frontend/travel/cplist'));
		$this->view->set('orderid', $this->request->get('orderid'));
		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/cporderdetail');
	}

}
