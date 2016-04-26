<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace PubApi\Controller\Frontend;

class IndexController extends AbstractController {

	public function Index() {

		// $this->show('[IndexController->Index]');
		$this->assign('acurl', U('/PubApi/Api/Attachment/Upload'));
		$this->_output("Frontend/Index/Index");
	}

}
