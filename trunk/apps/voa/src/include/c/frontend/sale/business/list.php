<?php
/**
 * 销售管理-商机管理
 * $Author$ tim_zhang
 * $Id$
 */

class voa_c_frontend_sale_business_list extends voa_c_frontend_sale_base {
	
	public function execute() {

		$this->view->set('navtitle', '商机管理');

		$this->view->set('types', voa_d_oa_sale_business::$type);
		// 引入应用模板
		$this->_output('mobile/'.$this->_plugin_identifier.'/business');
		
		return true;

	}

}
