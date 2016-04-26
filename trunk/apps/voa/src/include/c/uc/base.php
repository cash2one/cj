<?php
/**
 * voa_c_uc_base
 * UC/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_base extends controller {

	protected $_module = '';
	protected $_action = '';

	/**
	 * UC储存用户登录信息的cookie名
	 * @var string
	 */
	protected $_uc_auth_cookie_name = 'vcyuc_auth';

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// 当前执行的模块名
		$this->_module = $this->route->_params['controller'];

		// 当前动作名
		$this->_action = $this->action_name;

		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 设置或者获取用户登录信息
	 * @param mixed $data 登录数据
	 * 如果为数组，则设置用户登录cookie并返回加密后的字符串
	 * 如果为字符串，则解密用户信息并返回数据
	 * @return string|array
	 */
	protected function _uc_auth($data = '') {
		$comma = "\t";
		$crypt_xxtea = new crypt_xxtea(config::get('voa.auth_key'));
		if (is_array($data)) {
			// 设置uc登录信息cookie，返回true
			$auth_data = array();
			$auth_data[] = isset($data['m_id']) ? (int)$data['m_id'] : 0;
			$auth_data[] = isset($data['m_password']) ? (string)$data['m_password'] : '';
			$auth_data[] = isset($data['time']) ? (int)$data['time'] : startup_env::get('timestamp');
			$cookie_data = rbase64_encode($crypt_xxtea->encrypt(implode($comma, $auth_data)));
			$this->session->set($this->_uc_auth_cookie_name, $cookie_data);
			return $cookie_data;
		} else {
			// 解析登录cookie，返回用户信息
			$auth_data = rbase64_decode($data);
			$auth_data = $crypt_xxtea->decrypt($auth_data);
			$auth_data = explode($comma, $auth_data);

			return array(
				'm_id' => isset($auth_data[0]) ? (int)$auth_data[0] : 0,
				'm_password' => isset($auth_data[1]) ? (int)$auth_data[1] : '',
				'time' => isset($auth_data[2]) ? (int)$auth_data[2] : 0
			);
		}
	}

	/**
	 * 清除uc登录cookie信息
	 * @return boolean
	 */
	protected function _uc_auth_clear() {
		$this->session->set($this->_uc_auth_cookie_name, '');
		return true;
	}

	/**
	 * 返回UC的路径
	 * @param string $module
	 * @param string $method
	 * @param string $action
	 * @param array $vars
	 * @return string
	 */
	public function uc_url($module = '', $method = '', $action = '', $vars = array()) {
		if (!$module && isset($this->_uc_module)) {
			$module = $this->_uc_module;
		}
		if (!$method && isset($this->_uc_method)) {
			$method = $this->_uc_method;
		}
		if (!$action && isset($this->_uc_action)) {
			$action = $this->_uc_action;
		}
		$url = config::get('voa.uc_url');
		$url .= $module.'/';
		if ($method) {
			$url .= $method.'/';
		}
		if ($action) {
			$url .= $action.'/';
		}
		if ($vars) {
			$url .= '?';
			foreach ($vars as $k => $v) {
				$url .= $k.'='.urlencode($v);
			}
		}

		return $url;
	}

	/**
	 * 通过跳转重定向来通知企业OA站点登录情况
	 * @param string $domian 站点域名
	 * @param string $action 动作类型 wechat|qq
	 * @param number $errcode 错误代码
	 * @param string $errmsg 错误信息
	 * @param unknown $result 结果
	 * @return boolean
	 */
	public function login_redirect($domian, $action, $errcode = 0, $errmsg = '', $result = array()) {

		$url = config::get('voa.oa_http_scheme').$domian.'/';
		$url .= 'member/'.$action.'/?data=';

		$data = array('errcode' => $errcode, 'errmsg' => $errmsg, 'result' => $result);
		$data = rjson_encode($data);
		$crypt_xxtea = new crypt_xxtea(config::get('voa.auth_key'));
		$data = rbase64_encode($crypt_xxtea->encrypt($data));

		$url .= urlencode($data);
		unset($data);

		@header("Location: {$url}");
		exit;
		return true;
	}

	/**
	 * 输出模板
	 * @param string $tpl
	 */
	public function output($tpl) {

		$this->view->set('module', $this->_module);
		$this->view->set('action', $this->_action);

		// 默认浏览器标题文字
		$this->view->set('navtitle', '');

		// 静态文件目录ur
		$this->view->set('imgdir', APP_STATIC_URL.'images/');
		$this->view->set('cssdir', APP_STATIC_URL.'css/');
		$this->view->set('jsdir', APP_STATIC_URL.'js/');
		$this->view->set('staticdir', APP_STATIC_URL);

		// 当前时间戳
		$this->view->set('timestamp', startup_env::get('timestamp'));

		// 输入当前实例
		$this->view->set('cinstance', $this);

		// 输出 forumHash
		$this->view->set('formhash', $this->_generate_form_hash());
		$this->view->render($tpl);

		return $this->response->stop();
	}

	/**
	 * 生成formhash
	 * @return string
	 */
	protected function _generate_form_hash() {
		$fh_key = $this->request->server('HTTP_HOST');
		return voa_h_form_hash::generate($fh_key);
	}

	/**
	 * 重写 _is_post 判断, 在 post 时, 判断 formhash 值
	 */
	protected function _is_post() {
		if (!$this->request->is_post()) {
			return false;
		}

		if (!voa_h_form_hash::check('', $this->request->post('formhash'))) {
			return false;
		}

		return true;
	}

	/**
	 * 显示错误提示信息
	 * @param string $msg 错误信息，定义形式可参看@see voa_c_uc_base::_msg_string()方法的定义
	 * @param string $url 待跳转的目标页面url
	 * @param array $data 待传递给目标页面的GET数据
	 */
	protected function _error_message($msg, $url = '', $data = array()) {

		// 解释提示信息
		$r = $this->_msg_string($msg);

		// 真实输出的提示信息
		$message = $r['errmsg'];
		if ($r['errcode']) {
			// 提示编码
			$message .= "[{$r['errcode']}]";
		}
		unset($r);

		if ($this->_is_ajax()) {
			// ajax 则返回json格式
			$result = array(
				'errcode' => $r['errcode'],
				'errmsg' => $message,
				'result' => array(
					'url' => $url,
					'data' => $data
				)
			);
			return $this->_json_message($result);
		} else {
			// 普通消息提示
			return $this->_web_message('error', $message, $url, $data);
		}
	}

	/**
	 * 显示成功操作的提示信息
	 * @param string $msg 成功操作的提示信息，定义形式可参看@see voa_c_uc_base::_msg_string()方法的定义
	 * @param string $url 待跳转的目标页面url
	 * @param array $data 待传递给目标页面的GET数据
	 */
	protected function _success_message($msg = '', $url = '', $data = array()) {
		// 解释提示信息
		$r = $this->_msg_string($msg);

		// 真实输出的提示信息
		$message = $r['errmsg'];
		if ($r['errcode']) {
			// 提示编码
			$message .= "[{$r['errcode']}]";
		}
		unset($r);

		if ($this->_is_ajax()) {
			// ajax 则返回json格式
			$result = array(
				'errcode' => $r['errcode'],
				'errmsg' => $message,
				'result' => array(
					'url' => $url,
					'data' => $data
				)
			);
			return $this->_json_message($result);
		} else {
			// 普通消息提示
			return $this->_web_message('success', $message, $url, $data);
		}
	}

	/**
	 * 输出普通网页格式的提示信息，一般不直接使用
	 * @param string $msg_type 消息类型：error=错误提示、success=成功提示
	 * @param string $message 提示消息文本内容
	 * @param string $url 跳转页面的url
	 * @param array $data 传递给跳转页面的数据
	 */
	protected function _web_message($msg_type, $message, $url = '', $data = array()) {
		if (!empty($data)) {
			// 存在待传递的数据
			$data_string = http_build_query($data);
			if (strpos($url, '?') === false) {
				$url .= '?';
			} else {
				$url .= substr($url, -1) != '?' ? '&' : '';
			}
			$url .= $data_string;
		}
		$this->view->set('msg_type', $msg_type);
		$this->view->set('message', $message);
		$this->view->set('url', $url);
		if (!$message && $url) {
			// 无提示信息文字且存在跳转的url，则直接header跳转
			@header("Location: {$url}");
			exit;
		}
		$this->output('uc/message');
	}

	/**
	 * 输出json格式的提示信息，一般不直接使用
	 * @param mixed $result
	 */
	protected function _json_message($result = array()) {
		// header('Content-type: application/json');
		if (!isset($result['errcode'])) {
			$result['errcode'] = 0;
		}
		if (!isset($result['errmsg'])) {
			$result['errmsg'] = '';
		}
		if (!isset($result['result'])) {
			$result['result'] = $result;
		}
		$this->response->append_body(rjson_encode($result));
		$this->response->stop();
	}

	/**
	 * 判断是否为 ajax 请求
	 * @return boolean
	 */
	protected function _is_ajax() {
		return $this->request->get('inajax') ? true : false;
	}

	/**
	 * 转换错误信息为可读格式，一般不直接使用
	 * @param mixed $s
	 * 可以是直接来自错误代码库的常量如：voa_errcode_uc_member::MEMBER..
	 * 也可以是给定的字符串
	 * 也可以是给定的数组 array('', array(变量1， 变量2,....))
	 * @return array('errcode' => [int], 'errmsg' => [string])
	 */
	protected function _msg_string($s) {

		if (is_array($s)) {
			// 来自错误代码库定义的信息，且包含变量文字

			call_user_func_array("voa_h_func::set_errmsg", isset($s[1]) ? $s : array($s));
			$errcode = voa_h_func::$errcode;
			$errmsg = voa_h_func::$errmsg;
		} else {

			if (preg_match('/\s*(\d+)\s*\:/', $s)) {
				// 来自错误代码库定义的信息，但不包含错误信息变量文字

				return $this->_msg_string(array($s, array()));
			} else {
				// 自定义的纯文本信息

				$errcode = 0;
				$errmsg = $s;
			}
		}

		return array('errcode' => $errcode, 'errmsg' => $errmsg);
	}
}
