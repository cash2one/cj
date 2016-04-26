<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午1:44
 */
namespace Stat\Model;

class StatCompanyModel extends AbstractModel {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据条件和时间统计付费公司
	 * @return array
	 */
	public function list_by_conds_cp($params, $page_option = array()) {

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
		if (!empty($page_option)) {
			$order_option = array('time' => 'DESC');
		} else {
			$order_option = array('time' => 'ASC');
		}

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where)."{$orderby}{$limit}", $where_params);
	}


	/**
	 * 根据时间统计公司
	 * @return array
	 */
	public function count_by_conds_cp($params) {

		$sql = "SELECT COUNT(*) FROM __TABLE__";
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

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * 昨天公司信息
	 * @param $params array 时间段
	 * @return array
	 */
	public function get_company($params) {

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

		return $this->_m->fetch_row($sql . ' WHERE ' . implode(' AND ', $where), $where_params);

	}
}