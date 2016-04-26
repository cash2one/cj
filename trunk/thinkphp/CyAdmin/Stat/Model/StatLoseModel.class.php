<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午1:44
 */
namespace Stat\Model;

class StatLoseModel extends AbstractModel {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 清除之前数据
	 */
	public function delete_all() {

		$params = array();
		// SET
		$sets = array("`status`=?", "`deleted`=?");
		$where_params[] = $this->get_st_delete();
		$where_params[] = NOW_TIME;

		// 状态条件
		$wheres = array(
			'status < ?',
			'slid > ?',
		);
		$where_params[] = $this->get_st_delete();
		$where_params[] = 0;

		return $this->_m->result("UPDATE __TABLE__ SET ".implode(',', $sets)." WHERE ".implode(' AND ', $wheres), $where_params);

	}
}