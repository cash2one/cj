<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace Minutes\Controller\Frontend;

class IndexController extends AbstractController {

	public function Index() {

		$this->show('[IndexController->Index]');
		$this->_output("Frontend/Index/Index");
	}
}
