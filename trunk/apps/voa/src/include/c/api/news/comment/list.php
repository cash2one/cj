<?php
/**
 * voa_c_api_news_comment_list
 * 获取新闻公告评论
 * $Author$
 * $Id$
 */

class voa_c_api_news_comment_list extends voa_c_api_news_abstract {

	public function execute() {

		// 请求的参数
		$fields = array(
			// 新闻公告ID
			'ne_id' => array('type' => 'string', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		$ne_id = $this->_params['ne_id'];

		try{
			// 获取数据
			$result = array();
			$uda = &uda::factory('voa_uda_frontend_news_comment_list', $this->_ptname);
			$uda->list_news($result, array('ne_id' => $ne_id));
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}

		// 输出结果
		$this->_result = array(
			'list' => $result
		);

		return true;
	}

}

