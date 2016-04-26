<?php
/**
 * voa_c_wxwall_frontend_updateqrcode
 * 微信墙前端/展示:更新二维码
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_wxwall_frontend_updateqrcode extends voa_c_wxwall_frontend_base {

	public function execute() {

		/** 触发二维码是否更新 */
		$expire = $this->_current_wxwall['ww_qrcodeexpire'] - startup_env::get('timestamp');
		$qrcodeurl = voa_h_wxwall::make_qrcode($this->_current_ww_id);
		if ($expire < 60) {
			/** 进行触发更新 */
			$data = array(
					'hash' => mt_rand(0, startup_env::get('timestamp')),
					'expire' => 30,
					'qrcodeurl' => $qrcodeurl
			);
		} else {
			/** 使用缓存 */
			$data = array(
					'hash' => $this->_current_wxwall['ww_qrcodeexpire'],
					'expire' => $expire,
					'qrcodeurl' => $qrcodeurl
			);
		}
		$this->_ajax_return($data);
		exit;

	}

}
