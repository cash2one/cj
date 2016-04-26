<?php

/**
 * voa_uda_frontend_event_base
 * 统一数据访问/社群活动/基本控制
 * Create By gaosong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_event_base extends voa_uda_frontend_base {

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.event.setting', 'oa');
		//应用信息缓存
		$this->_plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
	}

	/**
	 *活动状态判断
	 * @param int start
	 * @param int end
	 *return string type
	 */
	protected function _check_type($start, $end) {

		$time = startup_env::get('timestamp');//当前时间
		$type = array();
		if ($start <= $time && $time <= $end) {
			$type[0] = '已开始'; //已开始
			$type[1] = 1; //已开始
		} elseif ($start > $time) {
			$type[0] = '未开始'; //未开始
			$type[1] = 2; //未开始
		} elseif ($end < $time) {
			$type[0] = '已结束'; //已结束
			$type[1] = 3; //已结束
		}
		return $type;
	}

	/**
	 * 处理外部人员字段,增加name的MD5值，和序列化数组
	 * @param $in
	 * @param $out
	 */
	public function outfiled($in, &$out) {
		foreach ($in as $k => &$v) {
			$v['md5name'] = md5(trim($v['name']));
		}
		$out = serialize($in);
		return true;
	}

	/**
	 * 生成二维码
	 *
	 * @param int $id
	 * @param string $file
	 * @param boolean $is_download
	 */
	public function qrcode($id, $file='', $is_download = false)
	{
		//生成二维码
		include_once(ROOT_PATH.'/framework/lib/phpqrcode.php');
		//跳转地址
		$scheme = config::get('voa.oa_http_scheme');

		// 扫描二维码地址
		$url = $scheme . $_SERVER['HTTP_HOST'] . '/previewh5/micro-community/index.html?_ts='.startup_env::get('timestamp').'/#/app/page/activity/activity-scene-scan?eid='.$id;
		// 纠错级别：L、M、Q、H
		$errorCorrectionLevel = 'L';
		// 点的大小：1到10
		$matrixPointSize = 10;
		$qrcode = QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);

		//创建背景,并将二维码贴到左边
		$bk = imagecreate(750, 370);
		imagecolorallocate($bk, 255, 255, 255);
		imagecopy($bk, $qrcode, 0, 0, 0, 0, 430, 430);

		if ($file) {
			// 生成文件
			imagepng($qrcode, $file);
		} else {
			// 直接输出图片
			header('Content-Type: image/png');
			imagepng($qrcode);
		}
	}

}
