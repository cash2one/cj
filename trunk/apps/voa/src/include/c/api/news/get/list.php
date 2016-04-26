<?php
/**
 * voa_c_api_news_list
 * 获取新闻公告
 * $Author$
 * $Id$
 */

class voa_c_api_news_get_list extends voa_c_api_news_abstract {

	public function execute() {

		// 请求的参数
		$fields = array(
			// 分类ID
			'nca_id' => array('type' => 'string', 'required' => true),
			'keyword' => array('type' => 'string', 'required' => false),//搜索关键字
			'limit' => array('type' => 'int', 'required' => false),
			'page' => array('type' => 'int', 'required' => false),
		);
		//检查参数
		if (!$this->_check_params($fields)) {
			return false;
		}
		if (!$this->_params['page']) {
			$this->_params['page'] = 1;
		}
		if (!$this->_params['limit']) {
			$this->_params['limit'] = 10;
		}
		//当前用户
		$uid = startup_env::get('wbs_uid');
		$this->_params['current'] = $uid;
		
		$this->_params['m_uid'] = $this->_member['m_uid'];
		
		try{
			// 获取数据列表
			$result = array();
			$uda = &uda::factory('voa_uda_frontend_news_my');
			$uda->list_my_news($this->_params,  $result);
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}

		// 输出结果
		$this->_result = array(
			'page' => $this->_params['page'],
			'limit' => $this->_params['limit'],
			'list' => empty($result) ? array() : array_values($result)
		);

		return true;
	}

}

