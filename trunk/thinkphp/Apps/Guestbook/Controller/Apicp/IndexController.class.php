<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace Guestbook\Controller\Apicp;

class IndexController extends AbstractController {

	public function Index() {

		$this->show('[IndexController->Index]');
		$this->_output("Apicp/Index/Index");
	}
}
