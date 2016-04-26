<?php
/**
 * 活动报名 申请取消
 * User: Muzhitao
 * Date: 2015/9/30 0030
 * Time: 14:22
 * Email：muzhitao@vchangyi.com
 */

namespace Activity\Controller\Api;

class CancelController extends AbstractController {

	/**
	 * 申请取消操作
	 * @return bool
	 */
	public function Cancel_post() {

		$acid = I('post.acid', '', 'intval');
		$apply = I('post.apply', '', 'htmlspecialchars');

		// id不合法
		if (empty($acid)) {
			$this->_set_error('_ERR_ACID_NOT_LE_LEGAL');
			return false;
		}

		// 当前申请的用户ID
		$m_uid = $this->_login->user['m_uid'];

		// 条件数组
		$conds = array(
			'm_uid' => $m_uid,
			'acid' => $acid
		);
		$serv_n = D('Activity/ActivityNopartake', 'Service');
		// 操作
		if (!$serv_n->doit($conds, $apply)) {
			$this->_set_error('_ERROR_IS_CHECK');
			return false;
		}

		return true;
	}

}
