<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace Notin\Controller\Frontend;

class IndexController extends AbstractController {

	public function Index() {

		$this->show('[IndexController->Index]');
		$this->_output("Frontend/Index/Index");
	}
}
