<?php
/**
 * StatLogService.class.php
 * $author$
 */

namespace Common\Service;

class StatLogService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/StatLog");
	}

	//
	public function record($request, $server) {

		$user_agent = (string)I('server.HTTP_USER_AGENT');
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
	}

}
