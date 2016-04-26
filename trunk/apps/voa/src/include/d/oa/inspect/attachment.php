<?php
/**
 * voa_d_oa_inspect_attachment
 * 巡店附件信息表
 * $Author$
 * $Id$
 */

class voa_d_oa_inspect_attachment extends voa_d_abstruct {

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.inspect_attachment';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'insat_id';
		// 字段前缀
		$this->_prefield = 'insat_';

		parent::__construct();
	}
}
