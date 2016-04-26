<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace UcRpc\Controller\Frontend;

class IndexController extends AbstractController {

	public function Index() {

		// $this->show('[IndexController->Index]');
		// $this->display("Frontend/Index/Index");
		$serv = D('UcRpc/Community', 'Service');
		$serv->update_gather();
	}

}

