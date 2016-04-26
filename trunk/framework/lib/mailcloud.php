<?php
/**
 * 邮件发送服务
 *
 */

class mailcloud {

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
	/** 发送人邮箱 */
	protected $_from = '';
	/** 发送人名字 */
	protected $_fromname = '';

	/** 发送普通短信 */
	const TPL_URL = 'https://sendcloud.sohu.com/webapi/mail.send_template.xml';

	/**
	 * &get_instance
	 * 获取一个短信发送类的实例
	 *
	 * @return object
	 */
	public static function &get_instance() {

		if (!self::$_instance) {
			self::$_instance = new mailcloud();
		}

		return self::$_instance;
	}

	/**
	 * __construct
	 *
	 * @param  mixed $group
	 * @return void
	 */
	public function __construct() {

		$cfg_name = startup_env::get('cfg_name');
		$this->_account = config::get($cfg_name.'.mailcloud.account');
		$this->_passwd = config::get($cfg_name.'.mailcloud.password');
		$this->_from = config::get($cfg_name.'.mailcloud.from');
		$this->_fromname = config::get($cfg_name.'.mailcloud.fromname');
	}

	/**
	 * 发送模板邮件
	 * @param string $tpl_name 模板名称
	 * @param array $mails 接收人邮箱地址
	 * @param string $subject 邮箱主题
	 * @param array $vars 模板邮件的变量值, 保持和接收人邮箱地址一致的顺序
	 */
	public function send_tpl_mail($tpl_name, $mails, $subject, $vars = array(), $from = '', $fromname = '') {

		foreach ($mails as $_email) {
			if (preg_match('/^\d+\@vchangyi\.com$/i', $_email)) {
				return false;
			}
		}

		$tpl_vars = array(
			'to' => $mails,
			'sub' => $vars
		);

		/** 判断发送人是否为空 */
		if (empty($from)) {
			$from = $this->_from;
		}

		if (empty($fromname)) {
			$fromname = $this->_fromname;
		}

		/** 发送参数 */
		$param = array(
			'api_user' => $this->_account,
			'api_key' => $this->_passwd,
			'from' => $from,
			'fromname' => $fromname,
			'template_invoke_name' => $tpl_name,
			'subject' => $subject,
			'substitution_vars' => json_encode($tpl_vars)
		);

		$options = array(
			'http' => array(
				'method'  => 'POST',
				'header' => "Connection: close\r\nContent-type: application/x-www-form-urlencoded",
				'content' => http_build_query($param)
			)
		);
		$context  = stream_context_create($options);
		$result = file_get_contents(self::TPL_URL, false, $context);

		/** 解析xml */
		$xml = (array)simplexml_load_string($result);
		/** 如果成功 */
		if (isset($xml['message']) && 'success' == trim($xml['message'])) {
			return true;
		}

		/** 记录日志 */
		logger::error(self::TPL_URL.'=>'.$result.'=>'.var_export($param, true));

		return false;
	}
}
