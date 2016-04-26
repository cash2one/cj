<?php
/**
 * 更新套件信息
 * $Author$
 * $Id$
 */

class voa_c_api_suite_post_update extends voa_c_api_suite_abstract {

	public function _before_action($action) {

		$params = array(
			'domain' => $this->request->get('domain'),
			'suiteid' => $this->request->get('suiteid'),
			'auth_code' => $this->request->get('auth_code'),
			'ts' => $this->request->get('ts'),
			'sig' => $this->request->get('sig')
		);

		if (voa_h_func::sig_check($params)) {
			$this->_require_login = false;
		}

		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		$suiteid = (string)$this->_get('suiteid');

		// 授权信息
		$authdata = $this->_get('authdata');
		$authdata = unserialize($authdata);

		$oa_suite = array(
			'auth_corpid' => $authdata['auth_corp_info']['corpid'],
			'permanent_code' => $authdata['permanent_code'],
			'access_token' => $authdata['access_token'],
			'expires' => startup_env::get('timestamp') + ($authdata['expires_in'] * 0.8),
			'authinfo' => serialize($authdata)
		);
		// 取套件id
		$serv_suite = &service::factory('voa_s_oa_suite');
		if (!$suite = $serv_suite->fetch_by_suiteid($suiteid)) {
			$oa_suite['suiteid'] = $suiteid;
			$serv_suite->insert($oa_suite);
		} else {
			$serv_suite->update($oa_suite, "`suiteid`='{$suiteid}'");
		}

		return true;
	}

}
