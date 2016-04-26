<?php
/**
 * voa_uda_frontend_thread_post_abstract
 * 统一数据访问/社区应用/评论基类
 *
 * $Author$
 * $Id$
 */

abstract class voa_uda_frontend_thread_post_abstract extends voa_uda_frontend_thread_abstract {

	public function __construct() {

		$this->_serv = new voa_s_oa_thread_post();
		parent::__construct();
	}

	/**
	 * 检查权限
	 * @param int $tid 主题id
	 * @param int $pid 评论id
	 * @param array $post 评论信息
	 * @return boolean
	 */
	protected function _chk_post_privilege($tid, $pid, &$post) {

		// 读取主题信息
		$serv_t = &service::factory('voa_s_oa_thread');
		if (!$thread = $serv_t->get($tid)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_thread::THREAD_IS_NOT_EXISTS);
			return false;
		}

		// 如果回复的是评论信息, 则读取评论信息
		if (!empty($pid) && 0 < $pid) {
			if (!$post = $this->_serv->get($pid)) {
				voa_h_func::throw_errmsg(voa_errcode_oa_thread::REPLY_IS_NOT_EXISTS);
				return false;
			}
		}

		// 检查主题权限
		return $this->_chk_privilege($thread);
	}
}
