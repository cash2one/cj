<?php
/**
 * ActivityModel.class.php
 * $author$
 */

namespace Activity\Model;

class ActivityModel extends AbstractModel {

	const CANCE_REG = 3; //同意取消
	// 构造方法
	public function __construct() {

		parent::__construct();

		$this->prefield = '';
	}

	/**
	 * 根据条件查询活动列表
	 * @param $conds
	 * @param $page_option
	 * @param $order_option
	 * @param string $fields
	 * @return array|bool
	 */
	public function fetch_all_by_status($conds, $page_option, $order_option, $fields = "*") {

		$params = array();

		// 条件组装
		foreach($conds as $field => $_v) {
			$wheres[] = "{$field}";
			$params[] = $_v;
		}

		// 状态条件
		$wheres[] = "`{$this->prefield}status`<?";
		$params[] = $this->get_st_delete();

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		$sql = "SELECT {$fields} FROM __TABLE__ WHERE ".implode(' AND ', $wheres)."{$orderby}{$limit}";

		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 获取详情 优化字段
	 * @param $acid
	 * @param string $field
	 * @return array
	 */
	public function get_detail_by_acid($acid, $field = "*") {

		$sql = "SELECT {$field} FROM __TABLE__ WHERE acid=? AND status<? LIMIT 1";
		$params = array($acid, $this->get_st_delete());

		return $this->_m->fetch_row($sql, $params);
	}

	/**
	 * 获取指定用户发起的活动
	 * @param int $m_uid
	 * @param string $fields
	 * @param int $start
	 * @param int $limit
	 * @return array
	 * */
	public function list_by_muid($m_uid, $fields, $start, $limit){

		$sql = "SELECT {$fields} FROM __TABLE__ WHERE m_uid=? AND `status`<? ORDER BY `updated` DESC LIMIT {$start},{$limit}";
		$params = array($m_uid, $this->get_st_delete());

		return $this->_m->fetch_array($sql,$params);
	}

	/**
	 * 获取指定用户参加的活动列表
	 * @param int $m_uid
	 * @param int $start
	 * @param int $limit
	 * @return array
	 * */
	public function join_list_by_muid($m_uid, $start, $limit){
		$sql = "SELECT a.acid, a.title, a.uname, a.start_time, a.end_time, a.updated FROM __TABLE__ a
				LEFT JOIN oa_activity_partake p ON p.acid=a.acid WHERE p.m_uid=? AND p.type<? AND a.status<?
				ORDER BY a.updated DESC LIMIT {$start}, {$limit}";
		$params = array($m_uid, self::CANCE_REG, $this->get_st_delete());

		return $this->_m->fetch_array($sql,$params);
	}
}
