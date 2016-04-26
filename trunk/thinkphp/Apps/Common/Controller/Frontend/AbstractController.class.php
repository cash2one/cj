<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Common\Controller\Frontend;
use Think\Controller;
use Com\Cookie;
use Common\Common\Login;
use Common\Common\Cache;

abstract class AbstractController extends Controller {

	// 是否必须登录
	protected $_require_login = true;
	// cookie
	protected $_cookie = null;
	// user
	protected $_login = null;
	// 站点配置
	protected $_setting = array();
	// 插件信息
	protected $_plugin = array();
	// 获取外部人员openid
	protected $_exterior_openid = false;

	// 前置操作
	public function before_action($action = '') {

		try {
			// 先读取数据库配置
			cfg(load_dbconfig(get_sitedir() . 'dbconf.inc.php'));
			// 读取全局缓存
			$cache = &Cache::instance();
			$this->_setting = $cache->get('Common.setting');
			// cookie
			$this->_start_cookie();
			// 检查是否登陆
			$this->_is_login();
			// 读取插件信息
			$this->_get_plugin();
		} catch (\Think\Exception $e) {
			$this->_error_message($e->getMessage());
			return false;
		} catch (\Exception $e) {
			$this->_error_message('_ERR_DEFAULT');
			return false;
		}

		return true;
	}

	// 后置操作
	public function after_action($action = '') {

		return true;
	}

	// 获取插件配置
	protected function _get_plugin() {

		return false;
	}

	// 初始化 cookie
	protected function _start_cookie() {

		$domain = cfg('COOKIE_DOMAIN');
		$expired = cfg('COOKIE_EXPIRE');
		$secret = empty($this->_setting['authkey']) ? cfg('COOKIE_SECRET') : $this->_setting['authkey'];
		// 初始化
		$this->_cookie = &Cookie::instance($domain, $expired, $secret);
		ob_start(array($this->_cookie, 'send'));
	}

	// 判断是否登陆
	public function _is_login() {

		// 用户信息初始化
		$this->_login = &Login::instance();
		$this->_login->init_user();
		// 如果用户信息为空
		$need_auth = false;
		if (empty($this->_login->user)) {
			$this->_login->auto_login($need_auth, $this->_require_login);
		} else { // 有 code 就转向剔除后的 URL
			$code = I('get.code');
			if (!empty($code)) {
				$boardurl = preg_replace('/\&?code\=(\w+)/i', '', boardurl());
				redirect($boardurl);
				return true;
			}
		}

		// 如果需要转向授权地址
		if ($need_auth) {
			$os = I('get._os', '', 'trim');
			$top = I('get._top', '', 'trim');
			$this->assign('os', $os);
			$this->assign('top', $top);
			$this->assign('redirectUrl', $this->_login->get_wxqy_auth_url());
			$this->_output('Common@Frontend/Redirect');
			return false;
		}

		// 如果需要强制登录或者只允许内部
		if ($this->_require_login && empty($this->_login->user) && !$this->_exterior_openid) {
			$this->_cookie->destroy();
			$this->_error_message(L('PLEASE_RSYNC_MEMBER'), null, null, L('PLEASE_RSYNC_MEMBER_TITLE'));
			return true;
		}

		return true;
	}

	/**
	 * 输出json数据字串
	 * @param string $data 返回数据
	 * @param string $type 数据类型
	 * @param number $code 返回状态
	 */
	protected function _output_json($data = null, $type = 'json', $code = 200) {

		parent::_response(generate_api_result($data), $type, $code);
	}

	/**
	 * output
	 * 输出模板
	 *
	 * @param string $tpl 引入的模板
	 * @return unknown
	 */
	protected function _output($tpl) {

		// 域名信息
		$this->view->assign('domain', $this->_setting['domain']);
		// 输出当前用户信息
		$this->view->assign('wbs_user', $this->_login->user);
		if (!empty($this->_login->user)) {
			$this->view->assign('wbs_uid', $this->_login->user['m_uid']);
			$this->view->assign('wbs_username', $this->_login->user['m_username']);
		} else {
			$this->view->assign('wbs_uid', 0);
			$this->view->assign('wbs_username', '');
		}

		// 输出 forumHash
		parent::_output($tpl);
		return true;
	}

	// 生成 formhash
	protected function _generate_formhash() {

		// 拼凑源字串
		$fh_key = I('server.HTTP_HOST').cfg('formhash_secret').$this->_setting['formhash_key'];
		if (!empty($this->_login->user)) {
			$fh_key .= $this->_login->user['m_uid'].$this->_login->user['m_username'];
		}

		// 生成 hash
		$formhash = &\Com\Formhash::instance();
		$hash = '';
		$formhash->generate($hash, $fh_key);
		return $hash;
	}

}
