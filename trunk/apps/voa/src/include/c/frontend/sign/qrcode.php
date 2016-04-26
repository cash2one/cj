<?php

/**
 * 二维码签到
 * $Author$
 * $Id$
 */
class voa_c_frontend_sign_qrcode extends voa_c_frontend_sign_base {

	public function execute() {
		/** 初始化微信服务 */
		$wx_service = voa_weixin_service::instance();

		/** 二维码标识生成 */
		$serv_qr = &service::factory('voa_s_oa_weixin_qrcode', array('pluginid' => 0));
		$wq_id = $serv_qr->insert(array(
			'm_uid' => $from_user['m_uid'],
			'm_username' => $from_user['m_username'],
			'wq_ip' => controller_request::get_instance()->get_client_ip()
		), true);

		/** 获取二维码 ticket */
		$qrcode_url = '';
		if (!$wx_service->get_qrcode($qrcode_url, $wq_id)) {
			$this->_error_message('refresh_page');
		}

		$this->_set_dept_job();
		$this->view->set('qrcode_url', $qrcode_url);
		$this->view->set('navtitle', '二维码');

		$this->_output('sign/qrcode');
	}
}
