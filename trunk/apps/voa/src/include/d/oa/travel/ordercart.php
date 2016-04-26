<?php
/**
 * 购物车
 * Create By linshiling
 * $Author$
 * $Id$
 */
class voa_d_oa_travel_ordercart extends voa_d_abstruct {


	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.order_cart';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'cartid';

		parent::__construct(null);
	}

}
