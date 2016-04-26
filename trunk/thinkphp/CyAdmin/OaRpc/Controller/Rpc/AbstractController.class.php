<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace OaRpc\Controller\Rpc;

abstract class AbstractController extends \Common\Controller\Rpc\AbstractController {

	public function before_action($action = '') {

		return parent::before_action($action);
	}

	public function after_action($action = '') {

		return parent::after_action($action);
	}

}
