<?php
/**
 * 销售管理-新增客户
 * $Author$ tim_zhang
 * $Id$
 */

class voa_c_frontend_sale_coustmer_add extends voa_c_frontend_sale_base {
	
	public function execute() {
		//获取参数
		$scid = $this->request->get('scid');
		$result = array();
		if($scid) {
			$uda_coustmer = &uda::factory('voa_uda_frontend_sale_coustmer');
		
			$request = array(
							'scid' => $scid
							);
			$uda_coustmer->doit($request,$result);
		}
		//获取状态分类
		$base = &uda::factory('voa_uda_frontend_sale_base');
		$fields = $base->get_type(voa_d_oa_sale_type::TYPE_FIELD);
		//获取来源
		$source = $base->get_type(voa_d_oa_sale_type::TYPE_SOURCE);
		$sources = array();
        if (!empty($source) && is_array($source)) {
            $sources = array_column($source, 'name', 'stid');
        }

		$this->view->set('data', $result);
		$this->view->set('scid', $scid);
		$this->view->set('source', $sources);
		$this->view->set('fields', $fields);
		if (empty($result)) {
			$this->view->set('navtitle', '新建客户');
		}else {
			$this->view->set('navtitle', '编辑客户');
		}
		// 引入应用模板
		$this->_output('mobile/'.$this->_plugin_identifier.'/coustmer/add');
		
		return true;

	}

}
