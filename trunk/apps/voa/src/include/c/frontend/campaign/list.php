<?php
/**
 * 活动中心
 * $Author$
 * $Id$
 */

class voa_c_frontend_campaign_list extends voa_c_frontend_campaign_base {

	public function execute() {

		$this->view->set('saleid', $this->_user['m_uid']);
		$this->_output('campaign/list');
	}
}
