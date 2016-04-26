<?php
/**
 * 列出文件信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_file_list extends voa_c_frontend_file_base {

	public function execute() {
		

		$this->view->set('list', $list);
		

		$this->_output('file/list');
	}
}
