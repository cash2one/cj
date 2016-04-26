<?php
/**
 * voa_uda_uc_smscode_insert
 * 统一数据访问/smscode
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_uc_smscode_insert extends voa_uda_uc_smscode_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 发送手机验证码短信
	 * @param string $mobilephone 接收验证码的手机号
	 * @param string $msg 短信信息，其中使用%seccode%代表验证码文字，%expire%代表过期时间
	 * @param number $set_expire_second 短信验证码的有效期，单位：秒，不指定则使用则 voa.smscode_send_expire 内的配置
	 * @param string $ipinfo 用于非常规发送时的客户端IP信息，使用加密数据。正常请求可为空。
	 * 使用该参数是避免某些环境调用时会将服务器IP认为是客户端IP地址
	 * <pre>
	 * $crypt_xxtea = new crypt_xxtea(config::get('voa.auth_key'));
	 * $ipinfo = startup_env::get('timestamp')."\t".controller_request::get_instance()->get_client_ip();
	 * $ipinfo = rbase64_encode($crypt_xxtea->encrypt($ipinfo));
	 * </pre>
	 * @return boolean
	 */
	public function send($mobilephone, $msg = '', $set_expire_second = 0, $ipinfo = null) {

		// 检查手机号码
		if (!$mobilephone || !validator::is_mobile($mobilephone)) {
			return $this->set_errmsg(voa_errcode_uc_mobileverify::UC_MV_MOBILE_ERROR, $mobilephone);
		}

		// 当前时间戳
		$timestamp = startup_env::get('timestamp');

		// 网络延迟偏移值
		$offset = 10;

		// 当前 IP 地址
		if (empty($ipinfo)) { // 未提供 IP 加密字符串，则使用当前的IP地址
			$ip = controller_request::get_instance()->get_client_ip();
		} else { // 使用 IP 加密字符串
			$ipinfo = rbase64_decode($ipinfo);
			$crypt_xxtea = new crypt_xxtea(config::get('voa.auth_key'));
			$ipinfo = explode("\t", (string)$crypt_xxtea->decrypt($ipinfo));
			if (!isset($ipinfo[1])) {
				return $this->set_errmsg(voa_errcode_uc_mobileverify::UC_MV_IP_CRYPT_ERROR);
			}
			if (!validator::is_ip($ipinfo[1], '.')) {
				return $this->set_errmsg(voa_errcode_uc_mobileverify::UC_MV_IP_CRYPT_NONE);
			}
			if (!is_numeric($ipinfo[0])) {
				return $this->set_errmsg(voa_errcode_uc_mobileverify::UC_MV_IP_CRYPT_TIME);
			}
			if ($timestamp - $ipinfo[0] > $offset*2) {
				return $this->set_errmsg(voa_errcode_uc_mobileverify::UC_MV_IP_CRYPT_TIMEOUT);
			}
			$ip = $ipinfo[1];
		}

		// 两次发送短信验证码的间隔时间
		$smscode_send_frequency = config::get('voa.smscode_send_frequency');
		if ($smscode_send_frequency < $offset) {
			$smscode_send_frequency = $offset;
		}

		$serv_smscode = &service::factory('voa_s_uc_smscode');
		// 检查当前 IP 地址请求是否频繁
		$rand_frequency = mt_rand($smscode_send_frequency - $offset, $smscode_send_frequency);
		if ($timestamp - $serv_smscode->last_by_ip($ip) < $rand_frequency) {
			return $this->set_errmsg(voa_errcode_uc_mobileverify::UC_MV_IP_TIMES_SHORT);
		}

		// 检查当前手机号请求是否频繁
		$rand_frequency = mt_rand($smscode_send_frequency - $offset, $smscode_send_frequency);
		if ($timestamp - $serv_smscode->last_by_mobile($mobilephone) < $rand_frequency) {
			return $this->set_errmsg(voa_errcode_uc_mobileverify::UC_MV_MOBILE_TIMES_SHORT);
		}

		// 生成验证码
		$seccode = random(6, true);

		// 验证码有效期
		/**if (empty($set_expire_second)) {
			$expire = rgmdate($timestamp + config::get('voa.smscode_send_expire') - $offset, 'Y-m-d H:i');
		} else {
			$expire = rgmdate($timestamp + $set_expire_second - $offset, 'Y-m-d H:i');
		}*/
		/** zhuun begin, 取默认值. */
		if (empty($set_expire_second)) {
			$set_expire_second = config::get('voa.smscode_send_expire');
		}
		/** zhuxun end. */
		// 短信内容
		if (empty($msg)) {
			$msg = "【畅移信息】您的验证码是{$seccode}";
		} else {
			$msg = str_replace(array(
				'%seccode%', '%expire%'
			), array(
				$seccode, $this->_to_dhi($set_expire_second)
			), $msg);
		}
		$msg = "【畅移信息】您的验证码是{$seccode}";

		// 发送短信
		if (config::get('voa.smscode_send')) {
			$uda_sms = &uda::factory('voa_uda_uc_sms_insert');
			if (!$uda_sms->send($mobilephone, $msg, $ip)) {
				if ($uda_sms->errno) {
					$this->errcode = $uda_sms->errno;
					$this->errmsg = $uda_sms->error;
					$this->result = array();
				} else {
					$this->set_errmsg(voa_errcode_uc_mobileverify::UC_MV_SEND_SMSCODE_UNKNOW);
				}
				return false;
			}
		}

		// 短信发送完毕，写入验证码发送记录
		$serv_smscode->insert(array(
			'smscode_mobile' => $mobilephone,
			'smscode_code' => $seccode,
			'smscode_ip' => $ip
		));

		return true;
	}

	/**
	 * 转换时间显示格式
	 * @param int $second 秒
	 */
	protected function _to_dhi($second) {

		$d = floor($second / 86400);
		$h = floor(($second % 86400) / 3600);
		$i = floor(($second % 3600) / 60);
		$s = $second % 60;

		return (0 < $d ? "{$d}天" : "").(0 < $h ? "{$h}小时" : "").(0 < $i ? "{$i}分" : "").(0 < $s ? "{$s}秒" : "");
	}
}
