<?php
/**
 * cpturnover.php
 * 销售的业绩详情页面
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_cpturnover extends voa_c_frontend_travel_base {

	public function execute() {


		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/cpturnover');
	}

}
