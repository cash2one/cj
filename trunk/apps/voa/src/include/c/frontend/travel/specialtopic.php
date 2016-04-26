<?php
/**
 * specialtopic.php
 * 选择规格
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_specialtopic extends voa_c_frontend_travel_basemp {

	public function execute() {

		$this->view->set('mtid', $this->request->get('mtid'));

		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/specialtopic');
	}

}
