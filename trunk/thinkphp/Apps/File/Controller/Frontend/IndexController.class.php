<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace File\Controller\Frontend;

class IndexController extends AbstractController {






	public function Index() {
//		$uinfo = $this->_login->user;
//		var_dump($uinfo);exit;
//		$this->show('[IndexController->Index]');
		//$list = I('request.');
		//$this->assign('list', $list);
		$this->assign('acurl', U('/File/Api/File/File_batch_download/'));
		$this->_output("Frontend/Index/Index");
	}
}
