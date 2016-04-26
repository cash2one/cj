<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Cli\Controller\Frontend;

abstract class AbstractController extends \Common\Controller\Frontend\AbstractController {

	public function before_action($action = '') {

		// 无需登录
		$this->_require_login = false;
		// 获取 HOST 参数
		$host = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : '';
		// 设置 HTTP_HOST
		$_SERVER['HTTP_HOST'] = $host;

		return parent::before_action($action);
	}

	public function after_action($action = '') {

		return parent::after_action($action);
	}
}
