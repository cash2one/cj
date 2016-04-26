<?php
/**
 * voa_c_admincp_office_news_base
 * 企业后台/微办公管理/新闻公告/基本控制器
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_news_base extends voa_c_admincp_office_base {

	protected $_p_sets = array();
	protected $_uda_base = null;
	//新闻公告分类
	protected $_categories = array();
	//新闻公告状态
	protected  $status = array(
		voa_d_oa_news::IS_DRAFT => '草稿',
		voa_d_oa_news::IS_PUBLISH => '已发布',
	);

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		//FIXME ！！！涉及指定应用更新问题
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.news.setting', 'oa');
		$s_category = new voa_s_oa_news_category();
		$this->_categories = $s_category->list_all();
		return true;
	}

	protected function _after_action($action) {

		parent::_after_action($action);
		return true;
	}

	/**
	 * 获取查看详情的url
	 * @param string $url url地址
	 * @param int $af_id 预览信息id
	 * @return boolean
	 */
	public function get_view_url(&$url, $ne_id) {
		/** 组织查看链接 */
		$scheme = config::get('voa.oa_http_scheme');

		$url = voa_wxqy_service::instance()->oauth_url($scheme . $this->_setting['domain'] . '/frontend/news/view?newsId=' . $ne_id . '&action=view&pluginid='. startup_env::get('pluginid'));
		return true;
	}

	/**
	 * 获取审核消息url
	 * @param $url
	 * @param $ne_id 公告id
	 */
	public function get_check_url(&$url, $ne_id) {
		$scheme = config::get('voa.oa_http_scheme');

		$url = voa_wxqy_service::instance()->oauth_url($scheme . $this->_setting['domain'] . '/frontend/news/bulletin?newsId=' . $ne_id . '&pluginid=' . startup_env::get('pluginid'));
		return true;
	}

	/**
	 * 发送图文提醒消息
	 * @param array $news
	 * @return boolean
	 */
	protected function _to_queue($news) {

		// 获取agentid
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		startup_env::set('pluginid', $this->_p_sets['pluginid']);
		startup_env::set('agentid', $plugins[$this->_p_sets['pluginid']]['cp_agentid']);

		/** 组织查看链接 */
		$viewurl = '';
		$msg_title = '';
		$msg_desc = '';
		if (!empty($news['is_check'])) {
			$this->get_check_url($viewurl, $news['ne_id']);
			//消息预览发送
			$s_news_check = new voa_s_oa_news_check();
			$news_check = $s_news_check->list_check_users($news['ne_id']);
			if ($news_check) {
				$m_uids = array_column($news_check, 'm_uid');
				$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
				$users = $serv_m->fetch_all_by_ids($m_uids);
				$touser = implode('|', array_column($users, 'm_openid'));
				$toparty = '';
			}
			//发送人名字查询
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$author = $serv_m->fetch($news['m_uid']);
			//$msg_title .= '您收到一条预览消息';
			$msg_title .= "【预览】".rhtmlspecialchars($news['title']);
			$msg_desc .= '发送人：' . $author['m_username'];
		} else {
			//正式发布
			$this->get_view_url($viewurl, $news['ne_id']);
			if ($news['is_all'] == 1) { //如果是发给全公司
				$touser = '@all';
				$toparty = '';
			} else { //如果是发给选择的人员和部门
				$s_news_right = new voa_s_oa_news_right();
				$news_right = $s_news_right->list_rights_for_single_news($news['ne_id']);
				if ($news_right) {
					$m_uids = array_column($news_right, 'm_uid');
					$cd_ids = array_column($news_right, 'cd_id');
					$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
					$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
					$users = $serv_m->fetch_all_by_ids($m_uids);
					$depms = $serv_d->fetch_all_by_key($cd_ids);
					$touser = implode('|', array_column($users, 'm_openid'));
					$toparty = implode('|', array_column($depms, 'cd_qywxid'));
				}

			}
			if ($news['is_secret'] == 1) {
				$msg_title .= '[保密]';
			}
			$msg_title .= rhtmlspecialchars($news['title']);
			if ($news['summary']) {
				$msg_desc .= '摘要：' . rhtmlspecialchars($news['summary']);
			}
		}
		$msg_url = $viewurl;
		$msg_picurl = voa_h_attach::attachment_url($news['cover_id'], 0);
		// 发送消息
		voa_h_qymsg::push_news_send_queue($this->session, $msg_title, $msg_desc, $msg_url, $touser, $toparty, $msg_picurl);

		return true;
	}
	/**
	 * 发送多图文提醒消息
	 * @param array $news
	 * @return boolean
	 */
	protected function _to_queue_s($news) {
		// 获取agentid
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		startup_env::set('agentid', $plugins[$this->_p_sets['pluginid']]['cp_agentid']);
		$msg_title = array(); //标题
		$msg_desc = array();//摘要
		$viewurl = array();
		foreach ($news as $val) {
			/** 组织查看链接 */
			$this->get_view_url($viewurl, $val['ne_id']);

			if ($val['is_check'] == 1) {
				//消息审核发送
				$s_news_check = new voa_s_oa_news_check();
				$news_check = $s_news_check->list_check_users($val['ne_id']);
				if ($news_check) {
					$m_uids = array_column($news_check, 'm_uid');
					$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
					$users = $serv_m->fetch_all_by_ids($m_uids);
					$touser[] = implode('|', array_column($users, 'm_openid'));
					$toparty[] = '';
				}
				$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
				$author = $serv_m->fetch($val['m_uid']);;
				//$msg_title[] = '您收到一条预览消息';
				$msg_title []= "【预览】".rhtmlspecialchars($news['title']);
				$msg_desc[] = '发送人：' . $author['m_username'];
			} else {
				//正式发布
				if ($val['is_all'] == 1) { //如果是发给全公司
					$touser[] = '@all';
					$toparty[] = '';
				} else { //如果是发给选择的人员和部门
					$s_news_right = new voa_s_oa_news_right();
					$news_right = $s_news_right->list_rights_for_single_news($val['ne_id']);
					if ($news_right) {
						$m_uids = array_column($news_right, 'm_uid');
						$cd_ids = array_column($news_right, 'cd_id');
						$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
						$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
						$users = $serv_m->fetch_all_by_ids($m_uids);
						$depms = $serv_d->fetch_all_by_key($cd_ids);

						$touser[] = implode('|', array_column($users, 'm_openid'));
						$toparty[] = implode('|', array_column($depms, 'cd_qywxid'));
					}
				}
				$title = '';
				if ($val['is_secret'] == 1) {
					$title .= '[保密]';
				}
				$title .= rhtmlspecialchars($val['title']);
				$msg_title[] = $title;
				if ($val['summary']) {
					$msg_desc[] = '摘要：' . rhtmlspecialchars($val['summary']);
				}

			}
			$msg_url[] = $viewurl;
			$msg_picurl[] = voa_h_attach::attachment_url($val['cover_id'], 0);

		}
		// 发送消息
		voa_h_qymsg::push_news_send_queue($this->session, $msg_title, $msg_desc, $msg_url, $touser, $toparty, $msg_picurl);
	}
}
