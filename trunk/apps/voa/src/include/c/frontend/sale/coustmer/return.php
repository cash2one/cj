<?php
/**
 * 销售管理- 回访
 * $Author$ tim_zhang
 * $Id$
 */

class voa_c_frontend_sale_coustmer_return extends voa_c_frontend_sale_base {
	
	public function execute() {
		
		$scid = $this->request->get('scid');
		$this->view->set('scid', $scid);
		$this->view->set('navtitle', '回访');
		
		// 引入应用模板
		$this->_output('mobile/'.$this->_plugin_identifier.'/coustmer/return');
		
		return true;

	}

}
