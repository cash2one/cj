<?php
/**
 * index.php
 * 培训/手机版入口文件
 * $Author$
 * $Id$
 */
class voa_c_frontend_showroom_index extends voa_c_frontend_showroom_base {

	public function execute() {

		$this->view->set('navtitle', '目录列表');

		// 引入应用模板
		$this->_output('mobile/showroom/index');
	}

}
