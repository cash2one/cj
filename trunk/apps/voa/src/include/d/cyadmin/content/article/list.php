<?php
/**
 * voa_d_cyadmin_article_list
 * 文章主页
 *
 * $Author$
 * $Id$
 */
class voa_d_cyadmin_content_article_list extends voa_d_abstruct {

	/**
	 * 初始化
	 */
	public function __construct($cfg = null) {
		
		/**
		 * 表名
		 */
		$this->_table = 'orm_cyadmin.article';
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
		$this->_pk = 'aid';
		
		parent::__construct(null);
	}
}
