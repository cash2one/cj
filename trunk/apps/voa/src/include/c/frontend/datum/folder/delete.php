<?php
/**
 * 删除文件夹
 * $Author$
 * $Id$
 */

class voa_c_frontend_datum_folder_delete extends voa_c_frontend_datum_folder {

	public function execute() {
		$dtf_id = intval($this->request->get('dtf_id'));
		$folder = $this->_get_mine($dtf_id);
		if (empty($folder)) {
			$this->_error_message('该文件夹不存在或已删除');
		}

		$serv = &service::factory('voa_s_oa_datum_folder', array('pluginid' => startup_env::get('pluginid')));
		$serv->delete_by_id($dtf_id);

		$this->_success_message('删除操作成功');
	}
}
