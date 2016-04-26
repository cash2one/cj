<?php
/**
 * SignDepartmentModel.class.php
 * $author$
 */

namespace Sign\Model;

class SignDepartmentModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 查班次部门表里对应部门的班次
	 * @param $dep
	 * @return array
	 */
	public function list_by_department($dep) {
		$sql = "SELECT * FROM __TABLE__";
		//拼装查询条件
		$where[] = "department IN (?)";
		$where_params[] = $dep;
		//不查询已经删除的
		$where[] = "status < ?";
		$where_params[] = $this->get_st_delete();

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * 获取部门对应有效班次
	 * @param $dep
	 * @return array
	 */
	public function get_by_department($dep) {
		$sql = "SELECT * FROM __TABLE__";
		$where = "department = " . $dep;
		$and = "status < 3";

		return $this->_m->fetch_array($sql . ' WHERE ' . $where . ' AND ' . $and);
	}

	/**
	 * 根据 班次id 获取数据
	 * @param $sbid
	 * @return array
	 */
	public function list_by_sbid($sbid) {
		$sql = "SELECT * FROM __TABLE__";
		//拼装查询条件
		$where[] = "sbid IN (?)";
		$where_params[] = $sbid;
		//不查询已经删除的
		$where[] = "status < ?";
		$where_params[] = $this->get_st_delete();

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}


	/**
	 * [get_true_cdids 接着拿出符合的所有的部门id]
	 * @param  [type] $true_sbids [传递的数据]
	 * @return [type]             [接着拿出符合的所有的部门id]
	 */
	public function get_true_cdids($v) {

		$sql = "SELECT * FROM __TABLE__";
		//拼装查询条件
		$where[] = "sbid = ?";
		$where_params[] = $v;

		//不查询已经删除的
		$where[] = "status < ?";
		$where_params[] = $this->get_st_delete();

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);	
	}

}
