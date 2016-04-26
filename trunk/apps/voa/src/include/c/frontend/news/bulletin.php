<?php
/**
 * 公司动态列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_news_bulletin extends voa_c_frontend_news_base {

	public function execute() {

		$params = array(
			'newsId' => rintval($this->request->get('newsId')),
			'pluginid' => $this->request->get('pluginid')
		);
		$url = '/h5/index.html#/app/page/news/news-add-bulletin?' . http_build_query($params);
		$this->redirect($url);
		return true;
	}

}

