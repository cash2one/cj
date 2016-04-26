<?php
/**
 * 审批流程列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_askfor_template extends voa_c_frontend_askfor_base {

	public function execute() {

		$this->redirect(config::get('voa.askfor.template'));

		return true;

		$templates = array();
		$uda  = &uda::factory('voa_uda_frontend_askfor_template_list');
		$uda->template_list($templates);

		$this->view->set('templates', $templates);
		$this->view->set('navtitle', '选择审批流程');

		$this->_output('mobile/askfor/template');
	}

}

