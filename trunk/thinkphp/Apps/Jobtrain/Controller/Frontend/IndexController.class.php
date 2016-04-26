<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace Jobtrain\Controller\Frontend;

class IndexController extends AbstractController {

	public function Index() {

		$this->show('[IndexController->Index]');
		$this->_output("Frontend/Index/Index");
	}

	// 文章列表
	public function ArticleList() {

		redirect('/h5/index.html?ts=' . NOW_TIME . '#/app/page/jobtrain/article-list');
		return true;
	}

	public function CollList() {

		redirect('/h5/index.html?ts=' . NOW_TIME . '#/app/page/jobtrain/coll-list');
		return true;
	}

	// 文章详情
	public function ArticleDetail() {

		redirect('/h5/index.html?ts=' . NOW_TIME . '#/app/page/jobtrain/article-detail?id=' . I('get.aid'));
		return true;
	}

	// 评论列表
	public function CommentList() {

		redirect('/h5/index.html?ts=' . NOW_TIME . '#/app/page/jobtrain/comment-list?id=' . I('get.aid'));
		return true;
	}

}
