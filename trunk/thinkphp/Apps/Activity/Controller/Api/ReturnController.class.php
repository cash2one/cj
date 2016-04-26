<?php
/**
 * 活动报名 审核取消报名操作
 * User: Muzhitao
 * Date: 2015/9/30 0030
 * Time: 14:22
 * Email：muzhitao@vchangyi.com
 */


namespace Activity\Controller\Api;

class ReturnController extends AbstractController {

	/**
	 * 审批视图
	 * @return bool
	 */
	public function Return_view_get() {

		$acid = I('get.acid', '', 'intval');
		$apid = I('get.apid', '', 'intval');

		// 活动ID不合法 或者审批ID不合法
		if (empty($acid) || empty($apid)) {
			$this->_set_error('_ERROR_PARAMETER');
			return false;
		}

		// 当前浏览人的m_uid
		$m_uid = $this->_login->user['m_uid'];

		$serv_n = D('Activity/ActivityNopartake', 'Service');
		$result = array();
		$serv_n->return_detail($acid, $apid, $m_uid, $result);

		// 返回数据
		$this->_result = $result;

		return true;
	}

	/**
	 * 同意操作
	 * @return bool
	 */
	public function Agree_post() {

		// 活动ID
		$acid = I('post.acid', '', 'intval');
		// 报名ID
		$apid = I('post.apid', '', 'intval');

		$serv_n = D('Activity/ActivityNopartake', 'Service');

		// 同意操作
		$serv_n->agree_apply($acid, $apid);

		return true;
	}

	/**
	 * 驳回操作
	 * @return bool
	 */
	public function Reject_post() {

		// 活动ID
		$acid = I('post.acid', '', 'intval');
		// 报名ID
		$apid = I('post.apid', '', 'intval');
		// 审核ID
		$anpid = I('post.anpid', '', 'intval');
		// 驳回理由
		$apply = I('post.reject', '', 'htmlspecialchars');

		$serv_n = D('Activity/ActivityNopartake', 'Service');

		// 驳回操作
		$serv_n->reject_apply($acid, $apid, $anpid, $apply);

		return true;
	}
}
