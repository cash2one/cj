<?php
/**
 * voa_uda_frontend_department_format
 * 统一数据访问/部门/格式化
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_department_format extends voa_uda_frontend_department_base {

	/**
	 * 主要数据字段
	 * @var array
	 */
	public $primary_fields = array('cd_name', 'cd_displayorder', 'cd_qywxid', 'cd_qywxparentid', 'cd_usernum');

	/**
	 * 格式化部门信息显示，全部数据包括原型
	 * @param array $department <strong style="color:red">(引用结果)</strong>部门信息
	 * @return boolean
	 */
	public function format(&$department) {

		return true;
	}

	/**
	 * 格式化部门信息显示，只输出主要的数据 cd_name,cd_displayorder,cd_qywxid,cd_qywxparentid,cd_usernum
	 * @param array $data 部门数据原型
	 * @param array $department <strong style="color:red">(引用结果)</strong>输出精简后的主要字段数据
	 * @return boolean
	 */
	public function format_primary($data, &$department) {
		$department = array();
		foreach ($data as $key => $value) {
			if (in_array($key, $this->primary_fields)) {
				$department[$key] = $value;
			}
		}

		return true;
	}

	/**
	 * 格式化列表数据以输出全部字段数据
	 * @param array $list <strong style="color:red">(引用结果)</strong>输出格式化后的全部字段数据
	 * @return boolean
	 */
	public function format_list(&$list) {
		foreach ($list as &$value) {
			$this->format($value);
		}

		return true;
	}

	/**
	 * 格式化列表数据以输出主要字段数据
	 * @param array $list <strong style="color:red">(引用结果)</strong>输出格式化后的主要字段数据
	 * @return boolean
	 */
	public function format_primary_list(&$list) {
		foreach ($list as &$value) {
			$this->format_primary($value, $value);
		}

		return true;
	}
}
