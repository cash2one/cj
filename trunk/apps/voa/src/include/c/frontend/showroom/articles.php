<?php
/**
 * index.php
 * 培训/文章列表
 * $Author$
 * $Id$
 */
class voa_c_frontend_showroom_articles extends voa_c_frontend_showroom_base {

	public function execute() {

		$tc_id = $this->request->get('tc_id');

		$this->view->set('tc_id', $tc_id);
		$this->view->set('navtitle', '文章列表');

		// 引入应用模板
		$this->_output('mobile/showroom/articles');
	}

}
