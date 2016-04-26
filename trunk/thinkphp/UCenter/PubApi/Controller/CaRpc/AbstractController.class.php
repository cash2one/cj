<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace PubApi\Controller\CaRpc;

abstract class AbstractController extends \Common\Controller\CaRpc\AbstractController {

	public function before_action($action = '') {

		return parent::before_action($action);
	}

	public function after_action($action = '') {

		return parent::after_action($action);
	}
}
