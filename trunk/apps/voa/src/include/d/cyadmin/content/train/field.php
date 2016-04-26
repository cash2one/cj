<?php
/**
 * voa_d_cyadmin_content_train_field
 * 线下培训扩展
 *
 * $Author$
 * $Id$
 */
class voa_d_cyadmin_content_train_field extends voa_d_abstruct {

	/**
	 * 初始化
	 */
	public function __construct($cfg = null) {
		
		/**
		 * 表名
		 */
		$this->_table = 'orm_cyadmin.train_field';
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
		$this->_pk = 'tfid';
		
		parent::__construct(null);
	}
}
