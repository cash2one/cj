<?php
/**
 * voa_uda_frontend_thread_delete
 * 统一数据访问/社区应用/删除帖子
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_thread_delete extends voa_uda_frontend_thread_abstract {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 输入参数
	 * @param array $in 输入参数
	 * @param array &$out 输出参数
	 * @return boolean
	 */
	public function execute($in, &$out) {
		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('tid', self::VAR_ARR, null, null, false)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		// 判断权限
/* 		if (!$this->_chk_privilege($thread)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_thread::NO_PRIVILEGE);
			return false;
		} */
		// 删除信息
		$this->_serv->delete($data['tid']);
		// 删除主题的评论信息
		$serv_p = &service::factory('voa_d_oa_thread_post');
		$serv_p->delete($data['tid']);
		// 删除允许查看主题的用户信息

		return true;
	}

	/**
	 * 检查权限
	 * @param array $thread 帖子信息
	 * @return boolean
	 */
	protected function _chk_privilege($thread) {

		// 如果当前用户为发起人
		if (startup_env::get('wbs_uid') == $thread['uid']) {
			return true;
		}

		return false;
	}

}
