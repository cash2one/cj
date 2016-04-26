<?php
/**
 * mycount.php
 * api任务接口：查看与“我”相关的统计计数
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_project_get_mycount extends voa_c_api_project_base {

	public function execute() {

		$serv_p = &service::factory('voa_s_oa_project', array('pluginid' => $this->_pluginid));

		$updated = startup_env::get('timestamp') + 10;

		$this->_result = array(
			// 我参与的所有项目数量
			'all' => (int)$serv_p->count_my_by_uids_updated($this->_member['m_uid'], $updated),
			// 正在进行中的项目数量
			'active' => (int)$serv_p->count_myactive_by_uids_updated($this->_member['m_uid'], $updated)
		);

		return true;
	}

}
