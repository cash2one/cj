<?php
/**
 * voa_uda_frontend_job_update
 * 统一数据访问/职位管理/更新
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_job_update extends voa_uda_frontend_job_base {


	public function __construct() {
		parent::__construct();
	}

	/**
	 * 添加/更新 职位
	 * @param array $history 旧数据，如果为空则为新建
	 * @param array $job 新提交的数据
	 * @param array $update <strong style="color:red">(引用结果)</strong>返回完整的新数据
	 * @return boolean
	 */
	public function update($history, $job, &$update) {

		$update = array();

		if (!isset($job['cj_displayorder']) && !isset($job['cj_name'])) {
			$this->errmsg('1001', '职位名称必须提供');
			return false;
		}

		if ((empty($history) || empty($history['cj_id'])) && $this->serv->count_all() > voa_d_oa_common_job::COUNT_MAX) {
			// 如果是新增，判断职位数量是否超过限制
			$this->errmsg('1002', '系统限制最多允许添加 '.voa_d_oa_common_job::COUNT_MAX.' 个职位');
			return false;
		}

		if (empty($history)) {
			// 无历史数据，则认为是新增，提取默认值作为历史数据
			$history = $this->serv->fetch_all_field();
		}

		if (!isset($job['cj_displayorder'])) {
			// 如果提交的数据不包含显示顺序值，则认为其与历史数据一致
			$job['cj_displayorder'] = $history['cj_displayorder'];
		}
		if (!isset($job['cj_name'])) {
			// 如果提交的数据不包含职位名称，则认为其与历史数据一致
			$job['cj_name'] = $history['cj_name'];
		}

		// 发生改变的数据
		$update = array();
		$this->updated_fields($history, $job, $update);
		if (empty($update)) {
			$this->errmsg('1003', '数据未发生改变无须提交');
			return false;
		}

		/**
		 * 检查显示顺序取值是否合法
		 */
		if (isset($update['cj_displayorder'])) {
			$update['cj_displayorder'] = (int)$job['cj_displayorder'];
			if ($update['cj_displayorder'] < $this->job_displayorder[0] && $update['cj_displayorder'] > $this->job_displayorder[1]) {
				// 显示顺序取值超出范围
				$update['cj_displayorder'] = 99;
			}
		}

		/**
		 * 检查职位名称是否合法
		 */
		if (isset($update['cj_name'])) {
			$update['cj_name'] = (string)$job['cj_name'];
			$update['cj_name'] = trim($update['cj_name']);
			$update['cj_name'] = preg_replace('/\s+/s', '', $update['cj_name']);
			if (!$this->validator_length($update['cj_name'], $this->job_name_length)) {
				// 职位名称长度不合法
				$this->errmsg('1004', '职位名称：'.$this->error);
				return false;
			}
			if ($update['cj_name'] != rhtmlspecialchars($update['cj_name'])) {
				$this->errmsg('1005', '职位名称不能包含特殊字符');
				return false;
			}
			if ($this->serv->count_by_name_notid($update['cj_name'], $history['cj_id']) > 0) {
				$this->errmsg('1006', '职位名称“'.$update['cj_name'].'”已被使用，请更换一个');
			}
		}

		// 真实可靠的发生改变了的数据
		$updated = array();
		$this->updated_fields($history, $update, $updated);
		if (empty($updated)) {
			$this->errmsg('1007', '数据未发生改变无须提交');
			return false;
		}
		$update = $updated;

		/**
		 * 提交数据更新
		 */
		if ($history['cj_id']) {
			// 更新职位信息
			$this->serv->update($update, $history['cj_id']);
			$this->errmsg(0, '编辑职位信息操作完毕');
		} else {
			// 新增职位
			$update['cj_id'] = $this->serv->insert($update, true);
			$this->errmsg(0, '新增职位信息操作完毕');
		}

		$update = array_merge($job, $update);

		// 更新缓存
		parent::update_cache();

		return true;
	}

	/**
	 * 更新职位显示顺序
	 * @param array $displayorder
	 * @return boolean
	 */
	public function displayorder_update($displayorder) {

		$displayorder = rintval($displayorder, true);

		$uda_get = &uda::factory('voa_uda_frontend_job_get');
		$list = array();
		$uda_get->list_all($list);

		$update = array();
		foreach ($list as $cj_id => $cd) {
			if (!isset($displayorder[$cj_id]) || $displayorder[$cj_id] == $cd['cj_displayorder']) {
				continue;
			}
			$value = $displayorder[$cj_id];
			if ($value >= $this->job_displayorder[0] && $value <= $this->job_displayorder[1]) {
				$update[$value][$cj_id] = $cj_id;
			}
		}

		if (empty($update)) {
			$this->errmsg('2001', '数据未更新无须提交');
			return false;
		}

		try {

			$this->serv->begin();

			foreach ($update as $_displayorder => $cj_ids) {
				$this->serv->update(array('cj_displayorder' => $_displayorder), $cj_ids);
			}

			$this->serv->commit();

		} catch (Exception $e) {
			$this->serv->rollback();
			$this->errmsg(2002, '更新职位排序发生数据错误');
			return false;
		}

		// 更新缓存
		parent::update_cache();

		return true;
	}

}
