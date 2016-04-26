<?php
/**
 * voa_d_oa_goods_table
 * 表格信息
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_goods_table extends voa_d_abstruct {

	/** 初始化 */
	public function __construct() {

		/** 表名 */
		$this->_table = 'orm_oa.goods_table';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'tid';

		parent::__construct();
	}


}

