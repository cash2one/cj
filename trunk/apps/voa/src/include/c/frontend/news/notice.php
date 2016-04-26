<?php
/**
 * 通知公告列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_news_notice extends voa_c_frontend_news_base {

	public function execute() {

		$this->view->set('navtitle', '通知公告');
		$this->view->set('nca_id', voa_d_oa_news::CATGEGORY_NOTICE);

		$this->_output('mobile/news/list');
	}

}

