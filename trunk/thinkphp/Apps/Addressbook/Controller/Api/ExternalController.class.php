<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/17
 * Time: 上午11:35
 */

namespace Addressbook\Controller\Api;

class ExternalController extends AbstractController {

	// 不强制登陆
	public function before_action() {

		$this->_require_login = false;
		return parent::before_action();
	}

	/**
	 * 人员名片
	 * @return bool
	 */
	public function Idcard_get() {

		$m_uid = I('get.m_uid');
		$uid = rbase64_decode($m_uid);
		$uid = authcode($uid, $this->_setting['authkey'], 'DECODE');

		// 判断提交是否为空
		if (empty($uid)) {
			E('_ERR_EMPTY_POST_UID');
			return false;
		}
		// 提交的 uid 不得为多个
		if (is_array($uid)) {
			E('_ERR_VIEW_UID_CAN_NOT_ARRAY');
			return false;
		}

		// 获取用户信息
		$serv_mem = D('Common/Member', 'Service');
		// 人员信息是否为空
		if (!$user_data = $serv_mem->get_by_conds(array('m_uid' => $uid))) {
			E('_ERR_EMPTY_USER_DATA');
			return false;
		}
		$this->_format_user_data($user_data);

		// 公司名称
		$user_data['sitename'] = $this->_setting['sitename'];

		// 获取二维码地址
		$qrcode = cfg('PROTOCAL') . $this->_setting['domain'] . '/Addressbook/Api/External/QRcode?m_uid=' . $m_uid;

		$this->_result = array(
			'user_data' => $user_data,
			'qrcode' => $qrcode,
		);

		return true;
	}

	/**
	 * 生成二维码接口
	 */
	public function QRcode_get() {

		$m_uid = I('get.m_uid');
		$uid = rbase64_decode($m_uid);
		$uid = authcode($uid, $this->_setting['authkey'], 'DECODE', '');

		if (empty($uid)) {
			E('_ERR_EMPTY_POST_UID');
			return false;
		}

		$serv_qrcode = D('Common/QRcode', 'Service');
		// 获取setting表缓存
		$url = $this->_setting['domain'];
		$app_name = $this->_setting['appname'];

		// 获取人员详情
		$serv_member = D('Common/Member', 'Service');
		$info = $serv_member->get($uid);
		// url地址组装
		$url = "BEGIN:VCARD
N:" . $info['m_username'] . "
ORG:" . $app_name . "
TEL;CELL:" . $info['m_mobilephone'] . "
EMAIL;WORK:" . $info['m_email'] . "
END:VCARD";
		// 生成二维码
		$serv_qrcode->get_qrcode($url);

		exit();
	}


}
