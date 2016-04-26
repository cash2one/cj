<?php
/**
 * 会议数据过滤
 * $Author$
 * $Id$
 */
class voa_uda_frontend_meeting_base extends voa_uda_frontend_base {
	/**
	 * 配置信息
	 */
	protected $_sets = array();
	/**
	 * 房间信息
	 */
	protected $_rooms = array();

	public function __construct() {

		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.meeting.setting', 'oa');
		$this->_rooms = voa_h_cache::get_instance()->get('plugin.meeting.room', 'oa');
	}

	/**
	 * 验证标题
	 *
	 * @param string $str
	 * @return boolean
	 */
	public function val_subject(&$str) {

		$str = trim($str);
		if (empty($str)) {
			$this->errmsg(100, 'subject_too_short');
			return false;
		}

		return true;
	}

	/**
	 * 验证内容
	 *
	 * @param string $str
	 * @return boolean
	 */
	public function val_message(&$str) {

		$str = trim($str);
		if (empty($str)) {
			$this->errmsg(101, 'message_too_short');
			return false;
		}

		return true;
	}

	/**
	 * 会议开始时间
	 *
	 * @param int $ts
	 * @return boolean
	 */
	public function val_begin_hm(&$ts) {

		$ts = (int)$ts;
		return true;
	}

	/**
	 * 会议结束时间
	 *
	 * @param int $ts
	 * @return boolean
	 */
	public function val_end_hm(&$ts) {

		$ts = (int)$ts;
		return true;
	}

	/**
	 * 会议室id
	 *
	 * @param int $id
	 * @return boolean
	 */
	public function val_mr_id(&$id) {

		$id = (int)$id;
		if (0 >= $id) {
			$this->errmsg(102, 'room_id_invalid');
			return false;
		}

		return true;
	}

	/**
	 * 验证参与人
	 *
	 * @param string $uidstr
	 * @param array $uids
	 * @return boolean
	 */
	public function val_join_uids($uidstr, &$uids) {

		$uidstr = (string)$uidstr;
		$uidstr = trim($uidstr);
		$tmps = empty($uidstr) ? array() : explode(',', $uidstr);
		$uids = array();
		foreach ($tmps as $uid) {
			$uid = (int)$uid;
			if (0 < $uid) {
				$uids[$uid] = $uid;
			}
		}

		if (empty($uids)) {
			$this->errmsg(103, '请选择参会人员');
			return false;
		}

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
		$url = $scheme."{$_SERVER['HTTP_HOST']}/frontend/meeting/scan?mr_id=".$id;

		// 纠错级别：L、M、Q、H
		$errorCorrectionLevel = 'L';
		// 点的大小：1到10
		$matrixPointSize = 10;
		$qrcode = QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);

		//获取会议室信息和二维码code
		$room = new voa_d_oa_meeting_room();
		$room = $room->fetch_by_id($id);
		$room['mr_timestart'] = substr($room['mr_timestart'], 0, 5);
		$room['mr_timeend'] = substr($room['mr_timeend'], 0, 5);

		//创建背景,并将二维码贴到左边
		$bk = imagecreate(750, 370);
		imagecolorallocate($bk, 255, 255, 255);
		imagecopy($bk, $qrcode, 0, 0, 0, 0, 430, 430);

		//设置字体颜色
		$black = imagecolorallocate($bk, 0, 0, 0);
		// 字体
		$font = ROOT_PATH . "/apps/voa/cyadmin_www/static/fonts/YaHei.ttf";
		// 写入文字
		imagettftext($bk, 30, 0, 360, 80, $black, $font, $room['mr_name']);
		imagettftext($bk, 24, 0, 360, 140, $black, $font, '容纳人数:' . $room['mr_galleryful']);
		imagettftext($bk, 24, 0, 360, 200, $black, $font, '开放时间:' . $room['mr_timestart'] . '-' . $room['mr_timeend']);
		imagettftext($bk, 24, 0, 360, 260, $black, $font, $room['mr_address']);

		if($file) {
			//生成文件
			imagepng($bk, $file);
		}else if($is_download){
			//直接下载
			Header("Content-type: application/octet-stream");
			Header("Accept-Ranges: bytes");
			Header("Accept-Length:6000");
			Header("Content-Disposition: attachment; filename={$room['mr_address']}-{$room['mr_name']}.png");
			imagepng($bk);
		}else{
			//直接输出图片
			header('Content-Type: image/png');
			imagepng($bk);
		}
	}
}
