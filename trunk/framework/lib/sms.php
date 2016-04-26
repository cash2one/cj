<?php
/**
 * 短信消息服务
 *
 */

class sms {

	/**
	 * _instance
	 *
	 * @var object
	 */
	protected static $_instance = null;

	/** 用户名 */
	protected $_account = '';
	/** 密码 */
	protected $_passwd = '';

	/** 发送普通短信 */
	const SIMPLE_URL = 'http://www.jianzhou.sh.cn/JianzhouSMSWSServer/http/sendBatchMessage';

	/**
	 * &get_instance
	 * 获取一个短信发送类的实例
	 *
	 * @return object
	 */
	public static function &get_instance($account = null, $password = null) {

		if (!self::$_instance) {
			self::$_instance = new sms($account, $password);
		}

		return self::$_instance;
	}

	/**
	 * __construct
	 *
	 * @param  mixed $group
	 * @return void
	 */
	public function __construct($account = null, $password = null) {

		$this->_account = $account === null ? config::get(startup_env::get('cfg_name').'.sms.account') : $account;
		$this->_passwd = $password === null ? config::get(startup_env::get('cfg_name').'.sms.password') : $password;
	}

	/**
	 * 批量发送消息
	 * @param int $result 发送状态值
	 * @param array $mobiles 目标手机
	 * @param string $msg 消息
	 * @param string $timed 定时时间
	 */
	public function send_batch_message($result, $mobiles, $msg, $timed = '') {

		/** 如果发送的用户名和密码为空 */
		if (empty($this->_account) || empty($this->_passwd)) {
			logger::error('account_or_passwd_is_empty');
			throw new Exception('account_or_passwd_is_empty', 100);
			return false;
		}

		/** 如果接收手机号码为空 */
		$mobiles = (array)$mobiles;
		if (empty($mobiles)) {
			logger::error('mobile_is_empty');
			throw new Exception('mobile_is_empty', 100);
			return false;
		}

		/** 如果信息为空 */
		if (empty($msg)) {
			logger::error('msg_is_empty');
			throw new Exception('msg_is_empty', 100);
			return false;
		}

		/** 使用 snoopy 进行发送 */
		$snoopy = new snoopy();
		//$snoopy->rawheaders['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8';
		$data = array(
			'account' => $this->_account,
			'password' => $this->_passwd,
			'destmobile' => implode(';', $mobiles),
			'msgText' => $msg,
			'sendDateTime' => $timed
		);
		if (!$snoopy->submit(self::SIMPLE_URL, $data)) {
			logger::error('submit_error');
			throw new Exception('submit_error', 100);
			return false;
		}

		/** 获取结果 */
		$result = (int)$snoopy->results;
		if ($result < 0) {
			logger::error('sms_send_error:'.$result);
			throw new Exception('sms_send_error('.$result.')', 100);
			return false;
		}

		return true;
	}
}
