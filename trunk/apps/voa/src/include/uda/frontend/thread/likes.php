<?php
/**
 * voa_uda_frontend_thread_likes
 * 统一数据访问/社区应用/帖子列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_thread_likes extends voa_uda_frontend_thread_abstract {
	// 列表
	protected $_pu_serv = null;

	public function __construct() {

		parent::__construct();
		$this->_pu_serv = new voa_s_oa_thread_permit_user();
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
			array('uid', self::VAR_INT, null, null, false),
			array('tid', self::VAR_INT, null, null, false)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 如果帖子不存在
		$thread = array();
		if (!$thread = $this->_serv->get($conds['tid'])) {
			voa_h_func::throw_errmsg(voa_errcode_oa_thread::THREAD_IS_NOT_EXISTS);
			return false;
		}

		// 判断权限
/* 		if (!$this->_chk_privilege($thread)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_thread::NO_PRIVILEGE);
			return false;
		} */

		// 赞 +1
		$this->_serv->update_by_conds($conds['tid'], array('`likes`=`likes`+?' => 1));

		return true;
	}

}
