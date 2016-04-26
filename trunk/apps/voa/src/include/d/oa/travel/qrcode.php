<?php
/**
 * detail.php
 * 销售
 * Create By linshiling
 * $Author$
 * $Id$
 */
class voa_d_oa_travel_qrcode extends voa_d_abstruct {


	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.qrcode_member';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'sale_id';

		parent::__construct(null);
	}

}
