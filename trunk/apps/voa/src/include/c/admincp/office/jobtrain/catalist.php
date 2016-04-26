<?php
/**
* voa_c_admincp_office_jobtrain_catalist
* Create By wowxavi
* $Author$
* $Id$
*/
class voa_c_admincp_office_jobtrain_catalist extends voa_c_admincp_office_jobtrain_base {

	public function execute() {

		try {
			$uda = &uda::factory('voa_uda_frontend_jobtrain_category');
			$catas = $uda->list_cata(true);
			if ($this->_is_post()) {
				$uda->save_catalist($_POST);
				$this->message('success', '保存成功', get_referer($this->cpurl($this->_module, $this->_operation, 'catalist', $this->_module_plugin_id)), false);
			}
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}

		$this->view->set('catas', $catas);
		$this->view->set('cataadd_url', $this->cpurl($this->_module, $this->_operation, 'cataadd', $this->_module_plugin_id));
		$this->view->set('catadel_url', $this->cpurl($this->_module, $this->_operation, 'catadel', $this->_module_plugin_id, array('id'=>'')));
		$this->view->set('cataedit_url', $this->cpurl($this->_module, $this->_operation, 'cataedit', $this->_module_plugin_id, array('id'=>'')));
		$this->view->set('cataview_url', $this->cpurl($this->_module, $this->_operation, 'cataview', $this->_module_plugin_id, array('id'=>'')));
		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, 'catalist', $this->_module_plugin_id));
		$this->output('office/jobtrain/catalist');
	}

}