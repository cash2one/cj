<?php
/**
 * Class voa_c_frontend_xdf_loginqrcode
 * 访问二维码url
 * @create-time: 2015-06-17
 * @author: huanw
 * @email: wanghuan@vchangyi.com
 */

class voa_c_frontend_xdf_loginqrcode extends voa_c_frontend_xdf_base {

	public function _before_action($action) {

		//不需要登录
		$this->_require_login = false;

		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		//获取code
		$scode = $this->request->get('scode');

		//生成一条登录记录
		$ser_qrcode = &service::factory('voa_s_oa_common_signature');
		$m_uid = startup_env::get('wbs_uid');
		$ser_qrcode->insert(array('sig_code' => $scode, 'sig_m_uid' => $m_uid));

		//获取二维码访问url
		$url = $this->qrcodelogin_url_base . "?scode=" . $scode;

		//根据url生成二维码
		voa_h_func::qrcode($url);
	}
}
