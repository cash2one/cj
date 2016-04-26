<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace UcRpc\Controller\Api;

class IndexController extends AbstractController {

	public function Index() {

		$this->show('[IndexController->Index]');
		$this->_output("Api/Index/Index");
	}
}
