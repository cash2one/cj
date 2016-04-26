<?php
/**
 * voa_d_oa_inspect_option
 * 巡店选项信息表
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_inspect_option extends voa_d_abstruct {
	// 使用中
	const STATE_USING = 1;
	// 未使用
	const STATE_UNUSED = 2;

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.inspect_option';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'inso_id';
		// 字段前缀
		$this->_prefield = 'inso_';

		parent::__construct();
	}
}

