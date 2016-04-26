<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午1:44
 */
namespace Stat\Model;

class StatCompanyAdminerModel extends AbstractModel {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据日期和负责人查询记录
	 * @param array $date 日期
	 * @param array $adminer 负责人
	 * @param array $page_option 分页参数
	 * @return array|bool
	 */
	public function list_by_date_adminer($date, $adminer = array(), $page_option = array()) {

		$sql = "SELECT * FROM __TABLE__";

		// 设置条件
		$where = array(
			'time > ?',
			'time <= ?',
			'status < ?',
			'ca_id > ?'
		);

		$where_params = array(
			rstrtotime($date['s_time']),
			rstrtotime($date['e_time']) + 86400,
			$this->get_st_delete(),
			0
		);
		if (!empty($adminer)) {
			$where[] = 'ca_id IN (?)';
			$where_params[] = $adminer;
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
	 * 根据日期和负责人查询记录
	 * @param array $date 日期
	 * @param array $adminer 负责人
	 * @param array $page_option 分页参数
	 * @return array|bool
	 */
	public function count_by_date_adminer($date, $adminer) {

		$sql = "SELECT COUNT(*) FROM __TABLE__";

		// 设置条件
		$where = array(
			'time > ?',
			'time <= ?',
			'status < ?',
		);

		$where_params = array(
			rstrtotime($date['s_time']),
			rstrtotime($date['e_time']) + 86400,
			$this->get_st_delete(),
		);

		if (!empty($adminer)) {
			$where[] = 'ca_id IN (?)';
			$where_params[] = $adminer;
		}

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}


	/**
	 * 统计昨天公司信息
	 * @param $ep_id int 公司id
	 * @param $ca_id int 负责人id
	 */
	public function get_yesterday_record($date, $ca_id) {

		$sql = "SELECT * FROM __TABLE__";
		// 设置条件
		$where = array(
			'time > ?',
			'time <= ?',
			'status < ?',
			'ca_id = ?'
		);

		$where_params = array(
			rstrtotime($date['s_time']),
			rstrtotime($date['e_time']) + 86400,
			$this->get_st_delete(),
			$ca_id,
		);

		return $this->_m->fetch_row($sql . ' WHERE ' . implode(' AND ', $where), $where_params);

	}

}