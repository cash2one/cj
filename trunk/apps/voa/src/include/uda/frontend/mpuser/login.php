<?php
/**
 * voa_uda_frontend_mpuser_login
 * 统一数据访问/公众号用户/根据 openid 登录操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_mpuser_login extends voa_uda_frontend_base {
	// session
	protected $_session = null;
	// service mpuser
	protected $_servm = null;
	// 登录方式
	protected $_type = 0;
	// 以 openid 登录
	const TYPE_OPENID = 0;
	// 以 cookie 登录
	const TYPE_COOKIE = 1;

	public function __construct() {

		parent::__construct();
		$this->_servm = &service::factory('voa_s_oa_mpuser');
	}

	/**
	 * 输入参数
	 * @param array $in 输入参数
	 * @param array &$out 输出参数
	 * @return boolean
	 */
	public function execute($in, &$out) {

		$this->_params = $in;

		// 读取用户
		$member = array();
		switch ($this->_type) {
			case self::TYPE_OPENID:
				$this->_get_by_openid($member);
				break;
			case self::TYPE_COOKIE:
				$this->_get_by_cookie($member);
				break;
			default:
				$this->_get_by_openid($member);
				break;
		}

		// 如果用户不存在
		if (empty($member)) {
			return false;
		}

		// 如果登录超过 30 分钟, 则更新最后更新时间
		$lastview = $this->_session->get('lastview');
		if ($this->_session && (empty($lastview) || startup_env::get('timestamp') - $lastview > 1800)) {
			$this->_servm->update($member['mpuid']);
			$lastview = startup_env::get('timestamp');
		}

		// 登录 cookie 信息
		$out = array(
			'member' => $member,
			'cookie' => array(
				'uid' => $member['mpuid'],
				'auth' => voa_h_func::generate_auth($member['password'], $member['mpuid'], $lastview),
				'lastview' => $lastview
			)
		);

		// 如果 session 对象未传入
		if (empty($this->_session)) {
			return true;
		}

		// 写入 session
		foreach ($out['cookie'] as $_k => $_v) {
			$this->_session->set($_k, $_v);
		}

		return true;
	}

	// 通过 cookie 读取用户
	protected function _get_by_cookie(&$member) {

		$conds = array();
		$conds['mpuid'] = (int)$this->_session->get('uid');
		$member = $this->_servm->get_by_conds($conds);
		if ($this->_session->get('auth') != voa_h_func::generate_auth($member['password'], $member['mpuid'], $this->_session->get('lastview'))) {
			$member = array();
			return false;
		}

		return true;
	}

	// 通过 openid 读取用户
	protected function _get_by_openid(&$member) {

		$conds = array();
		$conds['openid'] = (string)$this->get('openid');
		// 读取用户信息
		$member = $this->_servm->get_by_conds($conds);
		// 用户不存在
		if (empty($member)) {
			logger::error('member is not exist.(openid:'.$openid.')');
			return false;
		}

		return true;
	}

	// 设置登录类型
	public function set_type($type) {

		$this->_type = $type;
		return true;
	}

	// 设置 session 类
	public function set_session($session) {

		$this->_session = $session;
		return true;
	}

}
