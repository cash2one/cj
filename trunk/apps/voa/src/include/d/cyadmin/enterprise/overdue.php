<?php
/**
 * @Author: ppker
 * @Date:   2015-10-20 10:53:28
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-10-21 17:56:47
 */
class voa_d_cyadmin_enterprise_overdue extends voa_d_abstruct {

	/** 初始化 */
	public function __construct( $cfg = null ) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.enterprise_overdue';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'ovid';

		parent::__construct( null );
	}

}

