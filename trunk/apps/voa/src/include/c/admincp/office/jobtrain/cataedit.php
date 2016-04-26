<?php
/**
* voa_c_admincp_office_jobtrain_cataadd
* Create By wowxavi
* $Author$
* $Id$
*/
class voa_c_admincp_office_jobtrain_cataedit extends voa_c_admincp_office_jobtrain_base {

	public function execute() {
		$uda = &uda::factory('voa_uda_frontend_jobtrain_category');
		$id = $this->request->get('id');
		if ($this->_is_post()) {
			$data = $this->request->postx();
			try {
				$args = array(
					'id' => rintval($data['id'])
				);
				$uda->save_cata($data, $args);
				$this->message('success', '保存成功', $this->cpurl($this->_module, $this->_operation, 'catalist', $this->_module_plugin_id));
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}
		}
		$result = $uda->get_cata($id);
		$result['departments'] = rjson_encode(array_values($result['departments']));
		$result['members'] = rjson_encode(array_values($result['members']));

		if($result['pid']){
			$parent = $uda->get_cata($result['pid']);
			$this->view->set('parent', $parent);
		}

		$this->view->set('result', $result);
		$this->output('office/jobtrain/cataadd');
	}

}