<?php
/**
 * cporderlist.php
 * 销售查看自己售出的订单列表
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_cporderlist extends voa_c_frontend_travel_base {

	public function execute() {

		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/cporderlist');
	}

}
