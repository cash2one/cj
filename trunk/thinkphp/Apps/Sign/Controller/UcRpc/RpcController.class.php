<?php
/**
 * RpcController.class.php
 * $author$
 */

namespace Sign\Controller\UcRpc;

class RpcController extends AbstractController {

	public function Index() {

		$this->show('[RpcController->Index]');
		$this->_output("UcRpc/Rpc/Index");
	}
}
