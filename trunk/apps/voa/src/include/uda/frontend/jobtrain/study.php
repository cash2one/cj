<?php
/**
 * voa_uda_frontend_jobtrain_study
 * Create By wowxavi
 * $Author$
 * $Id$
 */

class voa_uda_frontend_jobtrain_study extends voa_uda_frontend_base {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_jobtrain_study();
		}
	}
	/**
	 * 获取列表
	 * @return array
	 */
	public function list_study(&$result, $conds, $pager, $cata) {
		$result =  $this->__service->list_study_by_conds($conds, $pager, $cata);
		// 未学习格式化
		if(!$conds['is_study']){
			$serv_dp = new voa_s_oa_common_department();
			$serv_jobs = new voa_s_oa_common_job();
			// 读取职位
			$jobs = $serv_jobs->fetch_all();
			// 读取部门
			$dps = $serv_dp->fetch_all();
			foreach ($result['list'] as $k => $v) {
				$result['list'][$k]['department'] = $dps[$v['cd_id']]['cd_name'];
				$result['list'][$k]['job'] = $jobs[$v['cj_id']]['cj_name'];
			}
		}
		return true;
	}
}