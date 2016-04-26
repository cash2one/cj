<?php
/**
 * voa_c_admincp_office_train_atdelete
 * 企业后台/微办公管理/培训/删除文章
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_train_atdelete extends voa_c_admincp_office_train_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->post('delete');
		$ta_id = $this->request->get('ta_id');

		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($ta_id) {
			$ids = rintval($ta_id, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}

		if (empty($ids)) {
			$this->message('error', '请指定要删除的 '.$this->_module_plugin['cp_name'].' 数据');
		}


		$uda = &uda::factory('voa_uda_frontend_train_action_articledelete');
		if ($uda->delete($ids)) {
			$this->message('success', '指定'.$this->_module_plugin['cp_name'].'信息删除完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'atlist', $this->_module_plugin_id)), false);
		} else {
			$this->message('error', '指定'.$this->_module_plugin['cp_name'].'信息删除操作失败');
		}
	}

}
