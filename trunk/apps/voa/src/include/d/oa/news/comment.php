<?php
/**
 * voa_d_oa_news_comment
 * 文章
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */

class voa_d_oa_news_comment extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.news_comment';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'ncomm_id';
		parent::__construct(null);
	}

}

