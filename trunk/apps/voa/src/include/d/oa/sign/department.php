<?php
/**
 * voa_d_oa_sign_batch
 * @author Burce
 *
 */
class voa_d_oa_sign_department extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.sign_department';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'sdid';

		parent::__construct(null);
	}

}

