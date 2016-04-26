<?php
/**
 * voa_c_frontend_index_home
 * 首页
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_index_home extends voa_c_frontend_base {

	protected function _before_action($action) {
		$this->_mobile_tpl = true;

		return parent::_before_action($action);
	}

	public function execute() {

		$this->view->set('controler', $this->controller_name);

// 		/** 调用接口, 入 center 库 */
// 		$auth_key = config::get('voa.rpc.client.auth_key');
// 		$args = array(
// 			'realname' => '朱逊',
// 			'wxuser' => 'zhuxuntest',
// 			'job' => '高级工程师',
// 			'mobilephone' => '13512345678',
// 			'telephone' => '021-87654567',
// 			'email' => 'zhuxun37@gmail.com',
// 			'company' => '康盛',
// 			'address' => '上海市宁夏路201号',
// 			'postcode' => '123456',
// 			'nc_id' => '10'
// 		);

// 		/** 调用接口, 自动开通企业 oa */
// 		$client = new voa_client_oa($auth_key);
// 		$url = config::get(startup_env::get('app_name') . '.oa_http_scheme').'qywx.vchangyi.com/api.php';
// 		$method = 'recognition.namecard';
// 		$result = $client->call($url, $method, $args);

		$this->_output('mobile/index/home');
	}
}
