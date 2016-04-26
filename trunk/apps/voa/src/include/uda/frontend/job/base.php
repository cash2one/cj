<?php
/**
 * voa_uda_frontend_job_base
 * 统一数据访问/职务表/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_job_base extends voa_uda_frontend_base {

	/**
	 * 全部职务列表
	 * @var array
	 */
	public $job_list = array();

	/** 职务名长度限制 array(长度单位, min, max) */
	public $job_name_length = array('count', 1, 64);

	/** 显示顺序取值范围 array(min, max) */
	public $job_displayorder = array(0, 99);

	/** 职务表操作对象 */
	public $serv = null;

	public function __construct() {
		parent::__construct();
		$this->job_list = voa_h_cache::get_instance()->get('job', 'oa');
		if ($this->serv === null) {
			$this->serv = &service::factory('voa_s_oa_common_job', array('pluginid' => 0));
		}
	}

	/**
	 * 更新职务缓存
	 * @return boolean
	 */
	public function update_cache() {
		voa_h_cache::get_instance()->get('job', 'oa', true);
		return true;
	}

	/**
	 * 判断职务cj_id或者名称cj_name是否合法，获取其真实的cj_id
	 * <p>主要用于在其他业务添加或更新职务信息的操作</p>
	 * <strong style="color:blue">不存在的职务名称则会尝试添加</strong>
	 * @param number $job_id
	 * @param string $job_name
	 * @param number $cj_id <strong style="color:red">(引用结果)</strong> 实际的cj_id
	 * @return boolean
	 */
	public function check_job($job_id, $job_name, &$cj_id) {

		$cj_id = 0;
		$job_uda_update = &uda::factory('voa_uda_frontend_job_update');
		$job_uda_get = &uda::factory('voa_uda_frontend_job_get');

		if (!empty($job_id)) {
			// 选择了职务，验证所选的cj_id是否合法
			$job = array();
			$job_uda_get->job($job_id, $job);
			if (!empty($job['cj_id'])) {
				// 找到了该id的职务
				$cj_id = $job['cj_id'];
				return true;
			}
		}

		if (empty($cj_id) && !empty($job_name)) {
			// 未提供或者未找到选择的职务cj_id，但填写了职务名称，则尝试检查输入的职务名

			$_cj_id = 0;
			if ($job_uda_get->get_cj_id_by_name($job_name, $_cj_id)) {
				// 找到了此名字的职务
				$cj_id = $_cj_id;
				return true;
			} else {
				// 未找到此名字的职务，尝试添加
				$result = array();
				if ($job_uda_update->update(array(), array('cj_name' => $job_name), $result)) {
					// 添加成功
					$cj_id = $result['cj_id'];
					return true;
				} else {
					// 添加失败
					$this->errmsg(1004, '新增职务出错:'.$this->error);
					return false;
				}
			}
		}

		return true;
	}
}
