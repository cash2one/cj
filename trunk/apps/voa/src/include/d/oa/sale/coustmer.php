<?php
/**
 * voa_d_oa_sale_coustmer
 * 
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_sale_coustmer extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.sale_coustmer';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'scid';

		parent::__construct(null);
	}

}
