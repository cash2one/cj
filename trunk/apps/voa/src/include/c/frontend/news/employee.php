<?php
/**
 * 员工动态列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_news_employee extends voa_c_frontend_news_base {

	public function execute() {

		$this->view->set('navtitle', '员工动态');
		$this->view->set('nca_id', voa_d_oa_news::CATGEGORY_EMPLOYEE);

		$this->_output('mobile/news/list');
	}

}

