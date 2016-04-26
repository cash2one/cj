<?php
/**
 * voa_d_cyadmin_content_join_list
 * 人才招聘
 *
 * $Author$
 * $Id$
 */
class voa_d_cyadmin_content_join_list extends voa_d_abstruct {

	/**
	 * 初始化
	 */
	public function __construct($cfg = null) {
		
		/**
		 * 表名
		 */
		$this->_table = 'orm_cyadmin.join';
		/**
		 * 允许的字段
		 */
		$this->_allowed_fields = array();
		/**
		 * 必须的字段
		 */
		$this->_required_fields = array();
		/**
		 * 主键
		 */
		$this->_pk = 'jid';
		
		parent::__construct(null);
	}
}
