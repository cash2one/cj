<?php
/**
 * 新增文件夹
 * $Author$
 * $Id$
 */

class voa_c_frontend_datum_folder_new extends voa_c_frontend_datum_folder {

	public function execute() {
		$name = trim($this->request->get('dtf_name'));
		if (empty($name)) {
			$this->_error_message('文件夹名称不能为空');
		}

		$serv = &service::factory('voa_s_oa_datum_folder', array('pluginid' => startup_env::get('pluginid')));
		$serv->insert(array(
			'm_uid' => startup_env::get('wbs_uid'),
			'm_username' => startup_env::get('wbs_username'),
			'dtf_name' => $name
		));

		$this->_success_message('文件夹新增成功', '/datum/folder');
	}
}
