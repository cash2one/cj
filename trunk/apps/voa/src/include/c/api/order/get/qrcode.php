<?php
/**
 * 我的二维码
 * /api/order/get/qrcode
 * $Author$	linshiling
 * $Id$
 */
class voa_c_api_order_get_qrcode extends voa_c_api_order_abstract {

	public function execute() {

		try {
			//获取映射id
			$id = $this->_member['m_uid'];
			$qrcode = new voa_d_oa_travel_qrcode();
			$code = $qrcode->get($id);
			if(!$code) {
				return $this->_set_errcode('获取映射code失败');
			}
			
			//获取微信二维码地址
			$wx_service = voa_weixin_service::instance();
	
			/** 获取二维码 ticket */
			$qrcode_url = '';
			if (!$wx_service->get_qrcode($qrcode_url, $code['code_id'])) {
				$this->_error_message('refresh_page');
			}
			if(!$qrcode_url) {
				return $this->_set_errcode('获取二维码图片地址失败');
			}
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}

		$this->_result = array('qrcode_url' => $qrcode_url);

		return true;
	}
}
