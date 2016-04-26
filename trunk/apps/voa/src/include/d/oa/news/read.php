<?php

/**
 * voa_d_oa_news_read
 * 文章阅读记录表
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_d_oa_news_read extends voa_d_abstruct {

	//初始化 
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.news_read';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'nre_id';
		parent::__construct(null);
	}

	/**
	 * 根据新闻公告ID数组查找已读人数
	 * @param array $ne_ids
	 * @return array
	 */
	public function list_read_numbers($ne_ids) {
		$data = array();
		$data[] = parent::STATUS_DELETE;
		$find_cd_sql = '';
		if ($ne_ids) {
			$str = array();
			$this->_field_sign_condi($str, "ne_id in (?)", $ne_ids);
			foreach ($ne_ids as $ne_id) {
				$data[] = $ne_id;
			}
			$find_cd_sql .= ' AND ' . $str[0];
		}
		$sql = "  status<? $find_cd_sql GROUP BY ne_id";
		$list = $this->_list_by_complex($sql, $data, array(), array(), 'count(ne_id) as number, ne_id, nre_id');
		if ($list) {
			foreach ($list as $k => $v) {
				unset($list[$k]);
				$list[$v['ne_id']] = $v['number'];
			}
		}

		return $list;
	}

	/**
	 * 根据新闻公告ID数组查找已读人员列表
	 * @param int $ne_id
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function list_read_users($ne_id, $start, $limit) {
		return $this->list_by_conds(array('ne_id' => $ne_id), array($start, $limit), array('created' => 'DESC'));
	}

	/**
	 * 根据新闻公告ID数组查找已读人员总数
	 * @param int $ne_id
	 * @return number
	 */
	public function count_read_users($ne_id) {
		return $this->count_by_conds(array('ne_id' => $ne_id));
	}

	/**
	 * 统计未读人数
	 * @param $ne_id
	 * @return array|int|number
	 * @throws service_exception
	 */
	public function count_users($ne_id) {

		$result = array();
		$d_user_number = null;
		$news = new voa_d_oa_news_right();
		$read = $news->list_by_conds(array('ne_id' => $ne_id));
		//当所有人可读时
		$num_department = array_column($read, 'cd_id');
		$num_member = array_column($read, 'm_uid');
		$read = array_values($read);
		$d_member = new voa_d_oa_member();
		$d_member_department = new voa_d_oa_member_department();
		if ($read[0]['is_all'] == 1) {
			$result = $d_member->count_all();
		} else {
			$this->__famart_from(array_column($read, 'cd_id'), $num_department);
			$this->__famart_from(array_column($read, 'm_uid'), $num_member);
			$conditions = array(
				'cd_id' => array($num_department, 'in'),
			);
			//获取可阅读权限部门的人
			$d_user_number = $d_member_department::fetch_all_by_conditions($conditions, '');
			//合并
			$d_news_muid = array_column($d_user_number, 'm_uid');

			$news_muid = array_unique(array_merge($num_member, $d_news_muid));

			$result = count($news_muid);
		}

		return $result;
	}

	//获取可阅读人员列表
	public function get_read_users($ne_id) {

		$users = array();
		$news = new voa_d_oa_news_right();
		$read = $news->list_by_conds(array('ne_id' => $ne_id));
		$read = array_values($read);
		$d_member = new voa_d_oa_member();
		$d_member_department = new voa_d_oa_member_department();
		if ($read[0]['is_all'] == 1) {
			$result = $d_member->fetch_all();
			$users = array_column($result, 'm_uid');
		} else {
			$num_department = array_column($read, 'cd_id');
			$num_member = array_column($read, 'm_uid');
			$this->__famart_from(array_column($read, 'cd_id'), $num_department);
			$this->__famart_from(array_column($read, 'm_uid'), $num_member);
			$conditions = array(
				'cd_id' => array($num_department, 'in'),
			);
			//获取可阅读权限部门的人
			$d_user_number = $d_member_department::fetch_all_by_conditions($conditions, '');
			//合并
			$d_news_muid = array_column($d_user_number, 'm_uid');

			$users = array_merge($num_member, $d_news_muid);
		}

		return $users;
	}

	/**
	 * 物理删除阅读情况记录
	 * @param $conds
	 * @return bool
	 * @throws service_exception
	 */
	public function delete_real_records_by_conds($conds) {

		return $this->_delete_real_by_conds($conds);
	}

	private function __famart_from($request, &$esult) {
		foreach ($request as $key => $val) {
			if ($val == 0) {
				unset($request[$key]);
			}
		}
		$esult = $request;

		return true;
	}

	public function delete_by_ne_uid($ne_id, array $conds) {

		$m_uids = implode(',', $conds);
		$where = "ne_id = {$ne_id} AND m_uid NOT IN({$m_uids})";
		$set = $this->_prefield . 'deleted = ' . startup_env::get('timestamp') . ',' . $this->_prefield . 'status = ' . self::STATUS_DELETE;
		$sql = "update {$this->_table} set {$set} where {$where}";

		return $this->_execute($sql);
	}
}

