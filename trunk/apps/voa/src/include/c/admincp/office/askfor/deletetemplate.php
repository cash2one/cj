<?php
/**
 * voa_c_admincp_office_train_atdelete
 * 企业后台/微办公管理/培训/删除文章
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_askfor_deletetemplate extends voa_c_admincp_office_askfor_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->post('delete');
		$aft_id = $this->request->get('aft_id');
		$action = trim($this->request->post('action'));

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


		$uda = &uda::factory('voa_uda_frontend_askfor_template_action');
		if ($uda->template_action($ids, $action)) {
			/** 更新缓存操作 */
			$uda_base = &uda::factory('voa_uda_frontend_base');
			$uda_base->update_cache();
			$this->message('success', '指定'.$this->_module_plugin['cp_name'].'信息操作完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'template', $this->_module_plugin_id)), false);
		} else {
			$this->message('error', '指定'.$this->_module_plugin['cp_name'].'信息操作失败');
		}
	}

}
