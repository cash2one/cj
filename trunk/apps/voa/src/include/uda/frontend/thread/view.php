<?php
/**
 * voa_uda_frontend_thread_view
 * 统一数据访问/社区应用/查看帖子
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_thread_view extends voa_uda_frontend_thread_abstract {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 输入参数
	 * @param array $in 输入参数
	 * @param array &$thread 输出参数
	 * @return boolean
	 */
	public function execute($in, &$thread) {
        
		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('tid', self::VAR_INT, null, null, false)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}
    
		// 如果帖子不存在
		if (!$thread = $this->_serv->get($data['tid'])) {
			voa_h_func::throw_errmsg(voa_errcode_oa_thread::THREAD_IS_NOT_EXISTS);
			return false;
		}

		// 判断权限
/* 		if (!$this->_chk_privilege($thread)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_thread::NO_PRIVILEGE);
			return false;
		} */

		// 格式化
		$this->_fmt && $this->_format($thread);

		return true;
	}

}
