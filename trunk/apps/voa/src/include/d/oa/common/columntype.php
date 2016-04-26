<?php
/**
 * voa_d_oa_common_columntype
 * 表格信息
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_common_columntype extends voa_d_abstruct {

	/** 初始化 */
	public function __construct() {

		/** 表名 */
		$this->_table = 'orm_oa.common_columntype';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'ctid';

		parent::__construct();
	}

}
