<?php
/**
 * voa_d_oa_activity
 * 活动报名
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_activity extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.activity';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'acid';

		parent::__construct(null);
	}

}

