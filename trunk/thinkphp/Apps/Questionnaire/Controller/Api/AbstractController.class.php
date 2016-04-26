<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Questionnaire\Controller\Api;

abstract class AbstractController extends \Common\Controller\Api\AbstractController {

	public function before_action($action = '') {

		return parent::before_action($action);
	}

	public function after_action($action = '') {

		return parent::after_action($action);
	}
}
