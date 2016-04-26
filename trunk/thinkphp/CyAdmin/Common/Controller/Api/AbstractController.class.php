<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Common\Controller\Api;
use Think\Controller\RestController;
use Com\Cookie;

class AbstractController extends RestController {

	// 前置操作
	public function before_action($action = '') {

		return true;
	}

	// 后置操作
	public function after_action($action = '') {

		return true;
	}
}
