<?php
/**
 * voa_c_admincp_office_notice_delete
 * 企业后台/微办公/通知公告/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_notice_delete extends voa_c_admincp_office_notice_base {

	public function execute() {

		$id = $this->request->get('nt_id');
		$id = rintval($id, false);
		$ids = $this->request->post('delete');
		$ids = rintval($ids, true);
		if ($id) {
			$ids = array($id);
		}

		if (empty($ids)) {
			$this->message('error', '请指定要删除的通知公告');
		}

		$uda = &uda::factory('voa_uda_frontend_notice_delete');
		if (!$uda->notice($ids)) {
			$this->message('error', $uda->error);
		}

		$this->message('success', '指定通知公告删除操作完毕', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id), false);
	}

}
