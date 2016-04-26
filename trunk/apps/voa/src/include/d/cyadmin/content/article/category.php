<?php
/**
 * voa_d_cyadmin_content_article_category
 * 文章分类
 *
 * $Author$
 * $Id$
 */
class voa_d_cyadmin_content_article_category extends voa_d_abstruct {

	/**
	 * 初始化
	 */
	public function __construct($cfg = null) {
		
		/**
		 * 表名
		 */
		$this->_table = 'orm_cyadmin.article_category';
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
		$this->_pk = 'acid';
		
		parent::__construct(null);
	}
}
