<?php
/**
 * voa_d_oa_inspect_item
 * 巡店打分项信息表
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_inspect_item extends voa_d_abstruct {
	// 使用中
	const STATE_USING = 1;
	// 未使用
	const STATE_UNUSED = 2;

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.inspect_item';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'insi_id';
		// 字段前缀
		$this->_prefield = 'insi_';

		parent::__construct();
	}
}

