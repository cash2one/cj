<?php
/**
 * 接口/新东方/ PC登录接口
 * User: luckwang
 * Date: 24/7/15
 * Time: 14:12
 */
class voa_c_frontend_xdf_pclogin extends voa_c_frontend_xdf_base {


	/**
	 * 回调url
	 * @var string
	 */
	protected $_callback;

	public function _before_action($action) {
		//非强制登录
		$this->_require_login = false;

		if (!parent::_before_action($action)) {
			return false;
		}

		$scheme = config::get(startup_env::get('app_name') . '.oa_http_scheme');
		$domain = $this->_setting['domain'];
		$this->_callback = $scheme . $domain . '/forum/forum.php?scode=';

		return true;
	}

	public function execute() {
		//未登录用户返回空的sig_code
		if (empty($this->_user)) {
			//echo $this->_callback;exit;
			//$this->redirect('http://www.baidu.com/');
			$this->redirect($this->_callback);

			return true;
		}
		//生成sig_code
		$scode = voa_h_login::code_create();

		//保存sig_code
		$ser_qrcode = &service::factory('voa_s_oa_common_signature');
		$m_uid = startup_env::get('wbs_uid');
		$result = $ser_qrcode->insert(array('sig_code' => $scode, 'sig_m_uid' => $m_uid, 'sig_login_status' => 1, 'sig_login_time' => startup_env::get('timestamp')));

		if (!$result) {
			return $this->_error_message("登录失败");
		}
		// 转向
		$this->redirect($this->_callback . $scode);
	}
}
