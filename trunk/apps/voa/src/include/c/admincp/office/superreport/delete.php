<?php
/**
 * voa_c_admincp_office_seperreport_delete
 * 企业后台/微办公管理/超级报表/删除报表
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_superreport_delete extends voa_c_admincp_office_superreport_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->get('delete');
		$dr_id = $this->request->get('dr_id');

		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($dr_id) {
			$ids = rintval($dr_id, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}

		if (empty($ids)) {
			$this->message('error', '请指定要删除的 '.$this->_module_plugin['cp_name'].' 数据');
		}

		$uda = &uda::factory('voa_uda_frontend_superreport_delete');
		if ($uda->delete($ids)) {
			$this->message('success', '指定'.$this->_module_plugin['cp_name'].'信息删除完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);
		} else {
			$this->message('error', '指定'.$this->_module_plugin['cp_name'].'信息删除操作失败');
		}
	}

}
