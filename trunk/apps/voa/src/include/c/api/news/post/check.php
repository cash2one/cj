<?php
/**
 * voa_c_api_news_post_check
 * 新闻公告审核处理
 * Created by PhpStorm.
 * User: kk
 * Date: 2015/5/20
 * Time: 18:56
 */

class voa_c_api_news_post_check extends voa_c_api_news_abstract {
	public function execute() {
		// 请求的参数
		$fields = array(
			// 新闻公告ID
			'ne_id' => array('type' => 'int', 'required' => true),
			//审核内容
			'content' => array('type' => 'string', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		if($this->_params['content']) {
			$this->_params['is_check'] = 3;
		} else {
			$this->_params['is_check'] = 2;
		}
		$this->_params['m_uid'] = startup_env::get('wbs_uid');

		$request['m_uid'] = $this->_params['m_uid'];
		$request['news_id'] = $this->_params['ne_id'];
		try{
			// 获取数据
			$result = array();

			$uda = &uda::factory('voa_uda_frontend_news_check');
			$uda-> is_check($request);//判断用户是否已处理审核
			$uda->update_check($this->_params, $result);
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}

		$title = $this->get($this->_params['m_uid']);

		if( $result ) {
			$this->_check_to_queue($this->_params);
		}

		// 输出结果
		$this->_result = array(
			'ne_id' => $this->_params['ne_id'] ,
			'update' => $result
		);

		return true;
	}
}
