<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace PubApi\Controller\Frontend;

class IndexController extends AbstractController {

	public function Index() {

		$this->show('[IndexController->Index]');
		$this->_output("Frontend/Index/Index");
	}


	// Test
	public function Test() {

		$this->_output("Frontend/Index/Test");
	}


	// Login
	public function Login() {

		// 回调URL
		$redirect_uri = I('get.redirect_uri', '', 'trim');
		$redirect_uri = urldecode($redirect_uri);
		$ep_id = I('get.ep_id');
		// 登录URL
		$url = U('/PubApi/Api/EnterpriseAdminer/Login');
		$this->assign('login_url', $url);
		$this->assign('redirect_uri', $redirect_uri);
		$this->assign('ep_id', $ep_id);

		$this->_output("Frontend/Index/Login");
	}
}
