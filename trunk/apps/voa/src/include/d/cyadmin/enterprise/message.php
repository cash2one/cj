<?php

/**
 * voa_d_oa_activity
 * 活动报名
 *
 * $Author$
 * $Id$
 */
class voa_d_cyadmin_enterprise_message extends voa_d_abstruct {

	/** 初始化 */
	public function __construct( $cfg = null ) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.enterprise_message';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'meid';

		parent::__construct( null );
	}

	public function __list_by_complex( $data, $limit ) {

		return $this->_list_by_complex( "(type = ? or epid = ? ) and status < 3", $data, $limit, array( 'created' => 'DESC' ) );
	}

	public function list_by_complex( $data ) {

		return $this->_list_by_complex( "(type = ? or epid = ? ) and status < 3", $data, '', array( 'created' => 'DESC' ) );
	}

	public function count_by_complex( $data ) {

		return $this->_count_by_complex( "(type = ? or epid = ? ) and status < 3", $data, '*' );
	}

}

