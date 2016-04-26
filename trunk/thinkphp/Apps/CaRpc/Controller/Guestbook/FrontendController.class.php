<?php
/**
 * FrontendController.class.php
 * $author$
 */

namespace CaRpc\Controller\Guestbook;

class FrontendController extends AbstractController {

	public function Index() {

		$this->show('[FrontendController->Index]');
		$this->_output("Guestbook/Frontend/Index");
	}
}
