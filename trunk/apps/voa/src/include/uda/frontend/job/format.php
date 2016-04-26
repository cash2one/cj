<?php
/**
 * voa_uda_frontend_job_format
 * 统一数据访问/职务/格式化输出
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_job_format extends voa_uda_frontend_job_base {

	/**
	 * 主要数据字段
	 * @var array
	 */
	public $primary_fields = array('cj_name', 'cj_displayorder');

	/**
	 * 格式化职务信息显示，全部数据包括原型
	 * @param array $job <strong style="color:red">(引用结果)</strong>职务信息
	 * @return boolean
	*/
	public function format(&$job) {

		return true;
	}

	/**
	 * 格式化职务信息显示，只输出主要的数据 cj_name,cj_displayorder
	 * @param array $data 职务数据原型
	 * @param array $job <strong style="color:red">(引用结果)</strong>输出精简后的主要字段数据
	 * @return boolean
	 */
	public function format_primary($data, &$job) {
		$job = array();
		foreach ($data as $key => $value) {
			if (in_array($key, $this->primary_fields)) {
				$job[$key] = $value;
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
