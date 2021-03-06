<?php
/**
 * voa_c_api_sale_get_coustmer_view
 * 客户详情
 * $Author$ tim_zhang
 * $Id$
 */
class voa_c_api_sale_get_coustmer_view extends voa_c_api_base {

	
	public function execute() {
		// 需要的参数
		$fields = array(
			// 客户id
			'scid' => array('type' => 'int', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			// 检查参数
			return false;
		}
		
		$uda_coustmer = &uda::factory('voa_uda_frontend_sale_coustmer');
		
		$request = array(
						'scid' => $this->_params['scid']
						);
		$reslut = array();
		$uda_coustmer->doit($request,$reslut);
		
		if(empty($reslut)) {
			return $this->_set_errcode(voa_errcode_api_sale::COUSTMER_NULL);
		}
		// 输出结果
		$this->_result = $reslut;
		return true;
	}
}
