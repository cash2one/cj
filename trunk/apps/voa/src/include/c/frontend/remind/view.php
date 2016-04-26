<?php
/**
 * 查看定时提醒
 * $Author$
 * $Id$
 */

class voa_c_frontend_remind_view extends voa_c_frontend_remind_base {

	public function execute() {
		/** 定时提醒ID */
		$rm_id = rintval($this->request->get('rm_id'));

		/** 读取定时提醒信息 */
		$serv = &service::factory('voa_s_oa_remind', array('pluginid' => startup_env::get('pluginid')));
		$remind = $serv->fetch_by_id($rm_id);
		if (empty($rm_id) || empty($remind)) {
			$this->_error_message('remind_is_not_exists');
		}

		if ($remind['m_uid'] != $this->_user['m_uid']) {
			$this->_error_message('no_privilege');
		}

		$remind['rm_subject'] = rhtmlspecialchars($remind['rm_subject']);
		$remind['rm_message'] = rhtmlspecialchars($remind['rm_message']);

		$this->view->set('action', $this->action_name);
		$this->view->set('remind', $remind);
		$this->view->set('navtitle', '查看定时提醒');

		$this->_output('remind/view');
	}

}
