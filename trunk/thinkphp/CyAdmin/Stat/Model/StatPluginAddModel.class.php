<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/25
 * Time: 上午11:18
 */

namespace Stat\Model;

class StatPluginAddModel extends AbstractModel {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据最新在安装的应用 左链 查询企业信息
	 * @param $start
	 * @param $end
	 * @param $identifier
	 * @param $page_option
	 * @return array|bool
	 */
	public function list_by_time_identifier_join_enterprise_profile($start, $end, $identifier, $page_option) {

		$p_field = array(
			'p.ep_name',
			'p.ep_mobilephone',
			'p.ep_industry',
			'p.ep_customer_level',
			'p.customer_status',
			'p.ep_companysize',
			'p.ep_ref',
			'p.ca_id',
			'p.ep_wxcorpid',
			'p.ep_created',
			'p.ep_updated',
			'a.ep_id',
			'a.pg_name',
			'a.pg_identifier',
		);
		$filed = implode(',', $p_field);
		$sql = "SELECT {$filed} FROM __TABLE__ AS a LEFT JOIN cy_enterprise_profile AS p ON a.ep_id = p.ep_id";

		// 设置条件
		$where = array(
			'a.status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);
		if (!empty($identifier)) {
			$where[] = 'a.pg_identifier = ?';
			$where_params[] = $identifier;
		}
		if (!empty($start)) {
			$where[] = 'a.time > ?';
			$where_params[] = $start;
		}
		if (!empty($end)) {
			$where[] = 'a.time <= ?';
			$where_params[] = $end;
		}

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$order_option = array('a.time' => 'DESC');

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$orderby}{$limit}", $where_params);
	}

	/**
	 * 统计时间段里的安装应用的企业数
	 * @param $start
	 * @param $end
	 * @param $identifier
	 * @return array
	 */
	public function count_new_install_ep($start, $end, $identifier) {

		$sql = "SELECT COUNT(*) FROM __TABLE__";
		// 设置条件
		$where = array(
			'status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);
		if (!empty($identifier)) {
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
	 * 根据最新在安装的时间 左链 查询企业信息
	 * @param $start
	 * @param $end
	 * @param $page_option
	 * @return array|bool
	 */
	public function list_by_time_join_enterprise_profile($start, $end, $page_option) {

		$p_field = array(
			'p.ep_name',
			'p.ep_mobilephone',
			'p.ep_industry',
			'p.ep_customer_level',
			'p.customer_status',
			'p.ep_companysize',
			'p.ep_ref',
			'p.ca_id',
			'p.ep_wxcorpid',
			'p.ep_created',
			'p.ep_updated',
			'a.pg_name',
			'a.time',
		);
		$filed = implode(',', $p_field);
		$sql = "SELECT {$filed} FROM __TABLE__ AS a LEFT JOIN cy_enterprise_profile AS p ON a.ep_id = p.ep_id";

		// 设置条件
		$where = array(
			'a.status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);
		if (!empty($start)) {
			$where[] = 'a.time > ?';
			$where_params[] = $start;
		}
		if (!empty($end)) {
			$where[] = 'a.time <= ?';
			$where_params[] = $end;
		}

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$order_option = array('a.time' => 'DESC');

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$orderby}{$limit}", $where_params);
	}

	/**
	 * 统计 在时间条件里安装的应用数
	 * @param $start
	 * @param $end
	 * @return array
	 */
	public function count_new_install_pliugin($start, $end) {

		$sql = "SELECT COUNT(*) FROM __TABLE__";

		// 设置条件
		$where = array(
			'status < ?',
		);
		$where_params = array(
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
	 * 根据公司id获取安装的应用列表
	 * @param $ep_id
	 */
	public function list_by_epid ($ep_id) {

		$sql = "SELECT * FROM __TABLE__";

		// 设置条件
		$where = array(
			'status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);

		if (!empty($ep_id)) {
			$where[] = 'ep_id = ?';
			$where_params[] = $ep_id;
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);

	}
}