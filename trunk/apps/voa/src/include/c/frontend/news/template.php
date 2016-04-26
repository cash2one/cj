<?php
/**
 * voa_c_frontend_news_template
 * 新闻公告模板列表
 * @date: 2015年5月19日
 * @author: kk
 * @version:
 */
class voa_c_frontend_news_template extends voa_c_frontend_news_base {
	//进行权限判断

	public function execute() {
		$user = startup_env::get('wbs_uid'); //用户id
		//获取用户权限
		try {
			$result = '';
			$uda = &uda::factory('voa_uda_frontend_news_issue');
			$uda->issue(array('m_uid' => $user), $result);
		} catch (help_exception $h) {
			$this->_error_message($h->getMessage());
			return false;
		} catch (Exception $e) {
			logger::error($e);
			$this->_error_message($e->getMessage());
			return false;
		}
		$this->view->set('body_id', 'oa-sp-launch');
		$this->_output('mobile/news/template');
	}
}
