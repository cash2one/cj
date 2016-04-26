<?php
/**
 * voa_c_api_news_comment_post_medit
 * 新闻公告多条修改
 * $Author$
 * $Id$
 */

class voa_c_api_news_post_medit extends voa_c_api_news_abstract {

	public function _before_action($action) {

		$this->_require_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		$data_post = $this->request->postx();
		$news = array();
		try {
			$uda = &uda::factory('voa_uda_frontend_news_medit');
			$uda->edit_news($data_post,  $news); //多条修改
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}

		$is_publish = $data_post['is_publish'];
		$is_push = isset($data_post['is_push']) ? $data_post['is_push'] : 0;
		//如果不是发布
		if (!$is_publish) {
			if($data_post['is_check'] == 1) {
				$this->_to_queue_s($news);
			}
		} else {
			//进行消息推送
			if($is_push){
				$this->_to_queue_s($news);
			}

		}
		$pluginid = $this->_p_sets['pluginid'];
		//返回url
		$list_url = "/admincp/office/news/madd/?pluginid=".$pluginid.'&action=edit';
		// 输出结果
		$this->_result = array(
			'comment' => $news,
			'url' => $list_url
		);

		return true;
	}

}

