<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace Note\Controller\Frontend;

class IndexController extends AbstractController {

	public function Index() {

		$this->show('[IndexController->Index]');
		$this->_output("Frontend/Index/Index");
	}
	//创建笔记
	public function addNote() {
		redirect('/h5/index.html?#/app/page/note/note-create');
		return true;
	}
	//我的笔记
	public function myNote() {
		redirect('/h5/index.html?#/app/page/note/note-mine');
		return true;
	}
	//查看笔记
	public function viewNote() {
		redirect('/h5/index.html?#/app/page/note/note-look');
		return true;
	}
}
