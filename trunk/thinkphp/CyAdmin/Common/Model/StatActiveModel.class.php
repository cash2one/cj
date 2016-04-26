<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/10/24
 * Time: 下午1:41
 */

namespace Common\Model;

class StatActiveModel extends AbstractModel {

	const ZERO = 0;
	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 统计昨日所有活跃人员
	 * @return array
	 */
	public function count_all_active_member(){

		$sql = "SELECT SUM(active_count) FROM __TABLE__";
		// 设置条件
		$where = array(
			'count > ?',
			'created > ?',
			'created < ?',
			'status < ?',
		);

		$where_params = array(
			self::ZERO,
			rstrtotime('yesterday'),
			rstrtotime('today'),
			$this->get_st_delete(),
		);

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}
}
