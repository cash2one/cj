<?php
/**
 * voa_c_admincp_office_train_atdelete
 * 企业后台/微办公管理/培训/删除文章
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_askfor_delete extends voa_c_admincp_office_askfor_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->post('delete');
		$aft_id = $this->request->get('af_id');

		if (!empty($delete)) {
			$ids = rintval($delete, true);
		} elseif ($aft_id) {
			$ids = rintval($aft_id, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}

		if (empty($ids)) {
			$this->message('error', '请指定要操作的 '.$this->_module_plugin['cp_name'].' 数据');
		}


		$uda = &uda::factory('voa_uda_frontend_askfor_delete');
		if ($uda->askfor_delete($ids)) {
			$this->message('success', '指定'.$this->_module_plugin['cp_name'].'信息操作完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);
		} else {
			$this->message('error', '指定'.$this->_module_plugin['cp_name'].'信息操作失败');
		}
	}

}
