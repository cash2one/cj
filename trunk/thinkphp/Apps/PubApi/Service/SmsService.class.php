<?php
/**
 * SmsService.class.php
 * $author$
 */

namespace PubApi\Service;
use Com\Cookie;

class SmsService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 发送验证码
	 * @param string $mobile 手机号码
	 * @param string $formhash hash值
	 * @param string $seccode 验证码
	 * @return boolean
	 */
	public function send_smscode($mobile, $formhash, $seccode) {

		$url = cfg('UCENTER_RPC_HOST') . '/OaRpc/Rpc/mobile';
		$result = array();
		if (!\Com\Rpc::query($result, $url, 'send_smscode', $mobile, get_client_ip(), $formhash, $seccode)) {
			return false;
		}

		return true;
	}

}
