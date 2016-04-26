<?php
/**
 * orderdetail.php
 * 订单详情
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_orderdetail extends voa_c_frontend_travel_basemp {

	public function execute() {

		$this->view->set('refer', get_referer('/frontend/travel/list'));
		$this->view->set('orderid', $this->request->get('orderid'));

		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/orderdetail');
	}

}
