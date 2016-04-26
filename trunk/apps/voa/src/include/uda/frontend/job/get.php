<?php
/**
 * voa_uda_frontend_job_get
 * 统一数据访问/职务/获取数据
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_job_get extends voa_uda_frontend_job_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 获取指定cj_id的职务信息（如果cj_id为空则返回默认数据）
	 * @param number $cj_id
	 * @param array $job <strong style="color:red">(引用结果)</strong>返回的职务数据
	 * @return boolean
	 */
	public function job($cj_id = 0, &$job = array()) {
		$cj_id = rintval($cj_id, false);
		$uda_format = &uda::factory('voa_uda_frontend_job_format');
		if ($cj_id > 0) {
			$job = $this->serv->fetch($cj_id);
		} else {
			$job = $this->serv->fetch_all_field();
		}
		$uda_format->format($job);

		return true;
	}

	/**
	 * 列出全部职务列表
	 * @param array $list <strong style="color:red">(引用结果)</strong>返回的职务列表数据
	 * @param string $type 输出的数据类型，primary主要字段数据，否则输出全部字段数据
	 */
	public function list_all(&$list, $type = 'primary') {

		$uda_format = &uda::factory('voa_uda_frontend_job_format');
		$list = $this->serv->fetch_all();
		if ($type == 'primary') {
			$uda_format->format_primary_list($list);
		} else {
			$uda_format->format_list($list);
		}

		return true;
	}

	/**
	 * 统计指定职务cj_id的用户数
	 * @param number $cj_id
	 * @param number $count <strong style="color:red">(引用结果)</strong>返回的该职务下的用户数
	 * @return boolean
	 */
	public function count_by_cj_id($cj_id = 0, &$count = 0) {
		$serv_member = &service::factory('voa_s_oa_member');
		$count = $serv_member->count_by_cj_id($cj_id);
		$count = rintval($count, false);
		return true;
	}

	/**
	 * 尝试找到职务名称cj_name对应的cj_id
	 * @param string $cj_name 职务名称
	 * @param number $cj_id <strong style="color:red">(引用结果)</strong>对应的cj_id
	 * @return boolean
	 */
	public function get_cj_id_by_name($cj_name = '', &$cj_id = 0) {
		$job = $this->serv->fetch_by_cj_name($cj_name);
		if (empty($job)) {
			$cj_id = 0;
			return false;
		} else {
			$cj_id = $job['cj_id'];
			return true;
		}
	}

	/**
	 * 获取指定cj_ids的职务列表
	 * @param array $cj_ids
	 * @param array $job <strong style="color:red">(引用结果)</strong>获取到的职务信息列表
	 * @return boolean
	 */
	public function get_by_cj_ids($cj_ids = array(), &$job = array()) {
		$job = $this->serv->fetch_all_by_key($cj_ids, 'cj_id');
		return true;
	}

}
