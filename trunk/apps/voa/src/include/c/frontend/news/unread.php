<?php
/**
 * voa_c_frontend_news_unread
 * 未读人员列表
 * $Author$
 * $Id$
 */
class voa_c_frontend_news_unread extends voa_c_frontend_news_base {
	public function execute() {
		$ne_id = rintval($this->request->get('ne_id'));
		try {
			//判断新闻ID是否合法
			if(!$ne_id){
				return voa_h_func::throw_errmsg(voa_errcode_oa_news::NE_ID_ERROR);
			}
			//取得公告
			$s_news = &service::factory('voa_s_oa_news');
			$news = $s_news->get($ne_id);
			//如果新闻不存在抛出错误
			if (!$news) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_news::NEWS_NOT_EXIST);
			}
			//查看当前用户是否有权限查看未读人员列表
			if($news['m_uid'] != $this->_user['m_uid']){
				return voa_h_func::throw_errmsg(voa_errcode_oa_news::NEWS_NO_USER_RIGHT);
			}
			//读取未读人员列表
			$uda = &uda::factory('voa_uda_frontend_news_read');
			$userids = array();
			$userids =$uda->get_unread_muid($ne_id);
			//如果没有数据则提示
			if(empty($userids)){
				return voa_h_func::throw_errmsg('1000622:没有数据');
			}
		} catch (help_exception $h) {
			$this->_error_message($h->getMessage());
			return false;
		} catch (Exception $e) {
			logger::error($e);
			$this->_error_message($e->getMessage());
			return false;
		}
		//注入变量
		$this->view->set('userids',$userids);
		//输出模板
		$this->_output('mobile/news/unread');
	}
}
