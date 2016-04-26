<?php
/**
 * voa_uda_frontend_project_format
 * 统一数据访问/任务应用/数据格式化
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_project_format extends voa_uda_frontend_project_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化投票数据数组
	 * @param array $list 投票信息数组
	 * @return boolean
	 */
	public function project_list(&$list) {
		foreach ($list as &$project) {
			$this->project($project);
		}

		return true;
	}

	/**
	 * 格式化投票数据
	 * @param array $project 投票信息
	 */
	public function project(&$project) {
		$project['_subject'] = rhtmlspecialchars($project['p_subject']);
		$project['_message'] = rhtmlspecialchars($project['p_message']);
		$project['_updated'] = rgmdate($project['p_updated'], 'Y-m-d H:i');

		return true;
	}
}
