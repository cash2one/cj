<?php
/**
 * startup_runtime
 *
 * $Author$
 * $Id$
 */

class startup_runtime extends startup {

	/**
	 * controller
	 * controller 基类的实例
	 *
	 * @var object
	 */
	public $controller = null;

	/**
	 * __construct
	 * 构造方法
	 *
	 * @return void
	 */
	public function __construct($options) {

		parent::__construct($options);

		$this->controller = controller_runtime::get_instance();
	}

	/**
	 * run
	 * 运行
	 *
	 * @return void
	 */
	public function run() {

	}

}
