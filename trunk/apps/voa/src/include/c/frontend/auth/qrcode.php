<?php
/**
 * voa_c_frontend_auth_qrcode
 * auth认证获取二维码
 * Created by zhoutao.
 * Created Time: 2015/7/5  8:23
 */

class voa_c_frontend_auth_qrcode extends voa_c_frontend_auth_base {

	//不强制登录，允许外部访问
	protected function _before_action($action) {
		$this->_auto_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}

	public function execute () {

		$getx = $this->request->getx();

		// 生成二维码
		$this->__auth_qrcode($getx);

		return true;
	}


	/**
	 * 生成auth认证的二维码
	 * @param $data 二维码包含信息
	 * @param string $file
	 * @param bool $is_download
	 */
	private function __auth_qrcode ($data, $file = '', $is_download = false) {
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');

		//生成二维码
		include_once(ROOT_PATH . '/framework/lib/phpqrcode.php');
		//跳转地址
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme . $sets['domain'] . "/frontend/auth/checkin?authcode=" . $data['authcode'] . "&singture=" . $data['singture'] . "&timestamp=" . $data['timestamp'];
		// 纠错级别：L、M、Q、H
		$errorCorrectionLevel = 'L';
		// 点的大小：1到10
		$matrixPointSize = 10;
		$qrcode = QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);

		if ($file) {
			//生成文件
			imagepng($qrcode, $file);
		} else {
			//直接输出图片
			header('Content-Type: image/png');
			imagepng($qrcode);
		}
	}
}
