<?php
/**
 * voa_c_uc_wechat_callback
 * 微信登录回调页面
 * 用于微信回调，本地判断绑定与否并决定是登录还是绑定
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_wechat_callback extends voa_c_uc_wechat_base {

	public function execute() {

		// 获取当前执行的企业站
		$enumber = $this->request->get('enumber');
		$enumber = (string)$enumber;

		$uda_enterprise = &uda::factory('voa_uda_uc_enterprise');
		// 当前企业信息
		$enterprise = array();
		if (!$uda_enterprise->check_enumber($enumber, $enterprise)) {
			// 检查企业号出错
			echo $uda_enterprise->errmsg.'['.$uda_enterprise->errcode.']';
			exit;
		}

		// 企业站域名
		$domain = $enterprise['ep_domain'];
		// 指定动作为微信登录
		$action = 'wechat';
		// 传入给企业站的错误代码
		$errcode = 0;
		// 传入给企业站的错误信息
		$errmsg = '';
		// 传入给企业站的执行结果
		$result = array();

		// 引入微信登录处理类
		$wechat_login = new voa_wechat_login();

		// 获取微信code
		$code = $wechat_login->get_code();

		// 初始化微信用户信息 （unionid机制）
		$wechat_user = array();

		if ($code === null) {
			$errcode = -1;
			$errmsg = '您必须授权微信才可以登录';
		} elseif ($code === false) {
			$errcode = -2;
			$errmsg = '获取微信CODE信息发生错误：'.$wechat_login->errmsg;
		}

		if (!$errcode) {
			// 上面已经获取到了code，则尝试获取token

			// 通过code获取token信息
			$token_info = $wechat_login->get_access_token($code);
			if (!$token_info) {
				$errcode = -3;
				$errmsg = '获取微信授权信息发生错误：'.$wechat_login->errmsg;
			} elseif (!isset($token_info['access_token'])) {
				$errcode = -4;
				$errmsg = '获取微信授权信息发生错误：token error';
			}
		}

		if (!$errcode) {
			// 上传已经获取到了token信息，则尝试获取unionid

			// 使用unionid机制获取微信用户信息
			$wechat_user = $wechat_login->get_userinfo_for_unionid($token_info['access_token'], $token_info['openid']);
			if (!$wechat_user) {
				$errcode = -5;
				$errmsg = '获取微信用户授权信息发生错误：'.$wechat_login->errmsg;
			} elseif (!isset($wechat_user['unionid'])) {
				$errcode = -6;
				$errmsg = '获取微信用户授权信息发生错误：unionid error';
			} elseif (!$wechat_user['unionid']) {
				$errcode = -7;
				$errmsg = '无法获取微信用户授权信息';
			}
		}

		if (!$errcode) {
			// 上面未发生任何错误，则认为已经获取到微信用户的unionid

			$timestamp = startup_env::get('timestamp');

			$errcode = 0;
			$errmsg = '';
			$result = array(
				'unionid' => $wechat_user['unionid']
			);
		}

		// 通过get传递unionid到oa企业站，其余动作均在企业站进行操作
		/**
		 * 通过传递到企业站的unionid来查找具体的企业站内所绑定的用户：
		 * 1.如果已绑定过，则自动登录到企业站
		 * 2.如果未绑定过，则显示普通的登录界面，进行登录，登录成功后自动绑定unionid
		 */
		$this->login_redirect($domain, $action, $errcode, $errmsg, $result);

		return true;
	}

}
