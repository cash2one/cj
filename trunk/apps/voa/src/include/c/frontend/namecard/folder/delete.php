<?php
/**
 * 删除名片夹群组
 * $Author$
 * $Id$
 */

class voa_c_frontend_namecard_folder_delete extends voa_c_frontend_namecard_folder {

	public function execute() {
		$ncf_id = intval($this->request->get('ncf_id'));
		$folder = $this->_get_mine($ncf_id);
		if (empty($folder)) {
			$this->_error_message('该群组不存在或已删除');
		}

		$serv = &service::factory('voa_s_oa_namecard_folder', array('pluginid' => startup_env::get('pluginid')));
		$serv->delete_by_ids(array($ncf_id));

		$this->_success_message('删除操作成功');
	}
}
