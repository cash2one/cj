<?php
/**
 * voa_d_oa_inspect_draft
 * 会议记录草稿信息表
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_inspect_draft extends voa_d_abstruct {

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.inspect_draft';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'insd_id';
		// 字段前缀
		$this->_prefield = 'insd_';

		parent::__construct();
	}

}
