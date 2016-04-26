<?php
/**
 * voa_d_oa_interface_flow
 * 接口流程
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_interface_flow extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.interface_flow';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'f_id';

		parent::__construct(null);
	}

	public function update_by_fids() {

	}

}

