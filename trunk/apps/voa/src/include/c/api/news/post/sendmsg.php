<?php
/**
 * voa_c_api_news_post_sendmsg
 * 未读人员微信消息提醒
 * $Author$
 * $Id$
 */
class voa_c_api_news_post_sendmsg extends voa_c_api_news_abstract {

	//不强制登录，允许外部访问
	protected function _before_action( $action ) {

		$this->_require_login = false;
		if( ! parent::_before_action( $action ) ) {
			return false;
		}
	
		return true;
	}

	public function execute() {

		// 请求的参数
		$fields = array(
			// 新闻公告ID
			'ne_id' => array('type' => 'string', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		$ne_id = $this->_params['ne_id'];
		if(!$ne_id){
			return $this->_set_errcode(voa_errcode_oa_news::NE_ID_ERROR);
		}
		//取得公告
		$s_news = &service::factory('voa_s_oa_news');
		$news = $s_news->get($ne_id);
		if (!$news) {
			return $this->_set_errcode(voa_errcode_oa_news::NEWS_NOT_EXIST);
		}
		//判断上次推送时间
		$now_time = time();
		if(($now_time - $news['send_no_time']) < 60*60*6){
			return $this->_set_errcode(voa_errcode_oa_news::ERR_SEND_NO_READ);
		}
		$data = array('send_no_time' => $now_time);
		$s_news->update($ne_id, $data);

		//获取agentid
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');

		startup_env::set('pluginid', $plugins[$this->_p_sets['pluginid']]['cp_pluginid']);
		startup_env::set('agentid', $plugins[$this->_p_sets['pluginid']]['cp_agentid']);
		$uda = &uda::factory('voa_uda_frontend_news_read');

		//获取未读人员列表
		$m_uids = $uda->get_unread_muid($ne_id);
		if (!empty($m_uids)) {
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $serv_m->fetch_all_by_ids($m_uids);
			$viewurl = '';
			$msg_title = '';
			$msg_desc = '';
			$touser = implode('|', array_column($users, 'm_openid'));
			$this->get_view_url($viewurl, $news['ne_id']);
			$msg_url = $viewurl;
			$msg_picurl = voa_h_attach::attachment_url($news['cover_id'], 0);

			if ($news['is_secret'] == 1) {
				$msg_title .= '[保密]';
			}
			$msg_title .= rhtmlspecialchars($news['title']);
			if ($news['summary']) {
				$msg_desc .= '摘要：'.rhtmlspecialchars($news['summary']);
			}
			// 发送消息
			voa_h_qymsg::push_news_send_queue( $this->session, $msg_title, $msg_desc, $msg_url, $touser, array(), $msg_picurl);
		} else {
			//未读人员不存在
			return $this->_set_errcode(voa_errcode_oa_news::ERR_NO_READER);
		}

	}
}
