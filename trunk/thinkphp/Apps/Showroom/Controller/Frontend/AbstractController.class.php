<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Showroom\Controller\Frontend;

abstract class AbstractController extends \Common\Controller\Frontend\AbstractController {

	public function before_action($action = '') {

		return parent::before_action($action);
	}

	public function after_action($action = '') {

		return parent::after_action($action);
	}
}
