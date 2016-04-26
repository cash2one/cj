<?php
/**
 * MemberController.class.php
 * $author$
 */

namespace PubApi\Controller\Frontend;

class MemberController extends AbstractController {

	public function before_action($action = '') {

		// js 登录标识
		if ('JsLogin' == $action) {
			cfg('JS_LOGIN', true);
		}

		return parent::before_action($action);
	}

	// Login
	public function Login() {

		$url = I('get.url', '', 'trim');
		$os = I('get._os', '', 'trim');
		$top = I('get._top', '', 'trim');
		$this->assign('os', $os);
		$this->assign('top', $top);
		$this->assign('redirectUrl', $url);
		$this->_output('Common@Frontend/Redirect');
		return true;
	}

	// js Login
	public function JsLogin() {

		$login_serv = D('PubApi/Login', 'Service');
		// 格式化用户信息
		$user = array();
		$login_serv->format_user($user);

		// 取jsapi授权签名相关
		$jscfg = array();
		$login_serv->get_js_config($jscfg, I('get._fronturl', '', 'trim'));

		$result = array('user' => $user, 'jscfg' => $jscfg);
		$result = generate_api_result($result);
		$javascript = "var _user = " . json_encode($result) . ";\nwindow.top.authComplete(_user);";
		// 如果是开发环境
		if ('dev' == I('get._env')) {
			exit($javascript);
		}

		$this->assign('javascript', $javascript);
		$this->_output('Common@Frontend/Redirect');
		return true;
	}

}
