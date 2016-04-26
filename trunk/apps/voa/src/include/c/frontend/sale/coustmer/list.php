<?php
/**
 * 销售管理-首页
 * $Author$ tim_zhang
 * $Id$
 */

class voa_c_frontend_sale_coustmer_list extends voa_c_frontend_sale_base {
	
	public function execute() {

		//获取状态分类
		$base = &uda::factory('voa_uda_frontend_sale_base');

		$status_list = $base->get_type(voa_d_oa_sale_type::TYPE_STATUS);
		$statuses = array();
		if (!empty($status_list) && is_array($status_list)) {
			$statuses = array_column($status_list, 'name', 'stid');
		}
		$statuses[0] = '全部';
		ksort($statuses);

		//获取客户来源
		$source = $base->get_type(voa_d_oa_sale_type::TYPE_SOURCE);
		$sources = array();
		if (!empty($source) && is_array($source)) {
			$sources = array_column($source, 'name', 'stid');
		}
		$sources[0] = '全部';
		ksort($sources);

		$this->view->set('statuses', $statuses);
		$this->view->set('sources', $sources);
		$this->view->set('navtitle', '客户管理');
		
		// 引入应用模板
		$this->_output('mobile/'.$this->_plugin_identifier.'/coustmer');
		
		return true;

	}

}
