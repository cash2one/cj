<?php
/**
 * MemberService.class.php
 * $author$
 */

namespace PubApi\Service;
use Common\Common\Login;
use Com\Cookie;

class MemberService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	public function run($phone) {

		if (empty($phone)) {
			$this->_set_error('_ERR_LOGIN_PHONE_NOT_NULL');
		}

		// header 信息
		$headers = array();
		// 如果登录用户信息错误
		if (!$this->_get_api_header($headers, $phone)) {

			return false;
		}

		return $headers;
	}

	/**
	 * 获取 api header
	 * @param array $headers 头信息
	 * @param int $uid 用户uid
	 * @return boolean
	 */
	protected function _get_api_header(&$headers, $phone) {

		// 获取用户信息
		$serv_mem = D('Common/Member');
		if (!$member = $serv_mem->get_by_phone($phone)) {
			return false;
		}

		// 刷新登录信息
		$login = &Login::instance();
		$login->flush_auth($member['m_uid'], $member['m_password']);

		// 取 cookie 信息
		$cookie = &Cookie::instance();
		$cdata = $cookie->get_cookie_data();
		$login_cookies = array();
		foreach ($cdata as $_k => $_v) {
			$login_cookies[] = $_k . '=' . urlencode($_v['value']);
		}

		$headers['Cookie'] = implode('; ', $login_cookies);
		return true;
	}
}
