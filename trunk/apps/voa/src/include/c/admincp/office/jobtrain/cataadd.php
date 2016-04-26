<?php
/**
* voa_c_admincp_office_jobtrain_cataadd
* Create By wowxavi
* $Author$
* $Id$
*/
class voa_c_admincp_office_jobtrain_cataadd extends voa_c_admincp_office_jobtrain_base {

	public function execute() {
		$uda = &uda::factory('voa_uda_frontend_jobtrain_category');
		$pid = rintval($this->request->get('pid'));
		if ($this->_is_post()) {
			$data = $this->request->postx();
			try {
				$args = array(
					'pid' => rintval($data['pid'])
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

		$result['is_open'] = 1;
		$result['is_all'] = 1;
		if($pid){
			$parent = $uda->get_cata($pid);
			$result['is_all'] = $parent['is_all'];
			$result['departments'] = rjson_encode(array_values($parent['departments']));
			$result['members'] = rjson_encode(array_values($parent['members']));
			$result['cd_ids'] = $parent['cd_ids'];
			$result['m_uids'] = $parent['m_uids'];
			$result['pid'] = $parent['id'];
			$this->view->set('parent', $parent);
		}
		$this->view->set('result', $result);
		$this->view->set('pid', $pid);
		$this->output('office/jobtrain/cataadd');
	}

}