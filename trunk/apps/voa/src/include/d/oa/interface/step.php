<?php
/**
 * voa_d_oa_interface_step
 * 流程步骤
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_interface_step extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.interface_step';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 's_id';

		parent::__construct(null);
	}

}

