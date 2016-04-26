<?php
/**
 * 销售管理-新增客户
 * $Author$ tim_zhang
 * $Id$
 */

class voa_c_frontend_sale_coustmer_view extends voa_c_frontend_sale_base {
	
	public function execute() {

		$this->view->set('navtitle', '客户详情');
		$scid = $this->request->get('scid');
		
		$this->view->set('scid', $scid);
		// 引入应用模板
		$this->_output('mobile/'.$this->_plugin_identifier.'/coustmer/view');
		
		return true;

	}

}
