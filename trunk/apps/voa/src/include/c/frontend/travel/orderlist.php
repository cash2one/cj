<?php
/**
 * orderlist.php
 * 订单列表
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_orderlist extends voa_c_frontend_travel_basemp {

	public function execute() {

		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/orderlist');
	}

}
