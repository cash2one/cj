<?php
/**
 * 名片夹群组列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_namecard_folder_list extends voa_c_frontend_namecard_folder {

	public function execute() {
		$uda = uda::factory('voa_uda_frontend_namecard_format');

		$serv_nc = &service::factory('voa_s_oa_namecard', array('pluginid' => startup_env::get('pluginid')));
		$serv_f = &service::factory('voa_s_oa_namecard_folder', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv_f->fetch_by_uid(startup_env::get('wbs_uid'));
		if (!$uda->folder_list($list)) {
			$this->_error_message($uda->error);
			return false;
		}

		$this->_set_dept_job();
		$this->view->set('list', $list);
		$this->view->set('ct_folder', $serv_f->count_by_uid(startup_env::get('wbs_uid')));
		$this->view->set('ct_namecard', $serv_nc->count_by_uid(startup_env::get('wbs_uid')));

		$this->_output('namecard/folder/list');
	}
}
