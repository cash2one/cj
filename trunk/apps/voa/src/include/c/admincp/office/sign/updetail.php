<?php

/**
 * voa_c_admincp_office_sign_edit
 * 企业后台/微办公管理/考勤签到/编辑状态
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_sign_updetail extends voa_c_admincp_office_sign_base {

	public function execute() {

		$sl_id = $this->request->get('sl_id');

		if (empty($sl_id)) {
			$this->message('error', '获取详情失败');
		}
		$serv_loc = &service::factory('voa_s_oa_sign_location');

		// 获取外勤记录数据
		$uda = &uda::factory('voa_uda_frontend_sign_out');
		$data = $serv_loc->get($sl_id);
		// 格式化数据
		$data = $uda->format($data);

		$this->view->set('data', $data);
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('sl_id' => $sl_id)));

		$this->output('office/sign/updetail');
	}


}
