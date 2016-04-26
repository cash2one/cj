<?php
/**
 * MobileController.class.php
 * $author$
 */

namespace OaRpc\Controller\Rpc;

class MobileController extends AbstractController {


	/**
	 * 发送手机验证码
	 * @param string $mobile 手机号码
	 * @param string $ip IP 地址
	 * @param string $action 操作标识
	 * @return boolean
	 */
	public function send_smscode($mobile, $ip, $formhash, $seccode, $action = 'register') {

		/**
		 * 操作标识
		 * register 注册操作
		 * pwdreset 重置密码操作
		 * oaresetpwd 重置Oa密码
		 */
		$acs = array('register', 'pwdreset', 'oaresetpwd');
		if (empty($action) || !in_array($action, $acs)) {
			$action = 'register';
		}

		// 检查图片验证码
		$serv_sc = D('Common/Seccode', 'Service');
		$sc_result = array();
		if (!$serv_sc->get_unuse_by_formhash_seccode($sc_result, $formhash, $seccode)) {
			E('_ERR_SECCODE_ERROR');
			return false;
		}

		// 如果是注册, 则判断该手机是否已经注册
		if ('register' == $action) {
			$serv = D('Common/Enterprise', 'Service');
			if (0 < $serv->count_by_mobile($mobile)) {
				E('_ERR_MOBILE_IS_REGISTER');
				return false;
			}
		}

		// 获取短信模板
		$msg = cfg('SMS_TPL.' . strtoupper($action));
		// 调用短信发送方法
		$serv_mobile = D('Common/Mobile', 'Service');
		if (!$serv_mobile->send_smscode($mobile, $ip, $msg)) {
			E($serv_mobile->get_errcode() . ':' . $serv_mobile->get_errmsg());
			return false;
		}

		// 重置验证码状态为已用
		$serv_sc->set_used($sc_result['id']);

		return true;
	}

	/**
	 * 校验手机验证码
	 * @param string $mobile 手机号码
	 * @param string $smscode 验证码
	 */
	public function verify_smscode($mobile, $smscode) {

		// 调用验证方法
		$serv_mobile = D('Common/Mobile', 'Service');
		if (!$serv_mobile->verify_smscode($mobile, $smscode)) {
			E($serv_mobile->get_errcode() . ':' . $serv_mobile->get_errmsg());
			return false;
		}

		return true;
	}

}
