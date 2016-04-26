<?php
/**
 * LoginService.class.php
 * $author$
 */

namespace PubApi\Service;
use Common\Common\Login;
use Com\Cookie;
use Common\Common\Wxqy\Service;
use Common\Common\User;

class LoginService extends AbstractService {

	// 登录类
	protected $_login = null;
	// 传入参数
	protected $_params = array();

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_login = &Login::instance();
	}

	/**
	 * 判断是否允许外部人员
	 * @param array $gets
	 * @param string $out_mark
	 * @return boolean
	 */
	protected function _is_outer(&$result, &$gets) {

		// 如果外部信息已存在, 则
		$wx_openid = $this->_login->getcookie('wx_openid');
		if (empty($wx_openid)) {
			return false;
		}

		// 格式化外部用户
		$user = array();
		$this->format_outer($user);

		// 取jsapi授权签名相关
		$jscfg = array();
		$this->get_js_config($jscfg, $this->_params['url']);

		$result = array('user' => $user, 'jscfg' => $jscfg, 'offical_img' => (int)$offical_img);
		return true;
	}

	/**
	 * 检查登录信息
	 * @param array $result 接口返回信息
	 * @param array $params 传入参数(get)
	 * @return boolean
	 */
	public function check_login(&$result, $params) {

		$this->_params = $params;
		// 判断用户未登陆
		if (!empty($this->_login->user)) {
			// 设置输出的用户信息
			$user = array();
			$this->format_user($user);

			// 取jsapi授权签名相关
			$jscfg = array();
			$this->get_js_config($jscfg, $params['url']);

			$result = array('user' => $user, 'jscfg' => $jscfg, 'offical_img' => $user['offical_img']);
			return true;
		}

		$gets = array();
		// 如果允许内部人员访问, 并且已经读取到用户信息
		if ($this->_is_outer($result, $gets)) {
			return true;
		}

		// 判断重定向url参数
		$url = $params['url'];
		if (empty($url)) {
			E('_ERR_AUTH_URL_INVALID');
			return false;
		}

		// 生成微信企业号authcode地址
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
		$gets['_fronturl'] = $urls['scheme'] . '://' . $urls['host'] . $urls['path'] . '?' . $urls['query'];
		$result = array('authurl' => U('/PubApi/Frontend/Member/JsLogin', '', false) . '?' . http_build_query($gets));
		E('PLEASE_AUTH_WECHAT');
		return false;
	}

	// 设置输出用户信息
	public function format_user(&$user) {

		$user['uid'] = $this->_login->user['m_uid'];
		$user['username'] = $this->_login->user['m_username'];
		$user['mobilephone'] = $this->_login->user['m_mobilephone'];
		$user['email'] = $this->_login->user['m_email'];
		$user['weixin'] = $this->_login->user['m_weixin'];
		$user['openid'] = $this->_login->user['m_openid'];
		$user['gender'] = $this->_login->user['m_gender'];
		$user['active'] = $this->_login->user['m_active'];
		$user['qywxstatus'] = $this->_login->user['m_qywxstatus'];
		$user['face'] = User::instance()->avatar($this->_login->user['m_uid'], $this->_login->user);

		// 获取用户关联信息
		$this->_get_departments($user);

		return true;
	}

	// 格式化外部用户信息
	public function format_outer(&$user) {

		// 外部用户信息
		$user = array(
			'wx_openid' => $this->_login->getcookie('wx_openid'),
			'wx_deviceid' => $this->_login->getcookie('wx_deviceid')
		);

		return true;
	}

	// 获取微信 jsapi config
	public function get_js_config(&$jscfg, $url) {

		// 取jsapi授权签名相关
		$serv = &Service::instance();
		$jscfg = array();
		$serv->jsapi_signature($jscfg, $url);
		return true;
	}

	/**
	 * 获取用户关联部门信息
	 *
	 * @param $user 用户信息 引用
	 */
	protected function _get_departments(&$user) {

		// 获取用户关联部门
		$serv_memdp = D('Common/MemberDepartment');
		$departments = $serv_memdp->list_by_uid($user['uid']);
		$user['departments'] = array();
		// 如果部门信息为空
		if (!is_array($departments) || empty($departments)) {
			return true;
		}

		// 获取部门 职务缓存数据
		$cache = &\Common\Common\Cache::instance();
		$department_cache = $cache->get('Common.department');
		$positions_cache = $cache->get('Common.positions');

		// 遍历关联部门
		foreach ($departments as $_dp) {
			// 如果部门缓存中不存在
			if (empty($department_cache[$_dp['cd_id']])) {
				continue;
			}

			$temp = array('cd_name' => $department_cache[$_dp['cd_id']]['cd_name']);
			// 判断职务是否存在
			if (! empty($positions_cache[$_dp['mp_id']])) {
				$temp['position'] = $positions_cache[$_dp['mp_id']]['mp_name'];
			} else {
				$temp['position'] = '';
			}

			$user['departments'][] = $temp;
		}

		return true;
	}

}
