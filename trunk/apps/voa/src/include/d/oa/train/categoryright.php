<?php
/**
 * voa_d_oa_train_categoryright
 * 文章目录查看权限
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */

class voa_d_oa_train_categoryright extends voa_d_abstruct {

	/** 所有人可查看 */
	const 	IS_ALL = 1;
	/** 非所有人可查看 */
	const NOT_IS_ALL = 0;
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.train_category_right';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'tcr_id';

		parent::__construct(null);
	}

	/**
	 * 查找用户有权限查看的目录
	 * @param int $m_uid 用户ID
	 * @param array $cd_ids 用户所属部门ID
	 * @param int $page_option 分页
	 */
	public function list_right_categories($m_uid, $cd_ids, $page_option) {

		$str = array();
		$cd_ids = empty($cd_ids) ? array(0) : $cd_ids;
		$this->_field_sign_condi($str, "cd_id in (?)", $cd_ids);
		$sql = " (m_uid=? OR {$str[0]} OR is_all=?) AND status<? ";
		$data[] = $m_uid;
		foreach ($cd_ids as $id) {
			$data[] = $id;
		}
		$data[] = 1;
		$data[] = parent::STATUS_DELETE;

		$list = $this->_list_by_complex($sql, $data, $page_option, array('updated' =>'DESC'), 'tc_id, tcr_id');

		return $list;
	}

	/**
	 * 查找用户有权限查看的目录总数
	 * @param int $m_uid 用户ID
	 * @param array $cd_ids 用户所属部门ID
	 */
	public function list_right_categories_count($m_uid, $cd_ids) {

		$str = array();
		$cd_ids = empty($cd_ids) ? array(0) : $cd_ids;
		$this->_field_sign_condi($str, "cd_id in (?)", $cd_ids);
		$sql = " (m_uid=? OR {$str[0]} OR is_all=?) AND status<? ";
		$data[] = $m_uid;
		foreach ($cd_ids as $id) {
			$data[] = $id;
		}
		$data[] = 1;
		$data[] = parent::STATUS_DELETE;

		$count = $this->_count_by_complex($sql, $data, 'tcr_id');

		return $count;
	}

	/**
	 * 物理删除文章权限
	 * @param array $ids 目录ID
	 */
	public function delete_real_by_category_ids ($ids) {

		return $this->_delete_real_by_conds(array('tc_id' => $ids));
	}

	/**
	 * 物理删除文章权限
	 * @param array $id 目录ID
	 */
	public function delete_real_by_category_id ($id) {

		return $this->_delete_real_by_conds(array('tc_id' => $id));
	}

	/**
	 * 按条件查找用户有权限查看的目录
	 * @param array $conds 查找条件
	 * @param int $page_option 分页
	 */
	public function list_category($conds, $page_option = array(), $orderby = array()) {

		$params = array();
		$sql = ' status<? AND ';
		$sql .= '( is_all=? ';
		$params[] = parent::STATUS_DELETE;
		$params[] = 1;

		if (isset($conds['m_uid'])) {
			$str = array();
			$this->_field_sign_condi($str, "m_uid in (?)", $conds['m_uid']);
			$attach[] = $str[0];
			foreach ($conds['m_uid'] as $uid) {
				$params[] = $uid;
			}
		}

		if (isset($conds['cd_id'])) {
			$str = array();
			$this->_field_sign_condi($str, "cd_id in (?)", $conds['cd_id']);
			$attach[] = $str[0];
			foreach ($conds['cd_id'] as $id) {
				$params[] = $id;
			}
		}

		if (!empty($attach)) {
			$sql .= ' OR ('.implode(' AND ', $attach).')';
		}
		$sql .= ')';
		$list = $this->_list_by_complex($sql, $params, $page_option, $orderby, 'tc_id, tcr_id');

		return $list;
	}

}
