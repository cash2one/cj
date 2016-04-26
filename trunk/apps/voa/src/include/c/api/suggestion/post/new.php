<?php
/**
 * 新建议操作
 * $Author$
 * $Id$
 */

class voa_c_api_suggestion_post_new extends voa_c_api_base {

	public function execute() {

		$message = (string)$this->_get('message', '');
		$domain = (string)$this->_get('domain', '');
		$username = (string)$this->_get('username', '');

		if (empty($message)) {
			return true;
		}

		$serv_sug = &service::factory('voa_s_uc_suggestion');
		$serv_sug->insert(array(
			'sug_message' => $message,
			'sug_domain' => $domain,
			'sug_username' => $username
		));

		return true;
	}

}
