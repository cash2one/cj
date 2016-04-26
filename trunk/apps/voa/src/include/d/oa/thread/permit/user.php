<?php
/**
 * 允许查看日志/记录的用户表
 * $Author$
 * $Id$
 */

class voa_d_oa_thread_permit_user extends voa_d_abstruct {

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.thread_permit_user';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'puid';
		// 字段前缀
		$this->_prefield = '';

		parent::__construct();
	}

}
