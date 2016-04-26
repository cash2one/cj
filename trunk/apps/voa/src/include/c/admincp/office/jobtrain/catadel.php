<?php
/**
* voa_c_admincp_office_jobtrain_delete
* Create By wowxavi
* $Author$
* $Id$
*/
class voa_c_admincp_office_jobtrain_catadel extends voa_c_admincp_office_jobtrain_base {

	public function execute() {
		$id = rintval($this->request->get('id'));
		$uda = &uda::factory('voa_uda_frontend_jobtrain_category');
		try {
			if ($uda->del_cata($id)) {
				$this->message('success', '指定'.$this->_module_plugin['cp_name'].'信息删除完毕', $this->cpurl($this->_module, $this->_operation, 'catalist', $this->_module_plugin_id));
			} else {
				$this->message('error', '指定'.$this->_module_plugin['cp_name'].'信息删除操作失败');
			}
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}
	}
}