<?php
/**
 * controller_runtime
 *
 * $Author$
 * $Id$
 */

class controller_runtime {

	/**
	 *  _instance
	 *  当前类的实例
	 *
	 *  @var object
	 */
	protected static $_instance = null;

	/**
	 * get_instance 获取一个实例
	 *
	 * @return void
	 */
	public static function get_instance() {

		if (null === self::$_instance) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * __construct
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct() {

	}

}
