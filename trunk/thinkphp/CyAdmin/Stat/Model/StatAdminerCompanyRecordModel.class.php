<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午1:44
 */
namespace Stat\Model;

class StatAdminerCompanyRecordModel extends AbstractModel {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据时间和管理员查询
	 * @param $date array 日期
	 * @param $page_option array 分页参数
	 * @return array
	 */
	public function list_by_time_adminer($date, $ca_id, $page_option) {

		// 设置条件
		$where = array(
			'a.status < ?',
		);

		$where_params = array(
			$this->get_st_delete(),
		);
		if (!empty($date['s_time'])) {
			$where[] = 'a.time > ?';
			$where_params[] = rstrtotime($date['s_time']);
		}

		if (!empty($date['e_time'])) {
			$where[] = 'a.time <= ?';
			$where_params[] = rstrtotime($date['e_time']) + 86400;
		}
		if (!empty($ca_id)) {
			$where[] = 'a.ca_id IN (?)';
			$where_params[] = $ca_id;
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
		return $this->_m->fetch_array('SELECT a.*,b.* FROM __TABLE__ AS a LEFT JOIN `cy_enterprise_profile` AS b ON a.ep_id=b.ep_id WHERE ' . implode(' AND ', $where)."{$orderby}{$limit}", $where_params);
	}

	/**
	 * 根据时间和管理员查询
	 * @param $date array 日期
	 * @param $page_option array 分页参数
	 * @return array
	 */
	public function count_by_time_adminer($date, $ca_id) {

		// 设置条件
		$where = array(
			'a.status < ?',
		);

		$where_params = array(
			$this->get_st_delete(),
		);
		if (!empty($date['s_time'])) {
			$where[] = 'a.time > ?';
			$where_params[] = rstrtotime($date['s_time']);
		}

		if (!empty($date['e_time'])) {
			$where[] = 'a.time <= ?';
			$where_params[] = rstrtotime($date['e_time']) + 86400;
		}
		if (!empty($ca_id)) {
			$where[] = 'a.ca_id IN (?)';
			$where_params[] = $ca_id;
		}

		return $this->_m->result('SELECT COUNT(*) FROM __TABLE__ AS a LEFT JOIN `cy_enterprise_profile` AS b ON a.ep_id=b.ep_id WHERE ' . implode(' AND ', $where), $where_params);
	}
}