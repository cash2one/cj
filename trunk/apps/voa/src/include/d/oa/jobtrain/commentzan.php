<?php
/**
 * 培训-评论
 * Create By wowxavi
 * $Author$
 * $Id$
 */

class voa_d_oa_jobtrain_commentzan extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.jobtrain_comment_zan';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}

}