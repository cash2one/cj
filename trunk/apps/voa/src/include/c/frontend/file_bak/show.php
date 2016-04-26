<?php
/**
 * 展示文件信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_file_show extends voa_c_frontend_file_base {

	public function execute() {

		/** 获取文件信息 */
		$id = (int)$this->request->get('fla_id');
		$uda = &uda::factory('voa_uda_frontend_file_get');
		$file = null;
		if (!$uda->get_by_fla_id($file, $id)) {
			$this->_error_message('数据错误, 请联系管理员');
		}

		$this->view->set('info', $file);

		//$this->_output('file/show');
	}
}
