<?php
/**
 * EnterpriseProfileModel.class.php
 * $author$
 */

namespace Stat\Model;

class StatPluginAllDataModel extends AbstractModel {

	/** 总数据 */
	const TYPE_ALL_DATA = 0;
	/** 主数据 */
	const TYPE_MAIN_DATA = 1;
	/** 总的公司(数据库数据) */
	const ALL_EPID = 0;

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 统计总数据大于0的公司数
	 * @return array
	 */
	public function count_active_company($date) {

		$where = array(
			'status < ?',
			'count_index > ?',
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

		return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);

	}

	/**
	 * 根据条件和时间统记数据
	 * @return array
	 */
	public function list_by_conds_time($params, $page_option) {

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
		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where)."{$orderby}{$page_option}", $where_params);
	}

	/**
	 * 根据时间查询
	 * @param $start
	 * @param $end
	 * @param $page_option
	 * @return array|bool
	 */
	public function list_by_time($start = 0, $end = 0, $page_option, $order = 'DESC') {

		$sql = "SELECT * FROM __TABLE__";

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

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		$order_option = array('time' => 'DESC');
		if ($order == 'ASC') {
			$order_option = array('time' => 'ASC');
		}
		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$orderby}{$limit}", $where_params);
	}

	/**
	 * 根据时间统计
	 * @param int $start
	 * @param int $end
	 * @return array|bool
	 */
	public function count_by_time($start = 0, $end = 0) {

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

}
