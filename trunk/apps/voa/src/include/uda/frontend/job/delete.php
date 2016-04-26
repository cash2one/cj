<?php
/**
 * voa_uda_frontend_job_delete
 * 统一数据访问/职务/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_job_delete extends voa_uda_frontend_job_base {

	/**
	 * 删除指定$job的职务信息
	 * @param array|number $job 可以是职务的id，也可以是职务的信息数组，推荐设置为职务信息数组
	 * @return boolean
	 */
	public function delete($job) {

		$uda_get = &uda::factory('voa_uda_frontend_job_get');

		// 给定的参数值既不是职务信息数组也不是职务id
		if (!isset($job['cj_id']) && !is_numeric($job)) {
			$this->errmsg(1001, '请指定要删除的职务');
			return false;
		}

		// 如果给定的是职务id，则找到其其他数据
		if (is_numeric($job)) {
			$cj_id = $job;
			$job = array();
			$uda_get->job($cj_id, $job);
			if (empty($job['cj_id'])) {
				$this->errmsg(1002, '指定的职务不存在或已被删除');
				return false;
			}
		}

		$cj_id = $job['cj_id'];

		// 删除本地数据
		$this->serv->delete($cj_id);

		// 更新缓存
		parent::update_cache();

		return true;
	}

}
