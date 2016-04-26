<?php

/**
 * 升级考勤推送操作手册图文消息
 * $Author$
 * $Id$
 */
class voa_c_api_sign_get_upgrade extends voa_c_api_sign_base {

	protected function _before_action($action = '') {
		$this->_require_login = false;

		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		return true;
		$str = file_get_contents(APP_PATH . '/data/sign_domain.txt');
		$str = substr($str, 0, -1);
		$domain_array = explode(',', $str);

		$snoopy = new snoopy();

		foreach($domain_array as $domain){
			$url = 'http://' . $domain . '.vchangyi.com/api/sign/get/send';
			$snoopy->fetch($url);
		}

		return true;
	}

}
