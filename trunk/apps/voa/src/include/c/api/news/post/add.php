<?php
/**
 * voa_c_api_news_comment_post_add
 * 添加新闻公告评论
 * $Author$
 * $Id$
 */

class voa_c_api_news_post_add extends voa_c_api_news_abstract {

	public function execute() {
		
		// 请求的参数
		$fields = array(
			// 新闻公告ID
			'ne_id' => array('type' => 'string', 'required' => true),
			'content' => array('type' => 'string', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		$this->_params['m_uid'] = $this->_member['m_uid'];
		
		//linshiling 检测有没有评论权限
		$cd_id = $this->_member['cd_id'];
		$right = new voa_d_oa_news_right();
		$rs = $right->has_right($this->_params['ne_id'], $this->_params['m_uid'], $cd_id);
		if(!$rs) {
			return $this->_set_errcode('没有评论权限');
		}

		try{
			// 获取数据
			$result = array();
			$uda = &uda::factory('voa_uda_frontend_news_comment_insert');
			$uda->add_comment($this->_params, $result);
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}

		// 输出结果
		$this->_result = array(
			'comment' => $result
		);

		return true;
	}

}

