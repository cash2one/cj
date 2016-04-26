<?php
/**
 * voa_c_frontend_member_wechat
 * 微信登录、退出OA
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_frontend_member_wechat extends voa_c_frontend_member_base {

	protected $_data = array();

	protected $_action = '';

	/**
	 * _before_action
	 *
	 * @param mixed $action
	 * @access protected
	 * @return void
	 */
	protected function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {

		// 当前动作
		$this->_action = (string)$this->request->get('action');

		if ($this->_action == 'loginsubmit' && $this->_is_post()) {
			// 提交普通登录并绑定操作

			$this->_loginsubmit();

		} elseif ($this->_action == 'bind') {
			// 用户绑定微信

			$this->_response_bind();

		} else {
			// 响应来自微信回调信息 from : uc.vchangyi.com/uc/wechat/callback

			return $this->_response_wechat();
		}

	}

	/**
	 * 处理普通登录并绑定操作
	 * 接收post传递的account和password，以及unionid加密字符串
	 * 成功后直接跳转页面
	 */
	protected function _loginsubmit() {

		$account = $this->request->post('account');
		$password = $this->request->post('password');
		$pwd_original = false;
		$member = $this->_normal_login($account, $password, $pwd_original);
		if ($member) {
			// 登录验证成功
			// 写入关联绑定信息

			// 解析提交的 unionid
			$bind_data = $this->_get_bind_data();
			if (!$bind_data) {
				return false;
			}

			// 关联绑定
			if ($this->_bind($member['m_uid'], $bind_data['unionid'])) {
				// 绑定成功，跳转到首页
				$this->redirect('/pc');
				return true;
			} else {
				$this->_error_message('绑定操作失败，请返回重试');
				return false;
			}
		}

	}

	/**
	 * 绑定微信帐号unionid
	 * 接收get传递的unionid加密字符串
	 * 成功后直接跳转页面
	 */
	protected function _response_bind() {

		$member = $this->_user;

		if (empty($member)) {
			$this->_error_message('未登录');
			return false;
		}

		// 当前接收的数据
		$_data = (string)$this->request->get('data');
		$this->_data = $this->_parse_data($_data);

		if (!isset($this->_data['errcode'])) {
			$this->_error_message('系统未知错误');
			return false;
		}

		if ($this->_data['errcode']) {
			$this->_error_message($this->_data['errmsg']);
			return false;
		}

		// 微信用户unionid
		$unionid = $this->_data['result']['unionid'];

		// 关联绑定
		if ($this->_bind($member['m_uid'], $unionid)) {
			// 绑定成功，跳转到首页
			$this->redirect('/pc');
			return true;
		} else {
			$this->_error_message('绑定操作失败，请返回重试');
			return false;
		}
	}

	/**
	 * 处理响应来自微信回调的信息
	 * @return boolean
	 */
	protected function _response_wechat() {

		// 当前接收的数据
		$_data = (string)$this->request->get('data');
		$this->_data = $this->_parse_data($_data);

		if (!isset($this->_data['errcode'])) {
			$this->_error_message('系统未知错误');
			return false;
		}

		if ($this->_data['errcode']) {
			$this->_error_message($this->_data['errmsg']);
			return false;
		}

		// 微信用户unionid
		$unionid = $this->_data['result']['unionid'];

		// 找到的用户信息
		$member = array();
		$uda_member = &uda::factory('voa_uda_frontend_member_get');
		if ($uda_member->member_by_unionid($unionid)) {
			// 已绑定了用户，则直接写入cookie登录并跳转

			$skey = $this->_generate_skey($member['m_username'], $member['m_password']);
			$this->_set_user_env($member, $skey);

			$this->redirect('/pc');

			return true;
		} else {
			// 未绑定，则显示普通登录界面


			// 加密微信unionid便于进行传输
			$crypt_xxtea = new crypt_xxtea(config::get('voa.auth_key'));
			$bind_data = array('unionid' => $unionid, 'time' => startup_env::get('timestamp'));
			$bind_data = rjson_encode($bind_data);
			$bind_data = base64_encode($crypt_xxtea->encrypt($bind_data));
			// 输出给前端，以进行提交传输
			$this->view->set('bind_data', $bind_data);
			// 指定登录动作
			$this->view->set('from_action', '?action=loginsubmit');

			$this->_output('member/union_login');
		}
	}

	/**
	 * 将微信unionid与用户uid进行绑定关联
	 * @param number $uid
	 * @param string $unionid
	 * @return boolean
	 */
	protected function _bind($uid, $unionid) {

		$uda_member_update = &uda::factory('voa_uda_frontend_member_update');
		if (!$uda_member_update->bind_wechat($uid, $unionid)) {
			$this->_error_message($uda_member_update->errmsg);
			return false;
		}

		return true;
	}

	/**
	 * 获取绑定的帐号信息
	 * @return boolean|mixed
	 */
	protected function _get_bind_data() {

		// 解析提交的 unionid
		$bind_data = $this->request->get('bind_data');

		$crypt_xxtea = new crypt_xxtea(config::get('voa.auth_key'));
		$bind_data = @base64_decode($bind_data);
		$bind_data = $crypt_xxtea->decrypt($bind_data);
		$bind_data = json_decode($bind_data, true);

		if (!isset($bind_data['unionid']) || !isset($bind_data['time'])) {
			$this->_error_message('未知的微信帐号信息');
			return false;
		}

		if (startup_env::get('timestamp') - $bind_data['time'] > 3600) {
			$this->_error_message('微信帐号信息过期');
			return false;
		}

		return $bind_data;
	}

}
