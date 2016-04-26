<?php
/**
 * PasswdController.class.php
 * $author$
 */

namespace OaRpc\Controller\Rpc;

class PasswdController extends AbstractController {

	// 通过手机验证码修改密码
	public function reset_by_smscode($enumber, $mobile, $smscode, $passwd) {

		// 调用验证方法
		$serv_mobile = D('Common/Mobile', 'Service');
		if (!$serv_mobile->verify_smscode($mobile, $smscode)) {
			E($serv_mobile->get_errcode() . ':' . $serv_mobile->get_errmsg());
			return false;
		}

		// 根据企业号读取企业信息
		$enterprise = array();
		$serv_ep = D('Common/Enterprise', 'Service');
		if (!$serv_ep->check_enumber($enterprise, $enumber)) {
			E($serv_ep->get_errcode() . ':' . $serv_ep->get_errmsg());
			return false;
		}

		// 修改密码?

		return true;
	}

	// 通过旧密码修改密码
	public function reset_by_oldpw() {

	}

}
