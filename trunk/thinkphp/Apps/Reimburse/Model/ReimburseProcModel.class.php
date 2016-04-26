<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/4
 * Time: 下午5:15
 */

namespace Reimburse\Model;

class ReimburseProcModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();

		$this->prefield = 'rbpc_';
	}

	/**
	 * crm统计数据
	 * @return array
	 */
	public function count_data() {

		$sql = "SELECT count(*) FROM __TABLE__";

		// 查询条件
		$where = array(
			'rbpc_status > ?',
			'rbpc_created > ?',
			'rbpc_created < ?',
			'rbpc_status < ?',
		);
		$s_time = rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')) - 86400;
		$e_time = rstrtotime(rgmdate(NOW_TIME, 'Y-m-d'));
		$where_params = array(
			1,
			$s_time,
			$e_time,
			$this->get_st_delete(),
		);

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	// 获取删除状态值
	public function get_st_delete() {

		return 6;
	}
}