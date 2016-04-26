<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午1:44
 */
namespace Home\Model;

class StatCompanyModel extends AbstractModel {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据条件和时间统计付费公司
	 * @return array
	 */
	public function list_by_conds_cp($params, $page_option) {

		$sql = "SELECT * FROM __TABLE__";
		// 设置条件
		$where = array(
			'time > ?',
			'time < ?',
			'status < ?',
		);

		$s_time = rstrtotime($params['s_time']);
		$e_time = rstrtotime($params['e_time']);
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
		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}
}