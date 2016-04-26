<?php
/**
 * 个人中心
 * $Author$
 * $Id$
 */

class voa_c_frontend_member_center extends voa_c_frontend_base {

	public function execute() {
		$uid = (int)startup_env::get('wbs_uid');
		/** 统计需要参加的会议数 */
		$servm = &service::factory('voa_s_oa_meeting_mem', array('pluginid' => startup_env::get('pluginid')));
		$meeting_ct = $servm->count_by_uid($uid);

		/** 统计等待我审批的申请 */
		$servafp = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
		$askfor_ct = $servafp->count_by_uid($uid);

		/** 统计我参加的项目 */
		$serv_pm = &service::factory('voa_s_oa_project_mem', array('pluginid' => startup_env::get('pluginid')));
		$project_ct = $serv_pm->count_running_by_uid($uid);

		$this->_set_dept_job();
		$this->view->set('meeting_ct', $meeting_ct);
		$this->view->set('askfor_ct', $askfor_ct);
		$this->view->set('project_ct', $project_ct);
		$this->view->set('navtitle', '个人中心');

		$this->_output('member/center');
	}
}

