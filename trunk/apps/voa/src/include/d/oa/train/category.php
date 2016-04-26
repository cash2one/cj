<?php
/**
 * voa_d_oa_train_category
 * 文章目录
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */

class voa_d_oa_train_category extends voa_d_abstruct {

	/** 标题最短字符 */
	const LENGTH_TITLE_MIN = 0;
	/** 标题最长字符 */
	const LENGTH_TITLE_MAX = 80;
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.train_category';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'tc_id';

		parent::__construct(null);
	}

	/**
	 * 返回新增记录ID
	 * @return int 目录ID
	 */
	public function getLastInsertId() {

		return $this->_lastInsertId();
	}

}
