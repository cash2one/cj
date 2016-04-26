<?php
/**
 * setting.php
 * 超级报表- 设置表
 * $Author$
 * $Id$
 */
class voa_d_oa_superreport_setting extends voa_d_abstruct {

	/** 数组数据 */
	const TYPE_ARRAY = 1;
	/** 标量数据 */
	const TYPE_NORMAL = 0;

	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.superreport_setting';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'key';

		parent::__construct(null);
	}

}
