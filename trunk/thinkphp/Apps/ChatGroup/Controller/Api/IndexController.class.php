<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace ChatGroup\Controller\Api;

class IndexController extends AbstractController {

	public function Index() {

		//$ser = D('ChatGroup/ChatgroupMember', 'Service');
		$this->show('[IndexController->Index]');
		$this->_output("Api/Index/Index");
	}
}
