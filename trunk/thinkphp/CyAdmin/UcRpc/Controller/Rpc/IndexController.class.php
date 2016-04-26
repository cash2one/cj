<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace UcRpc\Controller\Rpc;

class IndexController extends AbstractController {

	public function Index() {

		$this->show('[IndexController->Index]');
		$this->_output("Rpc/Index/Index");
	}
}
