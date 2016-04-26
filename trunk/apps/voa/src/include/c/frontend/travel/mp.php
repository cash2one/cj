<?php
/**
 * mp.php
 * 公众号入口
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_mp extends voa_c_frontend_travel_base {

	public function execute() {

		/**print_r($this->_user);
		$serv_wx = &service::factory('voa_weixin_service');
		echo "<br />".$serv_wx->oauth_url_userinfo('https://test.vchangyi.com/frontend/travel/mp')."<br />";
		echo $serv_wx->get_access_token();*/
		$scheme = config::get('voa.oa_http_scheme');
		echo "<br />".$serv_wx->oauth_url_userinfo($scheme.'test.vchangyi.com/frontend/travel/mp')."<br />";

		if (startup_env::get('timestamp') > $this->_user['web_token_expires']) {
			$this->session->destroy();
			$this->session->set('logintype', 'mp');
			$serv = voa_weixin_service::instance();
			header('Location: '.$serv->oauth_url_base(voa_h_func::get_auth_back_url('mp')));
			$this->response->stop(); exit;
			return false;
		}

		//获取微信地址共享接口参数
		$wepay = &service::factory('voa_wepay_service');
		$params = array();
		$wepay->get_addr_params($params);

		$this->view->set('addressParams', rjson_encode($params));

		$this->_mobile_tpl = true;
		$this->_output('mobile/travel/mp');
	}

}
