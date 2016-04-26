<?php
/**
 * voa_d_cyadmin_content_link_list
 * 友情链接
 *
 * $Author$
 * $Id$
 */
class voa_d_cyadmin_content_link_list extends voa_d_abstruct {

	/**
	 * 初始化
	 */
	public function __construct($cfg = null) {
		
		/**
		 * 表名
		 */
		$this->_table = 'orm_cyadmin.link';
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
		$this->_pk = 'lid';
		
		parent::__construct(null);
	}
}
