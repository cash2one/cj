<?php
/**
 * voa_c_admincp_office_footprint_delete
 * 企业后台/微办公/销售轨迹/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_footprint_delete extends voa_c_admincp_office_footprint_base {

	public function execute() {

		$fp_id = $this->request->get('fp_id');
		$fp_id = rintval($fp_id);
		$fp_ids = $this->request->post('delete');
		$fp_ids = rintval($fp_ids, true);
		if ($fp_id > 0) {
			$fp_ids = array($fp_id);
		}

		if (empty($fp_ids)) {
			$this->message('error', '请指定要删除的轨迹数据');
		}

		$uda = &uda::factory('voa_uda_frontend_footprint_delete');
		if ($uda->delete($fp_ids, array('pluginid' => $this->_module_plugin_id))) {
			$this->message('success', '删除指定的轨迹数据操作完毕', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id), false);
		} else {
			$this->message('error', '轨迹数据删除操作失败');
		}



	}

}
