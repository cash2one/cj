<?php
/**
 * AbstractService.class.php
 * $author$
 */

namespace Activity\Service;
use Common\Common\Wxqy\Service;
use Common\Common\User;
use Com\QRcode;

class AbstractService extends \Common\Service\AbstractService {

	const IS_CHECK = 1; // 已经签到
	const NO_CHECK = 0; // 未签到
	const NO_APPLY = 1; // 用户未申请退出
	const END_APPLY = 3; // 已经处理过
	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 生成二维码
	 * @param $id 主键ID
	 * @param string $url_prefix url前缀
	 * @param string $file
	 * @param bool $is_download
	 */
	public function qrcode($url, $file = '', $is_download = false) {

		$cache = &\Common\Common\Cache::instance();
		$sets = $cache->get('Common.setting');
		//$url = cfg('PROTOCAL') . $sets ['domain'] ."/Activity/Api/Qrcode/Scan_qrcode?regid=" . $id;
		//$url = $url_prefix . '?regid=' . $id;
		// 纠错级别：L、M、Q、H
		$errorCorrectionLevel = 'L';
		// 点的大小：1到10
		$matrixPointSize = 10;
		$qrcode = QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);

		if ($file) {
			// 生成文件
			imagepng($qrcode, $file);
		} else {
			// 直接输出图片
			header('Content-Type: image/png');
			imagepng($qrcode);
		}
	}

	/**
	 * 发送微信消息
	 * @param $data
	 * @param $to_users
	 * @param $to_partys
	 */
	public function send_msg($data, $to_users, $to_partys = array()) {

		if (!is_array($to_users)) {
			return false;
		}

		$openids = array();
		foreach($to_users as $_v) {
			$user = &User::instance()->get($_v);
			$openids[] = $user['m_openid'];
		}

		// 获取应用id
		$pluginid = $this->get_pluginid('activity');

		$post = &Service::instance();
		// 发送消息
		$post->post_news($data, 0, $openids, $to_partys);
	}

	/**
	 * 获取应用ID
	 * @param string $cp_pluginid
	 * @return int
	 */
	public function get_pluginid($cp_pluginid = "activity") {

		$pluginid = 0;
		$cache = &\Common\Common\Cache::instance();
		$plugins = $cache->get('Common.plugin');
		foreach($plugins as $_v) {
			if ($_v['cp_identifier'] == $cp_pluginid) {
				$pluginid = $_v['cp_pluginid'];
				break;
			}
		}

		return $pluginid;
	}

	/**
	 * 详情URL地址
	 * @param $ne_id
	 * @return string
	 */
	public function view_url($acid) {

		$cache = &\Common\Common\Cache::instance();
		$sets = $cache->get('Common.setting');
		$face_base_url = cfg('PROTOCAL') . $sets ['domain'];

		$url = $face_base_url. '/frontend/activity/view/?acid=' . $acid ;

		return $url;
	}
}
