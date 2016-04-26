<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace Askfor\Controller\Frontend;
use Common\Common\Cache;
use Com;

class IndexController extends AbstractController {

	// 查看自由审批详情
	public function ViewFree() {

		redirect('/h5/index.html?#/app/page/approve/approve-freedetail?af_id=' . I('get.af_id'));
		return true;
	}

	// 查看固定审批详情
	public function ViewFixed() {

		redirect('/h5/index.html?#/app/page/approve/approve-fixeddetail?af_id=' . I('get.af_id'));
		return true;
	}
}
