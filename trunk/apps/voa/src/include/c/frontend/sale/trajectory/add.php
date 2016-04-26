<?php
/**
 * 销售管理-新增轨迹
 * $Author$ tim_zhang
 * $Id$
 */

class voa_c_frontend_sale_trajectory_add extends voa_c_frontend_sale_base {
	
	public function execute() {
		
		//获取状态分类
		$base = &uda::factory('voa_uda_frontend_sale_base');
		$type = $base->get_type(voa_d_oa_sale_type::TYPE_STATUS);
		if (!empty($type) &&
				is_array($type)) {
			$type = array_column($type, 'name', 'stid');
		}

		//获取客户ID
		$s_coustmer = &service::factory('voa_s_oa_sale_coustmer');
		$all = $s_coustmer->list_all();
		$coustmer = array();
        if (!empty($all) &&
				is_array($all)) {
            foreach($all as $kc => $vc) {
                $coustmer[$kc] = $vc['companyshortname'].' ('.$vc['type'].')';
            }
        }
		
		$this->view->set('coustmer', $coustmer);
		$this->view->set('types', $type);
		$this->view->set('navtitle', '新建轨迹');
		// 引入应用模板
		$this->_output('mobile/'.$this->_plugin_identifier.'/trajectory/add');
		
		return true;

	}

}
