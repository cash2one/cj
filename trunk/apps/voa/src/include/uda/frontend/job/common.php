<?php
/**
 * voa_uda_frontend_job_common
 * 统一数据访问/职务/公共类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_job_common extends voa_uda_frontend_job_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 自缓存获取指定id的职务数据
	 * @param number $cj_id
	 * @return array
	 */
	public function get_job($cj_id, &$list) {
		$cj_id = rintval($cj_id, false);
		$list = isset($this->job_list[$cj_id]) ? $this->job_list[$cj_id] : array();
		return true;
	}

	/**
	 * 自缓存获取指定id的职务名称
	 * @param number $cj_id
	 * @return string
	 */
	public function get_jobname($cj_id, &$name) {
		$job = array();
		$this->get_job($cj_id, $job);
		$name = isset($job['cj_name']) ? $job['cj_name'] : '';
		return true;
	}

}
