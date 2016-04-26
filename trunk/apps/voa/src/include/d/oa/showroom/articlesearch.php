<?php
/**
 * voa_d_oa_showroom_articlesearch
 * 文章搜索
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */

class voa_d_oa_showroom_articlesearch extends voa_d_abstruct {


	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.showroom_article_search';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'tas_id';

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

	/**
	 * 根据搜索条件查找文章
	 * @param string $keyword 关键字
	 * @param array $ta_ids 文章ID
	 * @param array $page_option 分页信息
	 */
	public function search_articles($keyword, $ta_ids, $page_option) {

			$list = $this->list_by_conds(array('content LIKE?' => "%$keyword%", 'ta_id' => $ta_ids ), $page_option);

			return $list;
	}

	/**
	 * 根据搜索条件查找文章数量
	 * @param string $keyword 关键字
	 * @param array $ta_ids 文章ID
	 */
	public function count_search_articles($keyword, $ta_ids) {

		$count = $this->count_by_conds(array('content LIKE?' =>"%$keyword%", 'ta_id' => $ta_ids ));

		return $count;
	}
}

