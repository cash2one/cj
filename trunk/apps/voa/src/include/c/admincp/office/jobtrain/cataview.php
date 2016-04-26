<?php
/**
* voa_c_admincp_office_jobtrain_cataview
* Create By wowxavi
* $Author$
* $Id$
*/
class voa_c_admincp_office_jobtrain_cataview extends voa_c_admincp_office_jobtrain_base {

	public function execute() {
		try {
			$uda = &uda::factory('voa_uda_frontend_jobtrain_category');
			$id = $this->request->get('id');
			
			$result = $uda->get_cata($id);
			if($result['pid']){
				$parent = $uda->get_cata($result['pid']);
				$this->view->set('parent', $parent);
			}
			$this->view->set('result', $result);
			$this->output('office/jobtrain/cataview');
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}
	}

}