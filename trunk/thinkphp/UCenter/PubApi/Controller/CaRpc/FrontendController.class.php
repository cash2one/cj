<?php
/**
 * FrontendController.class.php
 * $author$
 */

namespace PubApi\Controller\CaRpc;

class FrontendController extends AbstractController {

	public function Index() {

		$this->show('[FrontendController->Index]');
		$this->_output("CaRpc/Frontend/Index");
	}
}
