<?php
/**
 * voa_d_oa_showroom_articlemember
 * 用户阅读文章 情况表
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */

class voa_d_oa_showroom_articlemember extends voa_d_abstruct {


	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.showroom_article_member';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'tam_id';

		parent::__construct(null);
	}

	/**
	 * 物理删除文章权限
	 * @param array $ids 文章ID
	 */
	public function delete_real_by_article_ids ($ids) {

		return $this->_delete_real_by_conds(array('ta_id' => $ids));
	}

	/**
	 * 物理删除文章权限
	 * @param int $id 文章ID
	 */
	public function delete_real_by_article_id ($id) {

		return $this->_delete_real_by_conds(array('ta_id' => $id));
	}
}
