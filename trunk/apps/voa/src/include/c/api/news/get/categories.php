<?php
/**
 * voa_c_api_news_get_categories
 * 获取新闻公告类型子菜单
 * $Author$
 * $Id$
 */

class voa_c_api_news_get_categories extends voa_c_api_news_abstract {

	public function execute() {

		// 请求的参数
		$fields = array(
			// 分类ID
			'nca_id' => array('type' => 'string', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		$nca_id = $this->_params['nca_id'];
		try{
			// 获取数据
			$uda = &uda::factory('voa_uda_frontend_news_category');
			$category = $uda->get_category($nca_id);
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}

		// 输出结果
		$this->_result = array(
			'list' => isset($category['nodes']) ? array_values($category['nodes']) : array()
		);

		return true;
	}

}

