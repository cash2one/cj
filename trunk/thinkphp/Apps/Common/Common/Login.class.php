<?php
/**
 * Login.class.php
 * 用户操作
 * $Author$
 */

namespace Common\Common;
use Com\Cookie;
use Think\Log;
use Common\Common\Wxqy\Service;

class Login {

	// 用户信息
	public $user = array();
	// openid
	public $openid = false;
	// cookie 键值的前缀
	public $_prekey = '';

	// 实例化
	public static function &instance($options = array()) {

		static $instance;
		if(empty($instance)) {
			$instance = new self($options);
		}

		return $instance;
	}

	// 构造方法
	public function __construct($options = array()) {

		// 如果 cookie 键值存在
		if (!empty($options) && isset($options['prekey'])) {
			$this->_prekey = (string)$options['prekey'];
		}
	}

	/**
	 * 设置 cookie 键值前缀
	 * @param string $prekey 键值前缀
	 * @return boolean
	 */
	public function set_prekey($prekey) {

		$this->_prekey = $prekey;
		return true;
	}

	/**
	 * 获取cookie信息
	 * @param string $name cookie 的名称
	 * @return Ambigous <string, unknown>
	 */
	public function getcookie($name) {

		$cookie = &Cookie::instance();
		$val = $cookie->get($this->_prekey . $name);
		return empty($val) ? '' : $val;
	}

	public function setcookie($name, $value = '', $expire = null) {

		$cookie = &Cookie::instance();
		// 如果有效时长小于 0, 则删除
		if (null !== $expire && 0 > $expire) {
			return $cookie->remove($this->_prekey . $name);
		} else {
			return $cookie->set($this->_prekey . $name, $value, $expire);
		}
	}

	// 初始化用户信息
	public function init_user() {

		// 如果已经有用户信息了
		if (!empty($this->user)) {
			return true;
		}

		// 取 uid, auth, lastlogin
		$uid = $this->getcookie('uid');
		$auth = $this->getcookie('auth');
		$lastlogin = $this->getcookie('lastlogin');
		// 如果 Cookie 值为空或者登陆时长超过一天
		if (empty($uid) || empty($auth) || empty($lastlogin) || $lastlogin + 86400 < NOW_TIME) {
			return false;
		}

		// 判断用户信息并登录
		$serv = D('Common/Member', 'Service');
		$member = $serv->get($uid);
		if (empty($member)) {
			return false;
		}

		// 验证校验字串是否正确
		if ($auth != $this->_generate_auth($uid, $member['m_password'], $lastlogin)) {
			return false;
		}

		$this->user = $member;
		// 重新设置 Cookie 签名
		$this->flush_auth($uid, $member['m_password']);
		return true;
	}

	// 自动登录企业号
	public function auto_login(&$need_oauth, $require_login = true) {

		$need_oauth = false;
		// 如果用户已登录
		if (!empty($this->user)) {
			return true;
		}

		// 获取 code
		$code = I('get.code');
		// 如果 code 不为空
		if (!$require_login || !empty($code) || cfg('AUTO_LOGIN') || !preg_match("/vchangyi\.(net|com)$/i", I('server.HTTP_HOST'))) {
			return $this->_auto_login_qy();
		}

		// 需要跳转到微信企业号授权
		$need_oauth = true;
		return false;
	}

	// 跳转到微信企业号授权地址
	public function get_wxqy_auth_url() {

		$redirect_url = '';
		// URL 转向
		if (cfg('JS_LOGIN')) { // JS 前端登录
			$url = I('get._fronturl', '', 'trim');
			$url = urldecode($url);
			// 解析url
			$urls = parse_url($url);
			// 解析参数
			parse_str($urls['query'], $queries);
			// 剔除 code 参数
			unset($queries['code']);
			if (!empty($urls['fragment'])) {
				unset($queries['_fronthash']);
				$queries['_fronthash'] = urlencode($urls['fragment']);
				$urls['query'] = http_build_query($queries);
			}

			// 重新拼 url
			$redirect_url = $urls['scheme'] . '://' . $urls['host'] . $urls['path'] . '?' . $urls['query'];
		} else {
			// 解析 url
			$boardurl = boardurl();
			$parsed_url = parse_url($boardurl);
			$queries = array();
			// 解析参数
			if (isset($parsed_url['query'])) {
				parse_str($parsed_url['query'], $queries);
			}

			// 重新拼接URL
			$redirect_url = $parsed_url['scheme'].'://'.$parsed_url['host'].$parsed_url['path'].'?'.http_build_query($queries);
			if (!empty($parsed_url['fragment'])) {
				$redirect_url .= '#'.$parsed_url['fragment'];
			}
		}

		$serv = &Service::instance();
		$redirect_url = $serv->oauth_url_base($redirect_url);
		return $redirect_url;
	}

	// 自动以企业号身份登录
	protected function _auto_login_qy() {

		// 判断是否已经登录
		if (!empty($this->user)) {
			return true;
		}

		// 如果存在debug数据
		if (cfg('AUTO_LOGIN') && cfg('DEBUG_QY_OPENID')) {
			$this->openid = cfg('DEBUG_QY_OPENID');
		}

		$openid = $this->_get_qy_openid();
		if (empty($openid)) {
			Log::record('qy openid is empty.');
			return false;
		}

		// 读取用户信息
		$serv = D('Common/Member', 'Service');
		$user = $serv->get_by_openid($openid);
		if (empty($user)) {
			Log::record('member is not exist.(openid:'.$openid.')');
			return false;
		}

		// 用户信息
		$this->user = $user;
		// 重新设置 Cookie 签名
		$this->flush_auth($user['m_uid'], $user['m_password']);
		return true;
	}

	// 获取当前用户 openid
	protected function _get_qy_openid() {

		// 如果已经读取过 openid
		if (false !== $this->openid) {
			return $this->openid;
		}

		// 从网页接口获取 openid
		$serv = &Service::instance();
		if (!$serv->get_web_openid($this->openid) || empty($this->openid)) {
			$this->openid = '';
		}

		// 设备id推入cookie
		$wx_deviceid = (string)$serv->get_wx_deviceid();
		$this->setcookie('wx_deviceid', $wx_deviceid, empty($wx_deviceid) ? -1 : null);
		// 如果用户是外部的
		$wx_openid = (string)$serv->get_wx_openid();
		$this->setcookie('wx_openid', $wx_openid, empty($wx_openid) ? -1 : null);

		return $this->openid;
	}

	/**
	 * 生成验证字串
	 * @param int $uid 用户UID
	 * @param string $passwd 密码
	 * @param int $lastlogin 最后登录时间
	 * @return string
	 */
	protected function _generate_auth($uid, $passwd, $lastlogin) {

		return md5($passwd."\t".$uid."\t".$lastlogin);
	}

	/**
	 * 刷新校验字串
	 * @param int $uid 用户UID
	 * @param string $passwd 密码
	 * @return boolean
	 */
	public function flush_auth($uid, $passwd) {

		$this->setcookie('uid', $uid);
		$this->setcookie('lastlogin', NOW_TIME);
		$this->setcookie('auth', $this->_generate_auth($uid, $passwd, NOW_TIME));
		return true;
	}

}
