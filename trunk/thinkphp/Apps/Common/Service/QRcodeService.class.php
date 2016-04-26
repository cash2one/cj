<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午12:16
 */
namespace Common\Service;
use Com\QRcode;

class QRcodeService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 生成二维码
	 * @param $url string 二维码地址
	 */
	public function get_qrcode($url) {

		$cache = &\Common\Common\Cache::instance();
		$sets = $cache->get('Common.setting');

		// 纠错级别：L、M、Q、H
		$errorCorrectionLevel = 'L';
		// 点的大小：1到10
		$matrixPointSize = 10;
		$qrcode = QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);

		// 直接输出图片
		header('Content-Type: image/png');
		imagepng($qrcode);
	}
}
