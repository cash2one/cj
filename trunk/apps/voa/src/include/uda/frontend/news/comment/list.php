<?php
/**
 * voa_uda_frontend_news_comment_list
 * 统一数据访问/新闻公告/评论列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_news_comment_list extends voa_uda_frontend_news_abstract {

	/** service 类 */
	private $__service = null;

	public function __construct() {
		parent::__construct();
		if ($this->__service === null) {
			$this->__service = new voa_s_oa_news_comment();
		}
	}

	/**
	 * 根据条件查找新闻公告列表
	 * @param array $conds 条件数组
	 * @param int|array $pager 分页参数
	 */
	public function list_comments(&$list, $conds) {
		$list = $this->_list_comments_by_conds($conds);

		return true;
	}

	/**
	 * 根据条件查找目录
	 * @param array $conds 条件数组
	 * @param int|array $pager 分页参数
	 * @return array $list
	 */
	protected function _list_comments_by_conds($conds) {

		$list = array();
		$list = $this->__service->list_by_conds($conds, null, array('updated' => 'DESC'));
		$this->__service->format_list($list);

		return $list;
	}

}
