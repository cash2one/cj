<?php
/**
 * MobileService.class.php
 * $author$
 */

namespace Common\Service;

class MobileService extends AbstractService {

	// sms 验证码
	protected $_smscode = '';
	// sms 验证码长度
	protected $_smscode_length = 0;
	// sms 签名
	protected $_signame = '';

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_signame = cfg('SMS_SIGNAME');
		$this->_smscode_length = cfg('SMSCODE_LENGTH');
	}

	/**
	 * 校验用户提交的手机短信验证码是否有效
	 * @param string $mobile 手机号
	 * @param string $smscode 用户提交的验证码文字
	 * @param number $expire 短信验证码有效期，注意与 send_smscode() 方法内的 $expire 值保持一致
	 * 如果不设置，则使用系统全局的设置  cfg('SMSCODE_EXPIRE')
	 * @return boolean
	 */
	public function verify_smscode($mobile, $smscode, $expire = 0) {

		// 检查手机号码合法性
		if (empty($mobile) || !\Com\Validator::is_mobile($mobile)) {
			$this->_set_error('_ERR_MOBILE_INVALID');
			return false;
		}

		// 尝试将全角中文数字转换为半角，增加用户体验
		$smscode = str_replace(
			array('０', '１', '２', '３', '４', '５', '６', '７', '８', '９'),
			array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
			$smscode
		);
		// 验证手机验证码的格式
		if (strlen($smscode) != $this->_smscode_length || !preg_match('/^\d+$/', $smscode)) {
			$this->_set_error('_ERR_SMSCODE_INVALID');
			return false;
		}

		// 找到该手机号的最后一次验证码
		$serv_smscode = D('Common/Smscode');
		$log = $serv_smscode->get_last_by_mobile($mobile);
		if (empty($log)) {
			$this->_set_error('_ERR_SMSCODE_NOT_EXIST');
			return false;
		}

		// 验证码有效期
		if (empty($expire)) {
			$expire = cfg('SMSCODE_EXPIRE');
		}

		// 判断是否过期
		if (NOW_TIME - $log['smscode_created'] > $expire) {
			$this->_set_error('_ERR_SMSCODE_TIMEOUT');
			return false;
		}

		// 检查手机验证码是否有效
		if ($log['smscode_code'] != $smscode) {
			$this->_set_error('_ERR_SMSCODE_ERROR');
			return false;
		}

		// 标记该验证码已被使用
		$serv_smscode->set_used($log['smscode_id']);
		return true;
	}

	/**
	 * 发送验证码短信
	 * @param string $mobile 接收验证码的手机号
	 * @param string $ip 用于非常规发送时的客户端IP信息，使用加密数据。正常请求可为空。
	 * @param string $msg 短信信息，其中使用%seccode%代表验证码文字，%expire%代表过期时间
	 * @param number $expire 短信验证码的有效期，单位：秒，不指定则使用则 smscode_expire 内的配置
	 * @return boolean
	 */
	public function send_smscode($mobile, $ip = '', $msg = '', $expire = 0) {

		// 整理待发送验证码短信
		if (!$this->_prepare_smscode($mobile, $ip, $msg, $expire)) {
			return false;
		}

		// 如果允许发送短信
		if (cfg('SMSCODE_SWITCH')) {
			// 发送短消息
			if (!$this->send_sms($mobile, $msg, $ip)) {
				return false;
			}

			// 短信发送完毕，写入验证码发送记录
			$serv_smscode = D('Common/Smscode');
			$serv_smscode->insert(array(
				'smscode_mobile' => $mobile,
				'smscode_code' => $this->_smscode,
				'smscode_ip' => $ip
			));
		}

		return true;
	}

	/**
	 * 整理验证码短信, 为发送做准备
	 * @param string $mobile 接收验证码的手机号
	 * @param string $ip 用于非常规发送时的客户端IP信息，使用加密数据。正常请求可为空。
	 * @param string $msg 短信信息，其中使用%seccode%代表验证码文字，%expire%代表过期时间
	 * @param number $expire 短信验证码的有效期，单位：秒，不指定则使用则 smscode_expire 内的配置
	 * @return boolean
	 */
	protected function _prepare_smscode(&$mobile, &$ip = '', &$msg = '', $expire = 0) {

		// 检查手机号码
		if (empty($mobile) || !\Com\Validator::is_mobile($mobile)) {
			$this->_set_error('_ERR_MOBILE_INVALID');
			return false;
		}

		// 当前 IP 地址
		if (empty($ip)) {
			$ip = get_client_ip();
		}

		// 两次发送短信验证码的间隔时间
		$frequency = cfg('SMSCODE_FREQUENCY');
		$serv_sms = D('Common/Sms');
		// 读取当前IP上次发送记录
		if ($last_sms = $serv_sms->get_last_by_ip($ip)) {
			// 检查当前 IP 地址请求是否频繁
			if ($last_sms['smscode_created'] + $frequency > NOW_TIME) {
				$this->_set_error('_ERR_IP_FREQUENTLY');
				return false;
			}
		}

		// 根据IP统计总数1天内的验证码数量
		$ts = NOW_TIME - 86400;
		if (cfg('SMS_IP_LIMIT_PERDAY') < $serv_sms->count_by_ip_timestamp($ip, $ts)) {
			$this->_set_error('_ERR_SMS_IP_MAX_PER_DAY');
			return false;
		}

		// 检查当前手机号请求是否频繁
		if ($last_sms = $serv_sms->get_last_by_mobile($mobile)) {
			if ($last_sms['smscode_created'] + $frequency > NOW_TIME) {
				$this->_set_error('_ERR_MOBILE_FREQUENTLY');
				return false;
			}
		}

		// 根据手机号码统计1小时内的发送量
		$ts = NOW_TIME - 3600;
		if (cfg('SMS_MT_LIMIT_PERHOUR') <= $serv_sms->count_by_mobile_timestamp($mobile, $ts)) {
			$this->_set_error('_ERR_SMS_MOBILE_MAX_PER_HOUR');
			return false;
		}

		// 根据手机号码统计1天内的发送量
		$ts = NOW_TIME - 86400;
		if (cfg('SMS_MT_LIMIT_PERDAY') <= $serv_sms->count_by_mobile_timestamp($mobile, $ts)) {
			$this->_set_error('_ERR_SMS_MOBILE_MAX_PER_DAY');
			return false;
		}

		// 生成验证码
		$this->_smscode = random($this->_smscode_length, true);
		// 如果有效期未指定, 则取默认值
		if (0 >= $expire) {
			$expire = cfg('SMSCODE_EXPIRE');
		}

		// 短信内容
		if (empty($msg)) {
			$msg = cfg('SMSCODE_DEFAULT_MSG');
		}

		// 格式化时间
		$sec_fmt = '';
		sec2dhis($sec_fmt, $expire, true);
		// 消息文本
		$msg = str_replace(array('%seccode%', '%expire%'), array($this->_smscode, $sec_fmt), $msg);

		return true;
	}

	/**
	 * 发送短信
	 * @param array $mobiles 手机号码
	 * @param string $msg 短信内容
	 * @param string $ip ip
	 * @return boolean
	 */
	public function send_sms($mobiles, $msg, $ip = '') {

		// 判断手机号码是否为空
		$mobiles = (array)$mobiles;
		if (empty($mobiles)) {
			$this->_set_error('_ERR_MOBILE_IS_EMPTY');
			return false;
		}

		// 短信内容
		$msg = (string)$msg;
		if (empty($msg)) {
			$this->_set_error('_ERR_SMS_MSG_IS_EMPTY');
			return false;
		}

		// ip
		$ip = empty($ip) ? get_client_ip() : $ip;

		// 发送状态
		$model_sms = D('Common/Sms');
		$status = $model_sms->get_st_create();
		try {
			// 发送短消息
			$result = '';
			if (\Com\Sms\Chuanglan::instance()->send($result, $mobiles, $msg)) {
				$status = $model_sms->get_st_update();
			} else {
				$this->_set_error('_ERR_SMS_SEND_ERROR');
			}
		} catch (\Think\Exception $e) {
			$this->_set_error($e->getCode() . ':' . $e->getMessage());
		}

		// 入库操作
		$smsdata = array();
		foreach ($mobiles as $_m) {
			$smsdata[] = array(
				'sms_mobile' => $_m,
				'sms_message' => $msg,
				'sms_ip' => $ip,
				'sms_status' => $status
			);
		}

		$serv_sms = D('Common/Sms');
		$serv_sms->insert_all($smsdata);

		return $model_sms->get_st_create() == $status ? false : true;
	}

}
