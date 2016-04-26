<?php
/**
 * voa_d_oa_diy_row
 * 表格数据行信息
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_diy_row extends voa_d_abstruct {

	/** 初始化 */
	public function __construct() {

		/** 表名 */
		$this->_table = 'orm_oa.diy_row';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'dr_id';

		parent::__construct();
	}

}
