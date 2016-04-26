<?php
/**
 * 任务主页(暂时没用)
 * $Author$
 * $Id$
 */

class voa_c_frontend_project_index extends voa_c_frontend_project_base {

	public function execute() {
		$serv = &service::factory('voa_s_oa_project_mem', array('pluginid' => startup_env::get('pluginid')));
		$uid = startup_env::get('wbs_uid');
		$ct_mine = $serv->count_mine($uid);

		$ct_closed = $serv->count_closed($uid);

		$ct_done = $serv->count_done($uid);

		$this->view->set('ct_mine', $ct_mine);
		$this->view->set('ct_closed', $ct_closed);
		$this->view->set('ct_done', $ct_done);

		$this->_output('project/index');
	}
}

