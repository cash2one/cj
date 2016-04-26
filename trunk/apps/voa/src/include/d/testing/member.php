<?php
/**
 * voa_d_oa_member
 * 首页
 *
 * $Author$
 * $Id$
 */

class voa_d_testing_member extends voa_d_abstruct {

	/** 初始化 */
	public function __construct() {

		/** 表名 */
		$this->_table = 'orm_oa.member';
		/** 允许的字段 */
		$this->_allowed_fields = array('m_username');
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'm_uid';

		parent::__construct();
	}



}


