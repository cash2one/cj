<?php
/**
 * voa_uda_frontend_department_common
 * 统一数据访问/部门/公共类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_department_common extends voa_uda_frontend_department_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 自缓存获取指定id的部门数据
	 * @param number $cd_id
	 * @return array
	 */
	public function get_department($cd_id, &$list) {
		$cd_id = rintval($cd_id, false);
		$list = isset($this->department_list[$cd_id]) ? $this->department_list[$cd_id] : array();
		return true;
	}

	/**
	 * 自缓存获取指定id的部门名称
	 * @param number $cd_id
	 * @return string
	 */
	public function get_dpname($cd_id, &$name) {
		$department = array();
		$this->get_department($cd_id, $department);
		$name = isset($department['cd_name']) ? $department['cd_name'] : '';
		return true;
	}

}
