<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace Sign\Controller\Frontend;

class IndexController extends AbstractController {

	public function Index() {

		$serv = D('Sign/SignRecord', 'Service');
		return true;
	}
}
