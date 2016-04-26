<?php
/**
 * Class voa_c_frontend_xdf_qrcodelogin
 * 扫码登录
 * @create-time: 2015-06-23
 * @author: huanw
 * @email: wanghuan@vchangyi.com
 */

class voa_c_frontend_xdf_qrcodelogin extends voa_c_frontend_xdf_base {

	public function execute() {

		//获取sure
		$sure = $this->request->get('sure');
		//获取code
		$scode = $this->request->get('scode');

		if (isset($sure) && $sure == 1) {
			//用户id
			$m_uid = startup_env::get('wbs_uid');

			//确认登录，更新记录状态
			$ser_qrcode = &service::factory('voa_s_oa_common_signature');
			$result = $ser_qrcode->update(array('sig_m_uid' => $m_uid, 'sig_login_status' => 1, 'sig_login_time' => startup_env::get('timestamp')), array('sig_code' => $scode));

			if ($result) {
				$this->_success_message('^_^ 服务很给力，登录成功');
			} else {
				$this->_error_message('>_< 登录失败,请联系客服人员');
			}
		}

		$this->view->set('code', $scode);
		$this->_output('xdf/sure');
	}
}
