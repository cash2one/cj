<?php
/**
 * 通知公告详情
 * $Author$
 * $Id$
 */
class voa_c_frontend_news_view extends voa_c_frontend_news_base {

	public function execute() {

		$ne_id = rintval($this->request->get('ne_id'));
		if (empty($ne_id)) {
			$ne_id = $this->request->get('newsId');
		}
		$params = array(
			'newsId' => $ne_id,
			'action' => $this->request->get('action'),
			'pluginid' => $this->request->get('pluginid')
		);
		$url = '/h5/index.html#/app/page/news/news-detail?' . http_build_query($params);
		$this->redirect($url);
		return true;
		$ne_id = rintval($this->request->get('ne_id'));
		try {
			//获取新闻公告详情
			$uda = &uda::factory('voa_uda_frontend_news_view');
			$news = array();
			$uda->get_view(array(
				'ne_id' => $ne_id,
				'm_uid' => $this->_user['m_uid']
			), $news);
		} catch (help_exception $h) {
			$this->_error_message($h->getMessage());
			return false;
		} catch (Exception $e) {
			logger::error($e);
			$this->_error_message($e->getMessage());
			return false;
		}

		// 查看是否是保密信息
		if ($news['is_secret'] == 1) {
			$is_secret = '[保密]';
		} else {
			$is_secret = '';
		}

		// 声明bool，判断当前用户是否有权限查看未读人员
		$bool = false;
		if ($news['m_uid'] == $this->_user['m_uid']) {
			$bool = true;
			// 未读人员列表url
			$unread_url = '/frontend/news/unread/?ne_id=' . $ne_id;
			// 获取未读人员总数
			$uda_read = &uda::factory('voa_uda_frontend_news_read');
			$unread_total = $uda_read->count_real_unusers($ne_id);
			// 注入模板变量
			$this->view->set('unread_total', $unread_total);
			$this->view->set('unread_url', $unread_url);
		}
		$this->view->set('navtitle', $is_secret . rhtmlspecialchars($news['title']));
		$this->view->set('news', $news);
		$this->view->set('bool', $bool);

        // 输出模版
		$this->_output('mobile/news/view');
	}
}

//end

