<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace OaRpc\Controller\Frontend;

abstract class AbstractController extends \Common\Controller\Frontend\AbstractController {

	// 后置操作
	public function after_action($action = '') {

		return true;
	}
}
