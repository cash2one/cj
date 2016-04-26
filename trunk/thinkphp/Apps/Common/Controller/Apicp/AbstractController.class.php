<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Common\Controller\Apicp;
use Think\Controller\RestController;
use Com\Cookie;
use Common\Common\Logincp;
use Common\Common\Cache;

abstract class AbstractController extends RestController {

	// 是否必须登录
	protected $_require_login = false;
	// cookie
	protected $_cookie = null;
	// user
	protected $_login = null;
	// 站点配置
	protected $_setting = array();
	// 插件信息
	protected $_plugin = array();
	// 返回结果
	protected $_result = array();

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
			// 读取插件信息
			$this->_get_plugin();
			// 检查是否登陆
			$this->_is_login();
		} catch (\Think\Exception $e) {
			$this->_response($e);
		} catch (\Exception $e) {
			$this->_response(L('_ERR_DEFAULT'));
		}

		return true;
	}

	// 后置操作
	public function after_action($action = '') {

		$this->_response();
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
		$this->_login = &Logincp::instance();
		$this->_login->set_prekey(get_sl_domain() . '_');
		$this->_login->init_user();

		// 如果需要强制登录
		if ($this->_require_login && empty($this->_login->user)) {
			$this->_cookie->destroy();
			$this->_set_error('PLEASE_LOGIN');
			$this->_response();
			return false;
		}

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

	/**
	 * 设置错误信息
	 * @param mixed $message 错误信息
	 * @param int $code 错误号
	 */
	protected function _set_error($message, $code = 0) {

		\Com\Error::instance()->set_error($message, $code);
		return true;
	}

	/**
	 * 重写输出方法
	 * @param mixed $data 输出数据
	 * @param string $type 输出类型
	 * @param int $code 返回状态
	 * @see \Think\Controller\RestController::_response()
	 */
	protected function _response($data = null, $type = 'json', $code = 200) {

		// 如果需要返回的是异常
		if ($data instanceof \Think\Exception) {
			// 如果是显示给用户的错误
			if ($data->is_show()) {
				\Com\Error::instance()->set_error($data);
			} else { // 如果是系统错误, 则显示默认错误
				$this->_set_error('_ERR_DEFAULT');
			}

			$data = '';
		} elseif ($data instanceof \Exception) { // 系统报错
			$data = '';
			$this->_set_error('_ERR_DEFAULT');
		}

		// 输出结果
		parent::_response(generate_api_result(null == $data ? $this->_result : $data), $type, $code);
	}

	/**
	 * 魔术方法 有不存在的操作的时候执行(重写)
	 *
	 * @access public
	 * @param string $method 方法名
	 * @param array $args 参数
	 * @return mixed
	 */
	public function __call($method, $args) {

		try {
			if (0 === strcasecmp($method, ACTION_NAME . C('ACTION_SUFFIX'))) {
				if (method_exists($this, $method . '_' . $this->_method . '_' . $this->_type)) { // RESTFul方法支持
					$fun = $method . '_' . $this->_method . '_' . $this->_type;
					$this->$fun();
				} elseif (method_exists($this, $method . '_' . $this->_method)) {
					$fun = $method . '_' . $this->_method;
					$this->$fun();
				} elseif (method_exists($this, '_empty')) {
					// 如果定义了_empty操作 则调用
					$this->_empty($method, $args);
				} else {
					E(L('_ERROR_ACTION_') . ':' . ACTION_NAME);
				}
			}
		} catch (\Think\Exception $e) {
			// 记录日志
			\Think\Log::record($e->getMessage());
			// 返回错误
			$this->_response($e);
		} catch (\Exception $e) {
			// 记录日志
			\Think\Log::record($e->getMessage());
			// 返回错误
			$this->_response($e);
		}
	}

	// 空方法, 在未找到处理方法时调用
	protected function _empty() {

		E('_ERROR_ACTION_');
	}

}
