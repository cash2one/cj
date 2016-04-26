<?php
/**
 * 编辑名片夹群组
 * $Author$
 * $Id$
 */

class voa_c_frontend_namecard_folder_edit extends voa_c_frontend_namecard_folder {

	public function execute() {
		$name = trim($this->request->get('ncf_name'));
		/** 因为输出时进行过转换, 所以这里再转回来 */
		$name = htmlspecialchars_decode($name);
		if (empty($name)) {
			$this->_error_message('群组名称不能为空');
		}

		$ncf_id = intval($this->request->get('ncf_id'));
		$folder = $this->_get_mine($ncf_id);
		if (empty($folder)) {
			$this->_error_message('该群组不存在或已删除');
		}

		/** 更新 */
		$serv = &service::factory('voa_s_oa_namecard_folder', array('pluginid' => startup_env::get('pluginid')));
		$serv->update(array(
			'ncf_name' => $name
		), array('ncf_id' => $ncf_id));

		$this->_success_message('群组编辑成功', '/namecard/folder');
	}
}
