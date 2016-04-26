<?php
/**
 * 编辑报名信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_campaign_custom extends voa_c_frontend_campaign_base {

	public function execute() {

		$id = intval($_GET['id']);
		$this->view->set('id', $id);
		$this->view->set('saleid', $this->_user['m_uid']);
		$this->view->set('time', time());

		$this->_output('campaign/custom');
	}
}
