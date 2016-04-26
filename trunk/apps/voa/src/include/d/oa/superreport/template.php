<?php
/**
 * template.php
 * 超级报表 - 模板表
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_d_oa_superreport_template extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.superreport_template';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'st_id';

		parent::__construct(null);
	}

}
