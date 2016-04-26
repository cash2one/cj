<?php
/**
 * 判断外部人员报名接口
 * OutsideController.class.php
 * $author$
 * User: Yinmengxuan
 */

namespace Activity\Controller\Api;

class OutSideController extends AbstractController {

	// 不强制登陆
	public function before_action() {

		$this->_require_login = false;
		return parent::before_action();
	}

	public function Outside_post() {

		$acid = I('post.acid');//当前活动ID
		$outname = I('post.outname');//外部人员报名姓名
		$outphone = I('post.outphone');//外部人员报名电话

		$serv_o = D('Activity/ActivityOutsider', 'Service');

		// 查询当前用户是否已报名
		$result = $serv_o->get_by_uid_out($acid, $outname, $outphone);
		$this->_result = $result;

		return true;

	}
}
