<?php
/**
 * voa_c_admincp_office_askoff_delete
 * 企业后台/微办公管理/请假审批/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_askoff_delete extends voa_c_admincp_office_askoff_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->post('delete');
		$ao_id = $this->request->get('ao_id');

		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($ao_id) {
			$ids = rintval($ao_id, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}

		if (empty($ids)) {
			$this->message('error', '请指定要删除的'.$this->_module_plugin['cp_name'].'记录');
		}

		$shard_key = array('pluginid' => $this->_module_plugin_id);
		$uda_askoff_delete = &uda::factory('voa_uda_frontend_askoff_delete');
		if ($uda_askoff_delete->askoff_delete($ids, array('pluginid' => $this->_module_plugin_id))) {
			$this->message('success', '指定'.$this->_module_plugin['cp_name'].'信息删除完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);
		} else {
			$this->message('error', '指定'.$this->_module_plugin['cp_name'].'信息删除操作失败');
		}

	}

}
