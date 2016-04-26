<?php
/**
 * camcard 类
 *
 */

class camcard {

	/**
	 * _instance
	 *
	 * @var object
	 */
	protected static $_instance = null;

	/** 用户名 */
	protected $_user = '';
	/** 密码 */
	protected $_passwd = '';
	/** 需要识别的语言 */
	protected $_lang = 0;

	/** 识别地址 */
	const BCR_URL = 'http://bcr2.intsig.net/BCRService/BCR_VCF2?PIN=%s&user=%s&pass=%s&lang=%s&size=%d';

	/**
	 * &get_instance
	 * 获取一个识别类的实例
	 *
	 * @return object
	 */
	public static function &get_instance() {

		if (!self::$_instance) {
			self::$_instance = new camcard();
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
		$this->_user = config::get($cfg_name.'.camcard.user');
		$this->_passwd = config::get($cfg_name.'.camcard.password');
		$this->_lang = config::get($cfg_name.'.camcard.lang');
	}

}
