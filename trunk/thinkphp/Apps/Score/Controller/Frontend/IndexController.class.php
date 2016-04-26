<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace Score\Controller\Frontend;

class IndexController extends AbstractController {

	//我的积分
	public function scoreLogList() {
		redirect('/h5/index.html?#/app/page/score/score-my-score');
		return true;
	}
	//奖品兑换
	public function awardList() {
		redirect('/h5/index.html?#/app/page/score/socre-exchange');
		return true;
	}
}
