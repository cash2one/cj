<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Common\Controller\Frontend;
use Think\Controller;
use Com\Cookie;

abstract class AbstractController extends Controller {

	// 前置操作
	public function before_action($action = '') {

		return true;
	}

	// 后置操作
	public function after_action($action = '') {

		return true;
	}
}
