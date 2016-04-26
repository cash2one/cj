<?php
/**
 * detail.php
 * 销售
 * Create By linshiling
 * $Author$
 * $Id$
 */
class voa_d_oa_travel_sale extends voa_d_abstruct {


	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.sale';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'saleid';

		parent::__construct(null);
	}

}
