<?php
/**
 * 编辑文件夹
 * $Author$
 * $Id$
 */

class voa_c_frontend_datum_folder_edit extends voa_c_frontend_datum_folder {

	public function execute() {
		$name = trim($this->request->get('dtf_name'));
		/** 因为输出时进行过转换, 所以这里再转回来 */
		$name = htmlspecialchars_decode($name);
		if (empty($name)) {
			$this->_error_message('文件夹名称不能为空');
		}

		$dtf_id = intval($this->request->get('dtf_id'));
		$folder = $this->_get_mine($dtf_id);
		if (empty($folder)) {
			$this->_error_message('该文件夹不存在或已删除');
		}

		/** 更新 */
		$serv = &service::factory('voa_s_oa_datum_folder', array('pluginid' => startup_env::get('pluginid')));
		$serv->update(array(
			'dtf_name' => $name
		), array('dtf_id' => $dtf_id));

		$this->_success_message('文件夹编辑成功', '/datum/folder');
	}
}
