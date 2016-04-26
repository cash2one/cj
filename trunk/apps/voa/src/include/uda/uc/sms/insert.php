<?php
/**
 * voa_uda_uc_sms_insert
 * 统一数据访问/sms 短信发送操作/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_uda_uc_sms_insert extends voa_uda_uc_sms_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 发送短信
	 * @param array $mobiles 手机号码
	 * @param string $msg 短信内容
	 * @param string $ip ip
	 * @return boolean
	 */
	public function send($mobiles, $msg, $ip = '') {

		/** 判断手机号码是否为空 */
		$mobiles = (array)$mobiles;
		if (empty($mobiles) || 1 > count($mobiles)) {
			$this->errmsg(100, '手机号码不能为空');
			return false;
		}

		/** 短信内容 */
		$msg = (string)$msg;
		if (empty($msg)) {
			$this->errmsg(100, '短信内容不能为空');
			return false;
		}

		/** ip */
		$ip = empty($ip) ? controller_request::get_instance()->get_client_ip() : $ip;

		/** 发送状态 */
		$status = voa_d_uc_sms::STATUS_FAILED;
		try {
			/** 发送短消息 */
			$result = '';
			//sms::get_instance()->send_batch_message($result, $mobiles, $msg.$this->_signame);
			$result = $this->send_sms('52a503c7e1e4bf4f606c4eb19d9b12a0', $msg, $mobiles);
			$status = voa_d_uc_sms::STATUS_SENDED;
		} catch (Exception $e) {
			$this->errmsg(100, '短信发送失败');
			logger::error($e);
		}

		/** 入库操作 */
		$smsdata = array();
		foreach ($mobiles as $_m) {
			$smsdata[] = array(
				'sms_mobile' => $_m,
				'sms_message' => $msg,
				'sms_ip' => $ip,
				'sms_status' => $status
			);
		}

		$serv_sms = &service::factory('voa_s_uc_sms', array('pluginid' => startup_env::get('pluginid')));
		$serv_sms->insert_multi($smsdata);

		return voa_d_uc_sms::STATUS_FAILED == $status ? false : true;
	}

	/**
	 * 智能匹配模版接口发短信
	 * apikey 为云片分配的apikey
	 * text 为短信内容
	 * mobile 为接受短信的手机号
	 */
	public function send_sms($apikey, $text, $mobile) {

		$url = "http://yunpian.com/v1/sms/send.json";
		$encoded_text = urlencode("$text");
		$mobile = urlencode(implode(',', $mobile));
		$post_string = "apikey=$apikey&text=$encoded_text&mobile=$mobile";
		return $this->sock_post($url, $post_string);
	}

	/**
	 * url 为服务的url地址
	 * query 为请求串
	 */
	public function sock_post($url, $query) {

		$data = "";
		$info = parse_url($url);
		$fp = fsockopen($info["host"], 80, $errno, $errstr, 30);
		if (! $fp) {
			return $data;
		}

		$head = "POST " . $info['path'] . " HTTP/1.0\r\n";
		$head .= "Host: " . $info['host'] . "\r\n";
		$head .= "Referer: http://" . $info['host'] . $info['path'] . "\r\n";
		$head .= "Content-type: application/x-www-form-urlencoded\r\n";
		$head .= "Content-Length: " . strlen(trim($query)) . "\r\n";
		$head .= "\r\n";
		$head .= trim($query);
		$write = fputs($fp, $head);
		$header = "";
		while ($str = trim(fgets($fp, 4096))) {
			$header .= $str;
		}

		while (! feof($fp)) {
			$data .= fgets($fp, 4096);
		}

		return $data;
	}

}

