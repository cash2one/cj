<?php
/**
 * EnterpriseProfileModel.class.php
 * $author$
 */

namespace Stat\Model;

class StatMemberAllModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 统计昨天公司人数
	 * @param $ep_id int 公司id
	 */
	public function count_old_member($ep_id) {

		$where = array(
			'status < ?',
			'ep_id = ?',
			'time > ?',
			'time <= ?'
		);
		$where_params = array(
			$this->get_st_delete(),
			$ep_id,
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')) - 86400,
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')),
		);

		return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);

	}

	/**
	 * 统计昨天单公司总人数
	 * @param $ep_id int 公司id
	 */
	public function get_old_member($ep_id) {

		$where = array(
			'status < ?',
			'ep_id = ?',
			'time > ?',
			'time <= ?'
		);
		$where_params = array(
			$this->get_st_delete(),
			$ep_id,
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')) - 2 * 86400,
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')) - 86400,
		);

		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);

	}

	/**
	 * 统计昨天公司人数
	 * @param $ep_id int 公司id
	 */
	public function get_yesterday_member($params) {

		$sql = "SELECT * FROM __TABLE__";
		// 设置条件
		$where = array(
			'time > ?',
			'time <= ?',
			'status < ?',
		);

		$s_time = rstrtotime($params['s_time']);
		$e_time = rstrtotime($params['e_time']);
		$where_params = array(
			$s_time,
			$e_time,
			$this->get_st_delete(),
		);

		return $this->_m->fetch_row($sql . ' WHERE ' . implode(' AND ', $where), $where_params);

	}

	/**
	 * 根据条件和时间统记数据
	 * @return array
	 */
	public function list_by_conds_time($params, $page_option = array()) {

		$sql = "SELECT * FROM __TABLE__";
		// 设置条件
		$where = array(
			'time > ?',
			'time <= ?',
			'status < ?',
		);

		$s_time = rstrtotime($params['s_time']);
		$e_time = rstrtotime($params['e_time']) + 86400;
		$where_params = array(
			$s_time,
			$e_time,
			$this->get_st_delete(),
		);

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$order_option = array('time' => 'DESC');

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}
		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where)."{$orderby}{$limit}", $where_params);
	}

	/**
	 * 统计昨天公司人数
	 * @param $ep_id int 公司id
	 */
	public function count_all_member() {

		$where = array(
			'status < ?',
			'time > ?',
			'time <= ?'
		);
		$where_params = array(
			$this->get_st_delete(),
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')) - 86400,
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')),
		);

		$result = $this->_m->result("SELECT SUM(`all`) FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);
		if ($result) {
			return $result;
		}
		return 0;
	}

	/**
	 * 统计昨天公司新增人数
	 * @param $ep_id int 公司id
	 */
	public function count_new_member() {

		$where = array(
			'status < ?',
			'time > ?',
			'time <= ?'
		);
		$where_params = array(
			$this->get_st_delete(),
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')) - 86400,
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')),
		);

		$result = $this->_m->result("SELECT SUM(`add`) FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);
		if ($result) {
			return $result;
		}
		return 0;
	}

	/*
	 * 根据公司id和时间获得数据
	 * @param $epid 公司id
	 * @param $cond 过滤时间
	 * return array
	 */
	public function list_by_conds_lastday($ep_id,$cond) {

		$where = array(
			'ep_id = ?',
			'time > ?',
			'time <= ?',
			'status < ?',
		);

		$where_params = array(
			$ep_id,
			$cond['s_time'],
			$cond['e_time'],
			$this->get_st_delete(),
		);
		$sql = "SELECT * FROM __TABLE__  WHERE ".implode(' AND ',$where);

	    return $this->_m->fetch_array($sql,$where_params);

	}


	/*
	 * 根据公司id和时间获得数据
	 * @param $epid 公司id
	 * @param $cond 过滤时间
	 * return array
	 */
	public function get_by_conds_lastday($ep_id,$cond) {

		$where = array(
			'ep_id = ?',
			'time > ?',
			'time <= ?',
			'status < ?',
		);

		$where_params = array(
			$ep_id,
			$cond['s_time'],
			$cond['e_time'],
			$this->get_st_delete(),
		);
		$sql = "SELECT * FROM __TABLE__  WHERE ".implode(' AND ',$where);

		return $this->_m->fetch_row($sql,$where_params);

	}

	/*
	 * 根据公司id获得单个企业数据详情
	 * @param $start 搜索开始时间
	 * @param $end 搜索结束时间
	 * @param $ep_id 公司id
	 * @param $page_option 分页配置
	 * @return array|bool
	 */
	public function list_by_conds_detail($start = 0, $end = 0, $ep_id, $page_option) {

		$where = array(
			'ep_id = ?',
			'status < ?',
		);
		$where_params = array(
			$ep_id,
			$this->get_st_delete(),
		);
		if (!empty($start)) {
			$where[] = 'time > ?';
			$where_params[] = $start;
		}
		if (!empty($end)) {
			$where[] = 'time <= ?';
			$where_params[] = $end;
		}

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$order_option = array('time' => 'DESC');

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		$sql = "SELECT * FROM __TABLE__ WHERE ".implode(' AND ',$where)."{$orderby}{$limit}";

		return $this->_m->fetch_array($sql,$where_params);
	}

	/*
	 * 统计数据详情汇总
	 * @param $ep_id 公司id
	 * @return array|bool
	 */
	public function count_detail($ep_id){

		$where = array(
			'ep_id = ?',
			'status < ?',
		);
		$where_params = array(
			$ep_id,
			$this->get_st_delete(),
		);

		$column = "SUM(`add`),SUM(`attention`),SUM(`unattention`),SUM(`all`)";

		$sql = $sql = "SELECT {$column} FROM __TABLE__  WHERE ".implode(' AND ',$where);

		return $this->_m->fetch_array($sql,$where_params);
	}

	/**
	 * 根据时间查询
	 * @param $start
	 * @param $end
	 * @param $page_option
	 * @return array|bool
	 */
	public function list_by_time($start = 0, $end = 0, $ep_id) {

		$sql = "SELECT * FROM __TABLE__";

		// 设置条件
		$where = array(
			'ep_id = ?',
			'status < ?',
		);
		$where_params = array(
			$ep_id,
			$this->get_st_delete(),
		);
		if (!empty($start)) {
			$where[] = 'time > ?';
			$where_params[] = $start;
		}
		if (!empty($end)) {
			$where[] = 'time <= ?';
			$where_params[] = $end;
		}

		$orderby = "ORDER BY `time` ASC";

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$orderby}", $where_params);
	}
	/*
	 * 统计数据active
	 * @param $ep_id 公司id
	 * @return array|bool
	 */
	public function count_active($ep_id){

		$where = array(
			'ep_id = ?',
			'status < ?',
		);
		$where_params = array(
			$ep_id,
			$this->get_st_delete(),
		);

		$column = "SUM(`active_count`)";

		$sql = $sql = "SELECT {$column} FROM  cy_stat_active  WHERE ".implode(' AND ',$where);

		return $this->_m->fetch_array($sql,$where_params);
	}

	/**
	 * 统计负责人公司数量
	 * @param $date array 日期
	 * @param $ep_id array 公司id
	 * @return array
	 */
	public function count_all_company_epid($date, $ep_id) {

		$sql = "SELECT COUNT(*) FROM __TABLE__";
		// 设置条件
		$where = array(
			'time > ?',
			'time <= ?',
			'status < ?',
			'ep_id IN (?)'
		);

		$where_params = array(
			rstrtotime($date['s_time']),
			rstrtotime($date['e_time']) + 86400,
			$this->get_st_delete(),
			$ep_id
		);

		return $this->_m->result($sql . " WHERE " . implode(' AND ', $where), $where_params);
	}

	/**
	 * 统计负责人公司数量
	 * @param $date array 日期
	 * @param $ep_id array 公司id
	 * @return array
	 */
	public function list_all_company_epid($date, $ep_id) {

		$sql = "SELECT * FROM __TABLE__";
		// 设置条件
		$where = array(
			'time > ?',
			'time <= ?',
			'status < ?',
			'ep_id IN (?)'
		);

		$where_params = array(
			rstrtotime($date['s_time']),
			rstrtotime($date['e_time']) + 86400,
			$this->get_st_delete(),
			$ep_id
		);

		return $this->_m->fetch_array($sql . " WHERE " . implode(' AND ', $where), $where_params);
	}

	/**
	 * 统计负责人公司人员数量
	 * @param $date array 日期
	 * @param $ep_id array 公司id
	 * @return array
	 */
	public function sum_all_member_epid($date, $ep_id) {

		$sql = "SELECT * FROM __TABLE__";
		// 设置条件
		$where = array(
			'time > ?',
			'time <= ?',
			'status < ?',
			'ep_id IN (?)'
		);

		$where_params = array(
			rstrtotime($date['s_time']),
			rstrtotime($date['e_time']) + 86400,
			$this->get_st_delete(),
			$ep_id
		);

		return $this->_m->result("SELECT SUM(`all`) FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);
	}
	/**
	 * 根据时间和公司id统计
	 * @param int $start
	 * @param int $end
	 * @return array|bool
	 */
	public function count_by_time_epid($start = 0, $end = 0, $ep_id) {

		$sql = "SELECT COUNT(*) FROM __TABLE__";

		// 设置条件
		$where = array(
			'ep_id = ?',
			'status < ?',
		);
		$where_params = array(
			$ep_id,
			$this->get_st_delete(),
		);
		if (!empty($start)) {
			$where[] = 'time > ?';
			$where_params[] = $start;
		}
		if (!empty($end)) {
			$where[] = 'time <= ?';
			$where_params[] = $end;
		}

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}
}
