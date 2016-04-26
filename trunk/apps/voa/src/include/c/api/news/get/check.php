<?php
/**voa_c_api_news_get_check
 * 获取审核公告的审核详情
 * @date: 2015年5月20日
 * @author: kk
 * @version:
 */

class voa_c_api_news_get_check extends voa_c_api_news_abstract {
	public function execute() {

		// 请求的参数
		$fields = array(
			// 分类ID
			'nca_id' => array('type' => 'string', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		//当前用户
		$uid = startup_env::get('wbs_uid');
		$this->_params['current'] = $uid;
		$this->_params['m_uid'] = $this->_member['m_uid'];

		try{
			// 获取数据
			$result = array();
			$uda = &uda::factory('voa_uda_frontend_news_check');
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
			'result' => empty($result) ? array() : array_values($result)
		);

		return true;
	}
}
