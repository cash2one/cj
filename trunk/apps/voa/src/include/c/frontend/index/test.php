<?php
/**
 * voa_c_frontend_index_test
 * 首页
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_index_test extends voa_c_frontend_base {

	public function _before_action($action) {

		exit;
		// 使用手机H5新模板
		$this->_mobile_tpl = true;

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {

		/**$client = voa_h_rpc::phprpc('http://uc.vcy.com/OaRpc/Rpc/Suite');
		$result = $client->get_by_suiteid('tj0129f84436fb3a58');
		var_dump($result);*/
		$this->_output('mobile/index/test');
	}

}
