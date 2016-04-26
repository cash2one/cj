<?php
/**
 * startup_web
 *
 * $Author$
 * $Id$
 */

class startup_web extends startup {

	/**
	 * front_c
	 *
	 * @var object
	 */
	public $front_c = null;

	/**
	 * __construct
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct($options) {

		parent::__construct($options);

		$this->set_front_c();
	}

	/**
	 * run
	 * 运行App
	 *
	 * @return void
	 */
	public function run() {

		$this->front_c->handle_request();
	}

	/**
	 * set_front_c
	 * 设置前端控制器
	 *
	 * @param  object $front_c
	 * @return object
	 */
	public function set_front_c($front_c = null) {

		if ($front_c) {
			return $this->front_c = $front_c;
		}

		return $this->front_c = controller_front::get_instance();
	}

}
