<?php
/**
 * 我的审批申请列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_askfor_index extends voa_c_frontend_askfor_base {

	public function execute() {
		/** 统计我发起的审批书 */
		$serv_af = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
		$askfor_ct_my = $serv_af->count_mine($this->_user['m_uid']);
		/** 统计待我处理的审批数 */
		$serv_p = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
		$askfor_ct_deal = $serv_p->count_by_uid($this->_user['m_uid']);

		$this->_set_dept_job();
		$this->view->set('askfor_ct_my', $askfor_ct_my);
		$this->view->set('askfor_ct_deal', $askfor_ct_deal);
		$this->view->set('navtitle', '审批');

		$this->_output('askfor/index');
	}
}
