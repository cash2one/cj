<?php
/**
 * $Author$
 * $Id$
 */

class voa_d_oa_sign_location extends voa_d_abstruct {
	const STATUS_REMOVE = 64;
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.sign_location';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		
		$this->_prefield = 'sl_';
		/** 主键 */
		$this->_pk = 'sl_id';

		parent::__construct(null);
	}

}

