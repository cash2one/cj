<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace UcRpc\Controller\Rpc;

abstract class AbstractController extends \Common\Controller\Rpc\AbstractController {

	// 后置操作
	public function after_action($action = '') {

		return true;
	}
}
