<?php
/**
 * voa_uda_frontend_train_action_categorylist
 * 统一数据访问/培训/目录列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_train_action_categorylist extends voa_uda_frontend_train_abstract {

	/**
	 * 列出所有目录
	 * @return array $list
	 */
	public function get_all_categories() {

		$category = new voa_d_oa_train_category();

		return $category->list_all();
	}

	/**
	 * 根据条件查找记录,用于后台目录列表
	 * @param array $conds 条件数组
	 * @param int|array $page_option 分页参数
	 */
	public function result( $page, &$result, $conds) {

		$result['list'] =  $this->_list_categories_by_conds($conds, $page);
		$result['count'] = $this->_count_categories_by_conds($conds);

		return true;
	}

	/**
	 * 根据条件查找目录
	 * @param array $conds 条件数组
	 * @param int|array $page_option 分页参数
	 * @return array $list
	 */
	protected function _list_categories_by_conds($conds, $page) {

		$category = new voa_d_oa_train_category();
		$categoryright = new voa_d_oa_train_categoryright();
		$tc_ids = array();
		if (isset($conds['m_uid']) || isset($conds['cd_id'])) {
			$rights = $categoryright->list_category($conds);
			if($rights) {
				foreach ($rights as $right) {
					$tc_ids[] = $right['tc_id'];
				}
				$tc_ids = array_unique($tc_ids);
				$conds['tc_id'] = $tc_ids;
			}
		}


		unset($conds['m_uid']);
		unset($conds['cd_id']);

		$list = $category->list_by_conds($conds, $page, array('updated' => 'DESC'));

		return $this->_format_data($list);
	}

	/**
	 * 根据条件计算目录数量
	 * @param array $conds
	 * @return number
	 */
	protected function _count_categories_by_conds($conds) {

		$category = new voa_d_oa_train_category();
		$categoryright = new voa_d_oa_train_categoryright();
		$tc_ids = array();
		if (isset($conds['m_uid']) || isset($conds['cd_id'])) {
			$rights = $categoryright->list_category($conds);
			if($rights) {
				foreach ($rights as $right) {
					$tc_ids[] = $right['tc_id'];
				}
				$tc_ids = array_unique($tc_ids);
				$conds['tc_id'] = $tc_ids;
			}

		}


		unset($conds['m_uid']);
		unset($conds['cd_id']);

		$count = $category->count_by_conds($conds);

		return $count;
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
				$result[$k]['created'] = $list['created'] ? rgmdate($list['created'], 'Y-m-d H:i:s') : '';
				$result[$k]['updated'] = $list['updated'] ? rgmdate($list['updated'], 'Y-m-d H:i:s') : '';
			}
		}

		return $result;
	}

	/**
	 * 列出前台用户有权限查看的目录
	 * @param int $m_uid 用户ID
	 * @param array $page_option 分页
	 * @param array $list 取得的结果
	 * @return number
	 */
	public function list_right_categories($m_uid, $page_option, &$list) {

		$categoryright = new voa_d_oa_train_categoryright();
		$category = new voa_d_oa_train_category();
		$cd_ids = $this->get_department_id($m_uid);

		$records = $categoryright->list_right_categories($m_uid, $cd_ids, $page_option); //目录ID
		if (!empty($records)) { //若目录记录不为空
			foreach($records as $record) {
				$ids[]= $record['tc_id'];
			}
			$list['list'] = $category->list_by_pks($ids);
			$list['count'] = $categoryright->list_right_categories_count($m_uid, $cd_ids);
		}

		return true;
	}

	/**
	 * 返回所有人可查看的目录ID
	 * @return array
	 */
	public function list_categories_by_is_all() {

		$result = array();
		$categoryright = new voa_d_oa_train_categoryright();
		$categories  = $categoryright->list_by_conds(array('is_all' => voa_d_oa_train_categoryright::IS_ALL));
		if ($categories) {
			foreach ($categories as $cate) {
				$result[] = (int)$cate['tc_id'];
			}
		}

		return $result;
	}

}
