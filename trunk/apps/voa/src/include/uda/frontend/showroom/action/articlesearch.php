<?php
/**
 * voa_uda_frontend_showroom_action_articlelist
 * 统一数据访问/陈列/根据关键字搜索有权限查看的文章（供前台接口使用）
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_showroom_action_articlesearch extends voa_uda_frontend_showroom_abstract {

	/**
	 * 根据条件查找记录
	 * @param array $conds 条件数组
	 * @param int|array $page_option 分页参数
	 * @param array $orderby 排序
	 * @throws service_exception
	 */
	public function search_artilce( $m_uid, $keyword, $page_option, &$result ) {

		$article_right = new voa_d_oa_showroom_articleright();
		$article_search = new voa_d_oa_showroom_articlesearch();
		$category_right = new voa_d_oa_showroom_categoryright();

		$result = array();
		$cd_ids = $this->get_department_id($m_uid);    //部门ID

		//取得有权限的目录ID，最多只取10000条
		$tc_ids = array();
		$category_right_rows = $category_right->list_right_categories($m_uid, $cd_ids, array(0,10000));
		if($category_right_rows) {
			foreach ($category_right_rows as $crow) {
				$tc_ids[] = $crow['tc_id'];
			}
		}

		//取得有权限查看的文章ID，因数量大，只取最新的10000条
		$ta_ids = array();
		$article_right_rows = $article_right->list_right_artilces($m_uid,$tc_ids, $cd_ids, array(0, 10000));
		if($article_right_rows) {
			foreach ($article_right_rows as $arow) {
				$ta_ids[] = $arow['ta_id'];
			}
		}

		$result['list'] =  $article_search->search_articles($keyword, $ta_ids, $page_option);
		$result['total'] =$article_search->count_search_articles($keyword, $ta_ids);

		return true;
	}

}
