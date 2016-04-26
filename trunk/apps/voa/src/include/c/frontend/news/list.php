<?php
/**
 * 公司动态列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_news_list extends voa_c_frontend_news_base {

	public function execute() {

		$params = array(
			'nca_id' => rintval($this->request->get('nca_id')),
			'navtitle' => $this->request->get('navtitle'),
			'pluginid' => $this->request->get('pluginid')
		);
		$url = '/h5/index.html#/app/page/news/news-list?' . http_build_query($params);
		$this->redirect($url);
		return true;
	}

}

