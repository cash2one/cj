<?php

/**
 * agant.php
 *
 * Created by zhoutao.
 * Created Time: 2015/7/29  12:10
 */
class voa_d_cyadmin_enterprise_agant extends voa_d_abstruct {

	/** 初始化 */
	public function __construct( $cfg = null ) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.enterprise_agant';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'ag_id';

		parent::__construct( null );
	}

}
