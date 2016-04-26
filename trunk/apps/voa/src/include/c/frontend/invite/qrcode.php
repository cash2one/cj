<?php
/**
 * voa_c_frontend_invite_qrcode
 * 生成二维码分享和链接分享
 * Created by zhoutao.
 * Created Time: 2015/7/8  16:59
 */

class voa_c_frontend_invite_qrcode extends voa_c_frontend_invite_base {

	// 分享链接
	private $__share_url = null;
	// 时间戳
	private $__timestamp = null;

	public function execute() {

		// 获取二维码生成的时间戳
		$this->__timestamp = $this->request->get('timestamp');
		$m_uid = $this->request->get('m_uid');

		// 生成分享链接
		$scheme = config::get('voa.oa_http_scheme');
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$this->__share_url = $scheme . $sets['domain'] . "/frontend/invite/introduction?timestamp=" . $this->__timestamp . "&m_uid=" . $m_uid;

		$qrdata = null;
		$qrcode = $this->qrcode($qrdata);

		$this->view->set('qrcode', $qrcode);
		return true;
	}

	public function qrcode($qdata, $file = '', $is_download = false) {
		//生成二维码
		include_once(ROOT_PATH . '/framework/lib/phpqrcode.php');

		//跳转地址
		$url = $this->__share_url;

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
