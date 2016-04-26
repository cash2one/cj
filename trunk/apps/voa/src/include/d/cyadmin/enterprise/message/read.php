<?php

/**
 * @Author: ppker
 * @Date:   2015-07-30 16:21:08
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-08-07 11:35:42
 */
class voa_d_cyadmin_enterprise_message_read extends voa_d_abstruct {

	/** 初始化 */
	public function __construct( $cfg = null ) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.enterprise_message_read';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'reid';
		/** 字段前缀 */
		parent::__construct( null );
	}

}
