<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Questionnaire\Controller\Apicp;

abstract class AbstractController extends \Common\Controller\Apicp\AbstractController {

	public function before_action($action = '') {
		$this->_require_login = false;
		return parent::before_action($action);
	}

	public function after_action($action = '') {

		return parent::after_action($action);
	}
}
