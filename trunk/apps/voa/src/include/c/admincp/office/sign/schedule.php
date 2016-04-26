<?php

/**
 * voa_c_admincp_office_sign_schedule
 * 人员排班
 *
 * $Id$
 */
class voa_c_admincp_office_sign_schedule extends voa_c_admincp_office_sign_base {

    public function execute() {

        $this->view->set('pluginId', $this->_module_plugin_id);
		$this->output('office/sign/schedule');
	}

}
