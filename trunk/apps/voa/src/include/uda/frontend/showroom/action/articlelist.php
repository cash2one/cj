<?php
/**
 * voa_uda_frontend_showroom_action_articlelist
 * 统一数据访问/陈列/文章列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_showroom_action_articlelist extends voa_uda_frontend_showroom_abstract {

	/**
	 * 根据条件查找文章
	 * @param array $conds 条件数组
	 * @param int|array $page_option 分页参数
	 */
	public function result( $page, &$result, $conds) {

		$result['list'] =  $this->_list_articles_by_conds($conds, $page);
		$result['count'] = $this->_count_articles_by_conds($conds);

		return true;
	}

	/**
	 * 根据条件查找文章
	 * @param array $conds 条件数组
	 * @param int|array $page_option 分页参数
	 * @return array $list
	 */
	protected function _list_articles_by_conds($conds, $page) {

		$article = new voa_d_oa_showroom_article();
		$list = $article->list_by_conds($conds, $page, array('updated' => 'DESC'));
		if ($list) { //如果记录不为空
			foreach ($list as $val) {
				$category_ids[] = $val['tc_id'];
			}
			$categories = $this->_list_category_names_by_pks($category_ids);
			if ($categories) {
				foreach ($list as $k => $val) {
					$list[$k]['category'] = isset($categories[$val['tc_id']]['title']) ? $categories[$val['tc_id']]['title'] : '';
				}
			}
		}
		return $this->_format_data($list);
	}

	/**
	 * 根据条件计算文章数量
	 * @param array $conds
	 * @return number
	 */
	protected function _count_articles_by_conds($conds) {

		$article = new voa_d_oa_showroom_article();
		$count = $article->count_by_conds($conds);

		return $count;
	}

	/**
	 * 根据目录ID取得目录名
	 * @param array $tc_ids 目录ID
	 * @return array  目录名
	 */
	protected  function _list_category_names_by_pks($tc_ids) {

		$list = array();
		$category = new voa_d_oa_showroom_category();
		if (!empty($tc_ids)) {
			$list = $category->list_by_pks($tc_ids);
		}

		return $list;
	}

	/**
	 * 格式化输出信息
	 * @param array  文章列表
	 */
	protected  function _format_data($lists) {

		$result = array();
		if ($lists) {
			foreach ($lists as $k => $list) {
				$result[$k] = $list;
				$result[$k]['title'] = rhtmlspecialchars($list['title']);
				$result[$k]['author'] = rhtmlspecialchars($list['author']);
				$result[$k]['category'] = rhtmlspecialchars($list['category']);
				$result[$k]['created'] = $list['created'] ? rgmdate($list['created'], 'Y-m-d H:i:s') : '';
				$result[$k]['updated'] = $list['updated'] ? rgmdate($list['updated'], 'Y-m-d H:i:s') : '';
			}
		}

		return $result;
	}

	/**
	 * 查找一个目录下的文章数量
	 * @param int $tc_id 目录ID
	 * @return number
	 */
	public function count_articles_by_category_id($tc_id) {

		$article = new voa_d_oa_showroom_article();
		$count = $article->count_by_conds(array('tc_id'=>$tc_id));

		return $count;
	}

	/**
	 * 查找用户有权限查看的文章
	 * @param int $m_uid 用户ID
	 * @param int $tc_id 目录ID
	 * @param int $page_option 分页
	 * @param int $list 结果
	 */
	public function list_right_artilce($m_uid, $tc_id, $page_option, &$list) {

		$articleright = new voa_d_oa_showroom_articleright();
		$article = new voa_d_oa_showroom_article();
		$article_member = new voa_d_oa_showroom_articlemember();
		$cd_ids = $this->get_department_id($m_uid); //部门ID

		$records = $articleright->list_right_artilce($m_uid, $tc_id, $cd_ids, $page_option); //取得由权限的文章ID
		if (!empty($records)) {  //若文章记录不为空
			foreach ($records as $record) {
				$ids[] = $record['ta_id'];
			}
			$result =  $article->list_by_conds(array('ta_id' => $ids),null, array('updated' => 'DESC'));  //取得文章

			//取得文章阅读情况
			$read_ids = array();
			$read = $article_member->list_by_conds(array('ta_id' => $ids, 'm_uid' => $m_uid));
			if ($read) {
				foreach ($read as $v) {
					$read_ids[] = $v['ta_id'];
				}
			}

			$list['list'] = $this->_get_plus_info($result, $read_ids);
			$list['total'] = $articleright->list_right_article_count($m_uid, $tc_id, $cd_ids);
		}

		return true;
	}

	/**
	 * 取得文章列表附加信息
	 * @param array $list 文章列表
	 */
	protected function _get_plus_info($list, $read_ids) {

		$d_categories = new voa_d_oa_showroom_category();
		$categories = $d_categories->list_all();

		$result = array();
		if ($list) {
			foreach ($list as $k => $val) {
				$result[$k] = $val;
				$result[$k]['tc_name'] = $categories[$val['tc_id']]['title'];  //目录名
				$result[$k]['read'] = in_array($k, $read_ids) ? true : false;  //是否已阅读
			}
		}

		return $result;
	}

}
