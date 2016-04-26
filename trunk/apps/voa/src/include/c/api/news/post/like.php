<?php
/**
 * voa_c_api_news_post_like
 * 添加新闻公告点赞
 * $Author ppker
 * $Id$
 */

class voa_c_api_news_post_like extends voa_c_api_news_abstract {

	public function execute() {
		
		// 请求的参数
		$fields = array(
			// 新闻公告ID
			'ne_id' => array('type' => 'string', 'required' => true),
			//'description' => array('type' => 'int', 'required' => true) // 描述 +1 -1
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		// $this->_params['m_uid'] = $this->_member['m_uid'];
		$this->_params['m_uid'] = startup_env::get('wbs_uid');
		//检测合法性
		//if(!$this->_params['is_like']) return $this->_set_errcode("点赞不合法！");

		try{
			//插入点赞表数据
			$like = &uda::factory('voa_uda_frontend_news_like_insert');
			$like_data = array();
			$like_data['ip'] = $this->request->get_client_ip();
			$like_data['m_uid'] = $this->_params['m_uid'];

			//$like_data['description'] = $this->_params['description'];
			$like_data['ne_id'] = $this->_params['ne_id'];
			// 新增点赞记录
			$res_like = array();
			if (!$like->add_like($like_data,$res_like)) {
				$this->_errcode = $like->errcode;
				$this->_errmsg = $like->errmsg;
			}
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}

		// 输出结果
		$this->_result = array(
			'like' => $res_like
		);

		return true;
	}

}

