<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace Guestbook\Controller\Api;

class IndexController extends AbstractController {

	public function Index_get() {

		$this->_response(L('copyright'));
	}
}
