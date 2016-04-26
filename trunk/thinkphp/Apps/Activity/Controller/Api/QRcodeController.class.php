<?php
/**
 * 活动报名 二维码
 * User: Muzhitao
 * Date: 2015/10/12 0030
 * Time: 09:22
 * Email：muzhitao@vchangyi.com
 */

namespace Activity\Controller\Api;

class QRcodeController extends AbstractController {

	/**
	 * 生成二维码
	 */
	public function Create_qrcode() {

		$id = I('get.id', '', 'intval'); // ID
		$url_prefix = I('get.url', '', 'htmlspecialchars'); // url地址前缀
		$status = I('get.status', '0', 'intval'); // 当前的状态

		// 如果参数为空，参数错误
		if (empty($id) || empty($url_prefix)) {
			$this->_set_error('_ERROR_PARAMETER');
			return false;
		}

		$serv_a = D('Activity/Activity', 'Service');

		// url地址组装
		$url = $url_prefix.'?id='.$id.'&status='.$status;

		// 生成二维码
		$serv_a->get_qrcode($url);
		exit();
	}

	/**
	 * 二维码扫描
	 * @return bool
	 */
	public function Scan_qrcode() {

		$id = I('get.id', '', 'intval');
		$status = I('get.status', '', 'intval');

		// 如果当前状态是1 则是内部人员报名信息,否则是外部人员
		if ($status == 1) {
			$serv_p = D('Activity/ActivityPartake', 'Service');
			// 如果签到错误
			if (!$serv_p->scan($id)) {
				return false;
			}
		} else {
			$serv_o = D('Activity/ActivityOutsider', 'Service');
			// 如果签到错误
			if (!$serv_o->scan($id)) {
				return false;
			}
		}

		return true;
	}
}
