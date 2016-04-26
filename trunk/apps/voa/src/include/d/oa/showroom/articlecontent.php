<?php
/**
 * voa_d_oa_showroom_articlecontent
 * 文章内容
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */

class voa_d_oa_showroom_articlecontent extends voa_d_abstruct {


	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.showroom_article_content';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'tac_id';

		parent::__construct(null);
	}

	/**
	 * 更新数据
	 * @param int $val 文章ID
	 * @param array $data 待更新数据
	 */
	public function update_by_article_id ($id, $data) {

		return $this->update_by_conds(array('ta_id' => $id), $data);
	}

}

