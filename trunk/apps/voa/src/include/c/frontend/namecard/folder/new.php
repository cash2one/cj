<?php
/**
 * 新增名片夹群组
 * $Author$
 * $Id$
 */

class voa_c_frontend_namecard_folder_new extends voa_c_frontend_namecard_folder {

	public function execute() {
		$ncf_name = (string)$this->request->get('ncf_name');
		$ncf_name = trim($ncf_name);
		if (empty($ncf_name)) {
			$this->_error_message('群组名称不能为空');
		}

		$serv = &service::factory('voa_s_oa_namecard_folder', array('pluginid' => startup_env::get('pluginid')));
		$ncf_id = $serv->insert(array(
			'm_uid' => startup_env::get('wbs_uid'),
			'ncf_name' => $ncf_name
		), true);

		$this->_success_message('群组新增成功', '/namecard/folder?ncf_name='.$ncf_name.'&ncf_id='.$ncf_id);
	}
}
