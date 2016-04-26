<?php
/**
 * voa_d_oa_train_article
 * 文章
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */

class voa_d_oa_train_article extends voa_d_abstruct {

	/** 标题最短字符 */
	const LENGTH_TITLE_MIN = 0;
	/** 标题最长字符 */
	const LENGTH_TITLE_MAX = 80;
	/** 作者最短字符 */
	const LENGTH_AUTHOR_MIN = 0;
	/** 作者最长字符 */
	const LENGTH_AUTHOR_MAX = 40;
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.train_article';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'ta_id';
		parent::__construct(null);
	}

	/**
	 * 返回新增记录ID
	 * @return int 文章ID
	 */
	public function getLastInsertId() {

		return $this->_lastInsertId();
	}


}

