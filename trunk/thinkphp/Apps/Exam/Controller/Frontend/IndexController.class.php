<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace Exam\Controller\Frontend;

class IndexController extends AbstractController {

	// 开始考试
	public function PaperDetail() {

		redirect('/h5/index.html?ts=' . NOW_TIME . '#/app/page/exam/paper-detail?paper_id=' . I('get.paper_id'));
		return true;
	}

	// 考试结束
	public function PaperFinished() {

		redirect('/h5/index.html?ts=' . NOW_TIME . '#/app/page/exam/paper-finished?paper_id=' . I('get.paper_id'));
		return true;
	}

	// 试卷列表
	public function PaperList() {

		redirect('/h5/index.html?ts=' . NOW_TIME . '#/app/page/exam/paper-list');
		return true;
	}

}
