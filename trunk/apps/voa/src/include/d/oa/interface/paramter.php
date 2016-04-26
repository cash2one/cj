<?php
/**
 * voa_d_oa_interface_paramter
 * 接口参数
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_interface_paramter extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.interface_parameter';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'p_id';

		parent::__construct(null);
	}

}

