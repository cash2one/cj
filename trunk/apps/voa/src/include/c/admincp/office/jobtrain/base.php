<?php
/**
 * 培训应用基本控制器
 * Create By wowxavi
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_jobtrain_base extends voa_c_admincp_office_base {

	//protected $_p_sets = array();

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}
		//$this->_p_sets = voa_h_cache::get_instance()->get('plugin.jobtrain.setting', 'oa');
		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 发送图文提醒消息
	 * @param array $article
	 * @return boolean
	 */
	protected function _to_queue($article) {
		// 获取agentid
		/*
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		startup_env::set('pluginid', $this->_p_sets['pluginid']);
		startup_env::set('agentid', $plugins[$this->_p_sets['pluginid']]['cp_agentid']);
		*/
		startup_env::set('pluginid', $this->_module_plugin_id);
		startup_env::set('agentid', $this->_module_plugin['cp_agentid']);

		// 权限服务
		$s_right = new voa_s_oa_jobtrain_right();
		/** 组织查看链接 */
		$viewurl = '';
		$msg_title = '';
		$msg_desc = '';
		// 生成详情链接
		$this->get_url($viewurl, $article['id']);
		if (!empty($article['is_preview'])) {
			//消息预览发送
			$article_right = $s_right->list_right_users($article['id']);
			if ($article_right) {
				$m_uids = array_column($article_right, 'm_uid');
				$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
				$users = $serv_m->fetch_all_by_ids($m_uids);
				$touser = implode('|', array_column($users, 'm_openid'));
				$toparty = '';
			}
			$msg_title .= "【预览】".rhtmlspecialchars($article['title']);
			$msg_desc .= '发送人：' . $this->_user['ca_username'];
			// 预览说明
			if(!empty($article['preview_summary'])){
				$msg_desc .= "\n说明：".rhtmlspecialchars($article['preview_summary']);
			}
		} else {
			//正式发布
			if ($article['is_all'] == 1) { //如果是发给全公司
				$touser = '@all';
				$toparty = '';
			} else { //如果是发给选择的人员和部门
				$article_right = $s_right->list_rights_for_single($article['cid']);
				if ($article_right) {
					$m_uids = array_column($article_right, 'm_uid');
					$cd_ids = array_column($article_right, 'cd_id');
					$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
					$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
					$users = $serv_m->fetch_all_by_ids($m_uids);
					$depms = $serv_d->fetch_all_by_key($cd_ids);
					$touser = implode('|', array_column($users, 'm_openid'));
					$toparty = implode('|', array_column($depms, 'cd_qywxid'));
				}
			}
			if ($article['is_secret'] == 1) {
				$msg_title .= '【保密】';
			}
			$msg_title .= rhtmlspecialchars($article['title']);
			if ($article['summary']) {
				$msg_desc .= '摘要：' . rhtmlspecialchars($article['summary']);
			}
		}
		$msg_url = $viewurl;
		$msg_picurl = voa_h_attach::attachment_url($article['cover_id'], 0);
		// 发送消息
		voa_h_qymsg::push_news_send_queue($this->session, $msg_title, $msg_desc, $msg_url, $touser, $toparty, $msg_picurl);

		return true;
	}

	/**
	 * 获取预览url
	 * @param $url
	 * @param $id
	 */
	public function get_url(&$url, $id) {

		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme . $this->_setting['domain'] . '/Jobtrain/Frontend/Index/ArticleDetail?aid=' . $id;
		return true;
	}
}
