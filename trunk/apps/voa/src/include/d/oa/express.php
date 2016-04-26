<?php
/**
 * 快递基本表
 * $Author$
 * $Id$
 */

class voa_d_oa_express extends voa_d_abstruct {

	//未领
	const GET_NO = 1;
	//已领
	const GET_YES = 2;

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.express';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'eid';
		// 字段前缀
		$this->_prefield = '';

		parent::__construct();
	}

}
