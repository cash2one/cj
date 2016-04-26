<?php
/**
 * voa_d_oa_news_right
 * 文章
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */

class voa_d_oa_news_right extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.news_right';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'nri_id';
		parent::__construct(null);
	}

	/**
	 * 取得一篇公告的权限（剔除所有人可见权限）
	 * @param int $ne_id
	 * @return array
	 */
	public  function list_rights_for_single_news($ne_id) {
		$rights = $this->list_by_conds(array('ne_id' => $ne_id));
		//将is_all=1的选项剔除
		if (!empty($rights)) {
			foreach ($rights as $k => $right) {
				if ($right['is_all'] == 1){
					unset($rights[$k]);
				}
			}
		}

		return $rights;
	}

	/**
	 * 取得单个用户有阅读权限的公告id及自己发布的id
	 * @param int $nca_id
	 * @param int $m_uid
	 * @param int $start
	 * @param int $limit
	 * @return boolean|multitype:
	 */
	public  function list_rights_for_single_user($nca_id, $m_uid) {
		$cd_ids = $this->get_department_ids($m_uid);
		$str = '';
		if (!empty($cd_ids)) {
			$str = " OR cd_id in(".implode(',', $cd_ids).")";
		}
		$where = " (is_all=1 OR (m_uid=$m_uid".$str.")) AND nca_id=$nca_id";
		$sql = "SELECT DISTINCT(ne_id) FROM $this->_table WHERE".$where;
		// 执行
		$sth = null;
		if ($this->_execute($sql, $this->_bind_params, $sth)) {
			// 读取数据
			if (!$list = $sth->fetchAll(PDO::FETCH_ASSOC)) {
				return false;
			}
			return $list;
		}

		return false;
	}

	/**
	 * 判断用户是否有阅读某篇公告的权限
	 * @param int $nca_id
	 * @param int $m_uid
	 * @param int $start
	 * @param int $limit
	 * @return boolean|multitype:
	 */
	public  function confirm_right_for_user($ne_id, $m_uid) {
		$cd_ids = $this->get_department_ids($m_uid);
		$str = '';
		if (!empty($cd_ids)) {
			$str = " OR cd_id in(".implode(',', $cd_ids).")";
		}
		$where = " (is_all=1 OR (m_uid=$m_uid".$str.")) AND ne_id=$ne_id";
		$sql = "SELECT * FROM $this->_table WHERE".$where;

		// 执行
		$sth = null;
		if ($this->_execute($sql, $this->_bind_params, $sth)) {
			// 读取数据
			if (!$list = $sth->fetchAll(PDO::FETCH_ASSOC)) {
				return false;
			}

			return $list;
		}

		return false;
	}

	/**
	 * 物理删除权限记录
	 * @param array $conds
	 */
	public  function delete_real_records_by_conds($conds) {
		return $this->_delete_real_by_conds($conds);
	}

	/**
	 * 找到指定用户所关联的部门ID
	 * @param number $m_uid 用户id
	 * @return array $ids 部门ID
	 */
	public  function get_department_ids($m_uid) {

		$department = new voa_d_oa_member_department();
		$ids = $department->fetch_all_by_uid($m_uid);

		$all = $this->_get_all_departments($ids);
		$new = array();
		$new = array_flip(array_flip($all));
		if (!empty($new)) {
			foreach ($new as $k => $v) {
				if ($v ==0){
					unset($new[$k]);
				}
			}
		}

		return $new;
	}

	private function _get_all_departments($cd_ids) {

		$d_departments = new voa_d_oa_common_department();
		$departments = $d_departments->fetch_all();
		$departments_ids = array_column($departments, 'cd_upid', 'cd_id');
		$all = $cd_ids;
		$this->__get_parents($cd_ids, $departments_ids, $all);

		return $all;
	}

	private function __get_parents($cd_ids, $departments_ids, &$all){
		$temp = array();
		$temp = array_intersect_key($departments_ids,$cd_ids);
		if (!empty($temp)){
			$all = array_merge($all, $temp);
			self::__get_parents(array_flip($temp), $departments_ids, $all);
		}
	}

}

