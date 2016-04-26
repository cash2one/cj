<?php
/**
 * voa_d_oa_inspect_mem
 * 可查看巡店信息人员信息表
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_inspect_mem extends voa_d_abstruct {
	// 接收人
	const TYPE_TO = 1;
	// 抄送人
	const TYPE_CC = 2;

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.inspect_mem';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'insm_id';
		// 字段前缀
		$this->_prefield = 'insm_';

		parent::__construct();
	}
}

