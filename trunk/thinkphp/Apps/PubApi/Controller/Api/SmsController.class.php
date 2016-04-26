<?php
/**
 * SmsController.class.php
 * $author$
 */
namespace PubApi\Controller\Api;

class SmsController extends AbstractController {

	// 发送短消息
	public function Smscode_get() {

		$mobile = I('get.mobile');
		$formhash = I('get.formhash');
		$seccode = I('get.seccode');
		$serv_sms = D('PubApi/Sms', 'Service');
		if (!$serv_sms->send_smscode($mobile, $formhash, $seccode)) {
			E($serv_sms->get_errcode() . ':' . $serv_sms->get_message());
			return false;
		}

		return true;
	}

}
