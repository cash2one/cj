<?php
/**
 * InterfaceModel.class.php
 * $author$
 */

namespace Cli\Model;

class InterfaceModel extends AbstractModel {

	// get
	const MODE_GET = 1;
	// post
	const MODE_POST = 2;

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	public function get_mode_get() {

		return self::MODE_GET;
	}

	public function get_mode_post() {

		return self::MODE_POST;
	}

}
