<?php
/**
 * 商机详情
 * $Author$
 * $Id$
 */

class voa_c_frontend_sale_business_view extends voa_c_frontend_sale_base {
	
	public function execute() {
		// 请求参数
		$bid = $this->request->get('bid');
		$uda_coustmer = &uda::factory('voa_uda_frontend_sale_business');
		
		$request = array(
						'bid' => $bid
						);
		$result = array();
		$uda_coustmer->doit($request,$result);
		//获取客户ID
		$s_coustmer = &service::factory('voa_s_oa_sale_coustmer');
		$all = $s_coustmer->list_all();

		$coustmer = array();
		if (!empty($all) && is_array($all)) {
			$coustmer = array_column($all, 'companyshortname', 'scid');
		}

		$this->view->set('coustmer', $coustmer);
		$this->view->set('data', $result);
		$this->view->set('type', voa_d_oa_sale_business::$type);
		$this->view->set('navtitle', '商机详情');

		// 引入应用模板
		$this->_output('mobile/'.$this->_plugin_identifier.'/business/add');

		return true;
	}

}
