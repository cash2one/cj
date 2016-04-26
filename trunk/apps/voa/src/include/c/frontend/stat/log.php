<?php
/**
 * 记录微信应用访问信息
 * User: luckwang
 * Date: 2/7/15
 * Time: 19:29
 */

class voa_c_frontend_stat_log extends voa_c_frontend_stat_base {

	public function execute() {

		$plugin = $this->request->get('plugin');
		$referer = $this->request->get('referer');

		$plugin = authcode($plugin, config::get('voa.auth_key'), 'DECODE');
		$referer = authcode($referer, config::get('voa.auth_key'), 'DECODE');

		if (empty($plugin) || empty($referer)) {
			return true;
		}
		//判断来源是否为空
		if ($referer == 'referer_empty') {
			$referer = '';
		}

		$user_agent = (string)$this->request->server('HTTP_USER_AGENT');
		$wechat = '';
		$nettype = '';
		$language = '';
		//微信版本
		if (preg_match('/MicroMessenger\/([^ ]*)/', $user_agent, $matches)) {
			$wechat = $matches[1];
		}
		//网络环境
		if (preg_match('/NetType\/([^ ]*)/', $user_agent, $matches)) {
			$nettype = $matches[1];
		}
		//语言环境
		if (preg_match('/Language\/([^ ]*)/', $user_agent, $matches)) {
			$language = $matches[1];
		}
		$data = array(
			'time' => startup_env::get('timestamp'),
			'ip' => controller_request::get_instance()->get_client_ip(),
			'wechat' => $wechat,
			'ep_id' => $this->_setting['ep_id'],
			'm_uid' => startup_env::get('wbs_uid'),
			'plugin' => $plugin,
			'domain' => $this->_setting['domain'],
			'appinfirst' => !empty($referer) ? 0 : 1,
			'appin' => '0',
			'url' => $this->request->server('HTTP_REFERER'),
			'user' => $user_agent,
			'referer' => $referer,
			'nettype' => $nettype,
			'language' => $language
		);
		logger::error(implode("\t", $data));

		$return = array();
		echo rjson_encode($return);
		exit;
	}

}
