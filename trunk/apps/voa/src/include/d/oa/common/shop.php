<?php
/**
 * 门店信息表
 * $Author$
 * $Id$
 */

class voa_d_oa_common_shop extends voa_d_abstruct {

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.common_shop';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'csp_id';
		// 字段前缀
		$this->_prefield = 'csp_';

		parent::__construct();
	}
}
