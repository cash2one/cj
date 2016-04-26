<?php
/**
 * voa_c_uc_wechat_login
 * 微信web登录
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_wechat_login extends voa_c_uc_wechat_base {

	public function execute() {

		$enumber = $this->request->get('enumber');
		$enumber = (string)$enumber;

		if (!$enumber) {
			// 如果未提供企业号，则显示输入界面，要求输入企业号
			echo '请提供企业号';
			exit;
		}

		// 回调页面
		$redirect_uri = $this->uc_url('wechat', 'callback', '', array('enumber' => $enumber));
		$redirect_uri = urlencode($redirect_uri);

		// 引入微信开放平台登录类
		$wechat = new voa_wechat_login();
		// 跳转到微信获取code
		$wechat_url = $wechat->get_code_from_qrcode_url($redirect_uri);

		echo $wechat_url;
		exit;
		@header("Location: {$wechat_url}");
		exit;
	}

}
