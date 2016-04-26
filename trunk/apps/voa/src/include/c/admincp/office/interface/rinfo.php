<?php

/**
 * voa_c_admincp_office_interface_rinfo
 * 企业后台/测试应用/日志详情
 * Create By xubinshan
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_interface_rinfo extends voa_c_admincp_office_interface_base {

	public function execute() {

		// 条件
		$conds = $this->request->getx('n_id');

		$uda_info = &uda::factory('voa_uda_frontend_interface_rinfo');
		// 读取列表及总数
		$info = array();

		$uda_info->execute($conds, $info);

		$this->view->set('interface', $info);

		// 输出模板
		$this->output('office/interface/rinfo');
	}

}
