<?php
/**
 * SignDepartmentService.class.php
 * $author$
 */

namespace Sign\Service;

class SignDepartmentService extends AbstractService {

	// 构造方法
	public function __construct() {
		$this->_d = D("Sign/SignDepartment");

		parent::__construct();
	}

	/**
	 * 查班次部门表里对应部门的班次
	 * @param $dep
	 * @return mixed
	 */
	public function list_batch_by_department($dep) {
		$list = $this->_d->list_by_department($dep);

		// 所有的班次
		$all_batch = array();
		if (!empty($list)) {
			// 把对应的班次ID放入新数组
			foreach ($list as $_sib) {
				$all_batch[] = $_sib['sbid'];
			}
		}

		return $all_batch;
	}

	/**
	 * 查询部门关联班次
	 * @param $dep
	 * @return mixed
	 */
	public function list_by_department($dep) {
		$list = $this->_d->list_by_department($dep);

		return $list;
	}

	/**
	 * 获取部门对应有效班次
	 * @param $dep
	 * @return mixed
	 */
	public function get_by_department($dep) {
		return $this->_d->get_by_department($dep);
	}

	/**
	 * 根据 班次id 获取数据
	 * @param $sbid
	 * @return mixed
	 */
	public function list_by_sbid($sbid) {
		return $this->_d->list_by_sbid($sbid);
	}

	/**
	 * [get_true_cdids 接着拿出符合的所有的部门id]
	 * @param  [type] $true_sbids [传递的参数]
	 * @return [type]             [接着拿出符合的所有的部门id]
	 */
	public function get_true_cdids($v) {

		return $this->_d->get_true_cdids($v);
	}


}
