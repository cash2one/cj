<?php
/**
 * SmsModel.class.php
 * $author$
 */

namespace Common\Model;

class SmsModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'sms_';
	}

	/**
	 * 根据IP和时间戳统计总数
	 * @param string $ip IP 地址
	 * @param int $timestamp 时间戳
	 * @return Ambigous <multitype:, number, mixed>
	 */
	public function count_by_ip_timestamp($ip, $timestamp = 0) {

		return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE `sms_ip`=? AND `sms_created`>? AND `sms_status`<?", array(
			$ip, $timestamp, $this->get_st_delete()
		));
	}

	/**
	 * 根据IP和时间戳统计总数
	 * @param string $mobile 手机号码
	 * @param int $timestamp 时间戳
	 * @return Ambigous <multitype:, number, mixed>
	 */
	public function count_by_mobile_timestamp($mobile, $timestamp = 0) {

		return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE `sms_mobile`=? AND `sms_created`>? AND `sms_status`<?", array(
			$mobile, $timestamp, $this->get_st_delete()
		));
	}

	/**
	 * 根据 IP 读取最近发送记录
	 * @param string $ip IP 地址
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_last_by_ip($ip) {

		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `sms_ip`=? AND `sms_status`<? ORDER BY `sms_id` DESC LIMIT 1", array(
			$ip, $this->get_st_delete()
		));
	}

	/**
	 * 根据手机号码读取最近发送记录
	 * @param string $mobile 手机号码
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_last_by_mobile($mobile) {

		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `sms_mobile`=? AND `sms_status`<? ORDER BY `sms_id` DESC LIMIT 1", array(
			$mobile, $this->get_st_delete()
		));
	}
}
