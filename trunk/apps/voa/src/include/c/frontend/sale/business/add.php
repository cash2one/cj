<?php
/**
 * 销售管理-新增商机
 * $Author$ tim_zhang
 * $Id$
 */

class voa_c_frontend_sale_business_add extends voa_c_frontend_sale_base {
	
	public function execute() {
		
		//获取客户ID
		$s_coustmer = &service::factory('voa_s_oa_sale_coustmer');
		$all = $s_coustmer->list_all();

		$coustmer = array();
        if (!empty($all) && is_array($all)) {
            $coustmer = array_column($all, 'companyshortname', 'scid');
        }

		$this->view->set('coustmer', $coustmer);
		$this->view->set('type', voa_d_oa_sale_business::$type);
		$this->view->set('navtitle', '新增商机');
		
		// 引入应用模板
		$this->_output('mobile/'.$this->_plugin_identifier.'/business/add');
		
		return true;

	}

}
