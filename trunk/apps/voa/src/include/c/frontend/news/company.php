<?php
/**
 * 公司动态列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_news_company extends voa_c_frontend_news_base {

	public function execute() {

		$this->view->set('navtitle', '公司动态');
		$this->view->set('nca_id', voa_d_oa_news::CATGEGORY_COMPANY);

		$this->_output('mobile/news/list');
	}

}

