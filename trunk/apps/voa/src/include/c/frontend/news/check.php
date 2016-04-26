<?php
/**
 * voa_c_frontend_news_check
 * 新闻公告审核
 * @date: 2015年5月20日
 * @author: kk
 * @version:
 */

class voa_c_frontend_news_check extends voa_c_frontend_news_base {
	public function execute() {
		//当前用户
		$uid = startup_env::get('wbs_uid');
		$params['m_uid'] = $uid;
		//公告id
		$params['news_id'] = rintval($this->request->get('ne_id'));
		try {
			// 获取数据
			$result = array();
			$uda = &uda::factory('voa_uda_frontend_news_check');
			$uda-> is_check($params);//判断用户是否有审核权限
			$uda->check_news($params, $result);//获取内容
		} catch (help_exception $h) {
			$this->_error_message($h->getMessage());
			return false;
		} catch (Exception $e) {
			logger::error($e);
			$this->_error_message($e->getMessage());
			return false;
		}
		$viewurl = '';
		$this->get_view_url($viewurl, $params['news_id']);//生成详情页链接
		$this->view->set('result', $result);
		$this->view->set('viewurl', $viewurl);
		$this->view->set('ne_id', $params['news_id']);
		$this->_output('mobile/news/check');
	}
}
