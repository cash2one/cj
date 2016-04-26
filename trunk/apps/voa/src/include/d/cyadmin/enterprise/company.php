<?php

/**
 *
 * $Author$
 * $Id$
 */
class voa_d_cyadmin_enterprise_company extends voa_d_abstruct {

	/** 初始化 */
	public function __construct( $cfg = null ) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.enterprise_company';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'coid';
		/** 字段前缀 */

		parent::__construct( null );
	}

}

