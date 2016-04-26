<?php
/**
 * mycart.php
 * 我的购物车
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_mycart extends voa_c_frontend_travel_basemp {

	public function execute() {

		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/mycart');
	}

}
