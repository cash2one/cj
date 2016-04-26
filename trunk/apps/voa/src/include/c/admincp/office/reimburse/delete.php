<?php
/**
 * voa_c_admincp_office_reimburse_delete
 * 企业后台/微办公管理/报销/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_reimburse_delete extends voa_c_admincp_office_reimburse_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->post('delete');
		$rb_id = $this->request->get('rb_id');

		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($rb_id) {
			$ids = rintval($rb_id, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}

		if (empty($ids)) {
			$this->message('error', '请指定要删除的 '.$this->_module_plugin['cp_name'].' 数据');
		}

		$shard_key = array('pluginid' => $this->_module_plugin_id);
		$uda_reimburse_delete = &uda::factory('voa_uda_frontend_reimburse_delete');
		if ($uda_reimburse_delete->reimburse_delete($ids, $shard_key)) {
			$this->message('success', '指定'.$this->_module_plugin['cp_name'].'信息删除完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);
		} else {
			$this->message('error', '指定'.$this->_module_plugin['cp_name'].'信息删除操作失败');
		}
	}

}
