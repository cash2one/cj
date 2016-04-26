<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/29
 * Time: 下午4:11
 */

namespace Stat\Model;

class StatPluginDailyModel extends AbstractModel {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据时间范围 统计 应用标识的数据
	 * @param int $start_time 起始时间
	 * @param int $end_time 结束时间
	 * @param string $identifier 应用标识
	 * @param $field
	 * @return array
	 */
	public function stat_plugin_by_identifier_time($start_time, $end_time, $identifier, $field) {

		$sql = "SELECT {$field} FROM __TABLE__";

		$where = array(
			'status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);
		if (!empty($start_time)) {
			$where[] = 'time > ?';
			$where_params[] = $start_time;
		}
		if (!empty($end_time)) {
			$where[] = 'time <= ?';
			$where_params[] = $end_time;
		}
		if (!empty($identifier)) {
			$where[] = 'pg_identifier = ?';
			$where_params[] = $identifier;
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * 计算应用有多少企业安装
	 * @param $pg_identifier
	 * @return array
	 */
	public function count_install_plugin_epid($pg_identifier) {

		$sql = "SELECT COUNT(DISTINCT ep_id) from __TABLE__";

		$where = array(
			'status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);
		if (!empty($pg_identifier)) {
			$where[] = 'pg_identifier = ?';
			$where_params[] = $pg_identifier;
		}

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}


	/**
	 * 负责人的每日活跃公司
	 * @param $date array 日期
	 * @param $ca_id int 负责人id
	 * @return array
	 */
	public function count_adminer_active_company_date($date, $ep_id) {

		$sql = "SELECT COUNT(*) FROM __TABLE__";

		// 设置条件
		$where = array(
			'status < ?',
			'ep_id IN (?)',
			'count_all > ?',
		);
		$where_params = array(
			$this->get_st_delete(),
			$ep_id,
			0,
		);

		if (!empty($date['s_time'])) {
			$where[] = 'time > ?';
			$where_params[] = rstrtotime($date['s_time']);
		}
		if (!empty($date['e_time'])) {
			$where[] = 'time <= ?';
			$where_params[] = rstrtotime($date['e_time']) + 86400;
		}

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * 统计流失公司的id
	 * @param $date
	 * @return array
	 */
	public function list_lose_company($date, $ep_list = array()) {

		$where = array(
			'status < ?',
			'time > ?',
			'time <= ?',
		);
		$where_params = array(
			$this->get_st_delete(),
			rstrtotime($date['s_time']),
			rstrtotime($date['e_time']) + 86400,
		);
		if (!empty($ep_list)) {
			$where[] = 'ep_id IN (?)';
			$where_params[] = $ep_list;
		}
		return $this->_m->fetch_array("SELECT ep_id FROM __TABLE__  WHERE " . implode(' AND ', $where)." GROUP BY ep_id HAVING SUM(`count_index`)<1", $where_params);

	}

	/**
	 * 统计流失公司的id
	 * @param $date
	 * @return array
	 */
	public function list_no_adminer_lose_company($date) {

		$where = array(
			'status < ?',
			'time > ?',
			'time <= ?',
		);
		$where_params = array(
			$this->get_st_delete(),
			rstrtotime($date['s_time']),
			rstrtotime($date['e_time']) + 86400,
		);
		if (!empty($ep_list)) {
			$where[] = 'ep_id IN (?)';
			$where_params[] = $ep_list;
		}
		return $this->_m->fetch_array("SELECT ep_id FROM __TABLE__  WHERE " . implode(' AND ', $where)." GROUP BY ep_id HAVING SUM(`count_index`)<1", $where_params);

	}
	/**
	 * 公司的总数据数量
	 * @param $date array 日期
	 * @param $ep_id int 公司id
	 * @return array
	 */
	public function count_lose_company($date, $ep_id) {

		$where = array(
			'status < ?',
			'ep_id = ?'
		);
		$where_params = array(
			$this->get_st_delete(),
			$ep_id
		);

		if (!empty($date['s_time'])) {
			$where[] = 'time > ?';
			$where_params[] = rstrtotime($date['s_time']);
		}

		if (!empty($date['e_time'])) {
			$where[] = 'time <= ?';
			$where_params[] = rstrtotime($date['e_time']) + 86400;
		}

		return $this->_m->result("SELECT SUM(`count_index`) FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);
	}

	/**
	 * 统计激活用户数量
	 * @param array $date 统计日期
	 * @param array $ep_list 公司id
	 * @return array
	 */
	public function count_by_conds_activation($date, $ep_list = array()) {

		$where = array(
			'status < ?',
			'is_activation = ?',
		);
		$where_params = array(
			$this->get_st_delete(),
			1,
		);
		if (!empty($date['s_time'])) {
			$where[] = 'time > ?';
			$where_params[] = rstrtotime($date['s_time']);
		}

		if (!empty($date['e_time'])) {
			$where[] = 'time <= ?';
			$where_params[] = rstrtotime($date['e_time']) + 86400;
		}

		if (!empty($ep_list)) {
			$where[] = 'ep_id IN (?)';
			$where_params[] = $ep_list;
		}

		return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);
	}



	/*
	 * 根据公司id获得数据
	 * @param $ep_id 公司id
	 * @param $cond 时间参数
	 * @return array
	 */
	public function get_by_cond_lastday($ep_id,$cond){

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
		$sql = "SELECT SUM(`count_index`)count_index,SUM(`count_all`)count_all FROM __TABLE__  WHERE ".implode(' AND ',$where);

		return $this->_m->fetch_row($sql,$where_params);

	}

	/*
	 * 根据公司id和时间获取应用安装数
	 * @param $ep_id $公司id
	 * @param $cond 时间参数
	 * @return int
	 */
	public function get_by_cond_install_number($ep_id,$cond) {

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

		$sql = "SELECT COUNT(DISTINCT `pg_identifier`)`pg_identifier` FROM __TABLE__  WHERE ".implode(' AND ',$where);

		return $this->_m->fetch_row($sql,$where_params);
	}

	/*
	 * 根据公司id和时间获取每天应用安装数
	 * @param $eo_id 公司id
	 * @return array&int
	 */
	public function get_by_cond_day_install_number($ep_id) {

		$where = array(
			'ep_id = ?',
			'status < ?',
		);

		$where_params = array(
			$ep_id,
			$this->get_st_delete(),
		);
		$orderby = "ORDER BY `time` DESC";
		$groupby = "GROUP BY `time`";

		$sql = "SELECT COUNT(DISTINCT `pg_identifier`)pg_identifier FROM __TABLE__  WHERE ".implode(' AND ',$where)."{$groupby}{$orderby}";

		return $this->_m->fetch_array($sql,$where_params);
	}

	/*
	 * 根据公司id获得数据详情列表
	 * @param $start_time 开始时间
	 * @param $end_time 结束时间
	 * @param $ep_id 公司id
	 * @param $page_option 分页参数
	 * @return array
	 */
	public function list_by_epid_detail($start_time, $end_time, $ep_id, $page_option = array()){

		$where = array(
			'ep_id = ?',
			'status < ?',
		);
		$where_params = array(
			$ep_id,
			$this->get_st_delete(),
		);

		if (!empty($start_time)) {
			$where[] = 'time > ?';
			$where_params[] = $start_time;
		}
		if (!empty($end_time)) {
			$where[] = 'time <= ?';
			$where_params[] = $end_time;
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
		$groupby = "GROUP BY `time`";
		$column = "SUM(count_index)count_index,SUM(count_all)count_all,SUM(active_staff)active_staff,time";

		$sql = "SELECT {$column} FROM __TABLE__ WHERE ".implode(' AND ',$where)."{$groupby}{$orderby}{$limit}";

		return $this->_m->fetch_array($sql,$where_params);
	}

	/*
	 * 根据公司id统计数据总数
	 * @param $start_time 开始时间
	 * @param $end_time 结束时间
	 * @param $ep_id 公司id
	 * @return int
	 */
	public function count_by_epid_view($start_time, $end_time, $ep_id){

		$where = array(
			'ep_id = ?',
			'status < ?',
		);
		$where_params = array(
			$ep_id,
			$this->get_st_delete(),
		);
		if (!empty($start_time)) {
			$where[] = 'time > ?';
			$where_params[] = $start_time;
		}
		if (!empty($end_time)) {
			$where[] = 'time <= ?';
			$where_params[] = $end_time;
		}


		$sql = "SELECT COUNT(DISTINCT `time`) FROM __TABLE__ WHERE ".implode(' AND ',$where);

		return $this->_m->result($sql,$where_params);
	}

	/**
	 * 根据时间查询
	 * @param $start
	 * @param $end
	 * @param $page_option
	 * @return array|bool
	 */
	public function list_by_time($start = 0, $end = 0, $ep_id) {

		$sql = "SELECT SUM(`count_index`)count_index,SUM(`count_all`)count_all,`time` FROM __TABLE__";

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
		$groupby = "GROUP BY `time`";

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$groupby}{$orderby}", $where_params);
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

	/**
	 * 根据时间范围 统计 应用标识的数据
	 * @param int $start_time 起始时间
	 * @param int $end_time 结束时间
	 * @param string $identifier 应用标识
	 * @param $field
	 * @return array
	 */
	public function plugin_by_identifier_time($start_time, $end_time, $ep_id, $identifier, $field, $page_option) {

		$sql = "SELECT {$field} FROM __TABLE__";

		$where = array(
			'status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),

		);
		if(!empty($ep_id)) {
			$where[] = 'ep_id = ?';
			$where_params[] = $ep_id;
		}
		if(!empty($identifier)) {
			$where[] = 'pg_identifier = ?';
			$where_params[] = $identifier;
		}
		if (!empty($start_time)) {
			$where[] = 'time > ?';
			$where_params[] = $start_time;
		}
		if (!empty($end_time)) {
			$where[] = 'time <= ?';
			$where_params[] = $end_time;
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

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where)."{$orderby}{$limit}", $where_params);
	}

	/**
	 * 根据时间和公司id统计
	 * @param int $start
	 * @param int $end
	 * @return array|bool
	 */
	public function count_by_time_epid_identifier($start = 0, $end = 0, $ep_id, $identifier) {

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

		if(!empty($identifier)) {
			$where[] = 'pg_identifier = ?';
			$where_params[] = $identifier;
		}
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


	/**
	 * 统计主数据大于0的公司数
	 * @return array
	 */
	public function count_active_company($date) {

		$where = array(
			'status < ?',
			'count_all > ?',
			'time > ?',
			'time <= ?'
		);
		$s_time = rstrtotime($date['s_time']);
		$e_time = rstrtotime($date['e_time']) + 86400;
		$where_params = array(
			$this->get_st_delete(),
			0,
			$s_time,
			$e_time,
		);

		return $this->_m->fetch_array("SELECT ep_id FROM __TABLE__  WHERE " . implode(' AND ', $where)." GROUP BY ep_id HAVING SUM(`count_all`)>0", $where_params);
	}

}