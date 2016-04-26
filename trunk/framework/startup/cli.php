<?php
/**
 * startup_cli
 *
 * $Author$
 * $Id$
 */

class startup_cli extends startup {

	/**
	 * cli_c
	 * CLI controller 基类的实例
	 *
	 * @var object
	 */
	public $cli_c = null;

	/**
	 * __construct
	 * 构造方法
	 *
	 * @return void
	 */
	public function __construct($options) {
		parent::__construct($options);

		$controllers = $this->get_option('controllers');
		$this->cli_c = controller_cli::get_instance($controllers);
	}

	/**
	 * run
	 * 运行
	 *
	 * @return void
	 */
	public function run() {
		$this->cli_c->handle_request();
	}

}
