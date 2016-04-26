<?php

/**
 * voa_c_api_news_comment_post_add
 * 添加新闻公告评论
 * $Author$
 * $Id$
 */
class voa_c_api_news_post_madd extends voa_c_api_news_abstract{

	// 不需要登录
	public function _before_action($action) {
		$this->_require_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute(){

		$data_post = $this->request->postx();
		try{
			// 读取数据
			$news = array();
			$uda = &uda::factory('voa_uda_frontend_news_minsert');
			$uda->add_news($data_post, $news);
		}catch(help_exception $h){
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		}catch(Exception $e){
			logger::error($e);

			return $this->_api_system_message($e);
		}
		//草稿状态
		$is_publish = $data_post['is_publish'];
		//是否推送消息
		$is_push = isset($data_post['is_push']) ? $data_post['is_push'] : 0;
		if(!$is_publish){ //如果不是发布
			if($data_post['is_check'] == 1){
				$this->_to_queue_s($news);
			}
		}else{
			if($is_push){
				$this->_to_queue_s($news);
			}

		}
		$pluginid = $this->_p_sets['pluginid'];
		$scheme = config::get('voa.oa_http_scheme');

		// 输出结果
		$list_url = "/admincp/office/news/madd/?pluginid=".$pluginid.'&action=add';
		$this->_result = array('comment' => $news, 'url' => $list_url);

		return true;
	}

}

