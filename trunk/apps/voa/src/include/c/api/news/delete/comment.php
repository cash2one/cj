<?php
/**
 * voa_c_api_news_delete_comment
 * 删除新闻公告评论
 * $Author$
 * $Id$
 */

class voa_c_api_news_delete_comment extends voa_c_api_news_abstract {

	public function execute() {

		// 请求的参数
		$fields = array(
			// 新闻公告评论ID
			'ncomm_id' => array('type' => 'int', 'required' => true),
		);

		if (!$this->_check_params($fields)) {
			return false;
		}

		$ncomm_id = $this->_params['ncomm_id'];

		try{
			// 获取数据
			$result = array();
			$uda = &uda::factory('voa_uda_frontend_news_comment_delete');
			$uda->delete_comment($ncomm_id);
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}

		// 输出结果
		$this->_result = array(
			'ncomm_id' => $ncomm_id
		);

		return true;
	}

}

