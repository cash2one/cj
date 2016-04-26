<?php
/**
 * 日志/记录对评论的回复信息表
 * $Author$
 * $Id$
 */

class voa_d_oa_thread_post_reply extends voa_d_abstruct {

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.thread_post_reply';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'prid';
		// 字段前缀
		$this->_prefield = '';

		parent::__construct();
	}

}
