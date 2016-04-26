<?php
/**
 * voa_c_api_news_abstract
 * 新闻公告基础控制器
 * $Author$
 * $Id$
 */

abstract class voa_c_api_news_abstract extends voa_c_api_base {
	/** 插件id */
	protected $_pluginid = 0;
	// 插件名称
	protected $_pluginname = 'news';
	// 表格名称
	protected $_tname = 'news';

	protected  $_perpage = 10;

	protected $_p_sets = array();
	// 管理后台 cookie
	protected $_cookie_data = array();

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 身份检查
	 * @return void
	 */
	protected function _access_check() {

		// 取后台登录信息
		$uda_member_get = &uda::factory('voa_uda_frontend_adminer_get');
		// cookie 信息
		$uda_member_get->adminer_auth_by_cookie($this->_cookie_data, $this->session);
		if (!empty($this->_cookie_data['uid']) && 0 < $this->_cookie_data['uid']) {
			// 如果后台登陆信息存在, 则清理前台登陆账号
			$this->session->remove('uid');
			$this->_require_login = false;
		}

		return parent::_access_check();
	}

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		//涉及指定应用更新问题
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.news.setting', 'oa');

		/** 取应用插件信息 */
		$pluginid = $this->_p_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		if (array_key_exists($pluginid, $plugins)) {
			$this->_plugin = $plugins[$pluginid];
			startup_env::set('agentid', $this->_plugin['cp_agentid']);
			/** 加载提示语言 */
			language::load_lang($this->_plugin['cp_identifier']);
		}

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

		$url = voa_wxqy_service::instance()->oauth_url($scheme.$this->_setting['domain'] . '/frontend/news/bulletin?newsId='.$ne_id.'&pluginid='.startup_env::get('pluginid'));
		return true;
	}

	/**
	 * 获取修改消息url
	 */
	public function get_add_url(&$url, $ne_id) {
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme.$this->_setting['domain'].'/frontend/news/add?ne_id='.$ne_id.'&pluginid='.startup_env::get('pluginid'));

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
		startup_env::set('agentid', $plugins[$this->_p_sets['pluginid']]['cp_agentid']);
		startup_env::set('pluginid', $this->_p_sets['pluginid']);
		/** 组织查看链接 */
		$viewurl = '';
		$msg_title = '';
		$msg_desc = '';
		if($news['is_check'] == 1) {
			$this->get_check_url($viewurl, $news['ne_id']);
			//消息审核发送
			$s_news_check = new voa_s_oa_news_check();
			$news_check = $s_news_check->list_check_users($news['ne_id']);
			if($news_check) {
				$m_uids = array_column($news_check, 'm_uid');
				$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
				$users = $serv_m->fetch_all_by_ids($m_uids);
				$touser = implode('|', array_column($users, 'm_openid'));
				$toparty = '';
			}
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$author = $serv_m->fetch($news['m_uid']);;
			$msg_title .= $author['m_username'].'发来一条预览公告';
			$msg_desc .= "标题：".$news['title']."\n";
			$msg_desc .= '发送人：'.$author['m_username'];
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
                    // 判断当前用户是否为空
                    if (empty($touser)) {
                        $touser = '';
                    }

                    // 判断当前部门是否为空
					$toparty = implode('|', array_column($depms, 'cd_qywxid'));
                    if (empty($toparty)) {
                        $toparty = '';
                    }
				}
			}
			if ($news['is_secret'] == 1) {
				$msg_title .= '[保密]';
			}
			$msg_title .= rhtmlspecialchars($news['title']);
			if ($news['summary']) {
				$msg_desc .= '摘要：'.rhtmlspecialchars($news['summary']);
			}
		}
		$msg_url = $viewurl;
		$msg_picurl = voa_h_attach::attachment_url($news['cover_id'], 0);
		// 发送消息
		voa_h_qymsg::push_news_send_queue($this->session, $msg_title, $msg_desc, $msg_url, $touser, $toparty, $msg_picurl, 0, 0, -1);

		$mq_ids = (string)$this->session->get('mq_ids');
		$ids = explode(',', $mq_ids);

		/** 读取待发送消息队列 */
		$serv = &service::factory('voa_s_oa_msg_queue', array('pluginid' => 0));
		$list = $serv->fetch_unsend_by_ids($ids);

		$serv_qy = voa_wxqy_service::instance();
		foreach ($list as $q) {
			startup_env::set('pluginid', $q['cp_pluginid']);
			switch ($q['mq_msgtype']) {
				case voa_h_qymsg::MSGTYPE_TEXT:
					$serv_qy->post_text($q['mq_message'], $q['mq_agentid'], $q['mq_touser'], $q['mq_toparty']);
					break;
				case voa_h_qymsg::MSGTYPE_NEWS:
					$serv_qy->post_news(unserialize($q['mq_message']), $q['mq_agentid'], $q['mq_touser'], $q['mq_toparty']);
					break;
			}
		}

		$serv->delete_by_ids($ids);
		$this->session->remove('mq_ids');

		return true;
	}

	/**
	 * 发送图文预览提醒消息
	 * @param array $news_check
	 * @return boolean
	 */
	protected function _check_to_queue($news_check) {
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		startup_env::set('agentid', $plugins[$this->_p_sets['pluginid']]['cp_agentid']);
		startup_env::set('pluginid', $this->_p_sets['pluginid']);
		/** 组织查看链接 */
		$viewurl = '';
		$this->get_add_url($viewurl, $news_check['ne_id']);

		$msg_title = '';
		$msg_desc = '';
		if($news_check['is_check'] == 2) {
			$this->get_add_url($viewurl, $news_check['ne_id']);
			$s_news = new voa_s_oa_news();
			$news = $s_news->get($news_check['ne_id']);

			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $serv_m->fetch($news['m_uid']); //接收人
			$check_user = $serv_m->fetch($news_check['m_uid']);

			$touser =  $users['m_openid'];
			$toparty = '';

			$msg_title .= '您发起的预览收到一条回复';
			$msg_desc .= '标题:'.rhtmlspecialchars($news['title'])."\n";
			$msg_desc .= '预览人：'.$check_user['m_username'];
		}
		if($news_check['is_check'] == 3){
			$this->get_add_url($viewurl, $news_check['ne_id']);
			$s_news = new voa_s_oa_news();
			$news = $s_news->get($news_check['ne_id']);

			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $serv_m->fetch($news['m_uid']);
			$check_user = $serv_m->fetch($news_check['m_uid']);
			$touser = $users['m_openid'];
			$toparty = '';
			$msg_title .= $check_user['m_username'].'回复了您的预览公告';
			$msg_desc .= '标题:'.rhtmlspecialchars($news['title'])."\n";
			$msg_desc .= '预览人：'.$check_user['m_username']."\n";
			$msg_desc .= '回复:'.rhtmlspecialchars($news_check['content']);
		}
		$msg_url = $viewurl;
		$msg_picurl = '';
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
		startup_env::set('pluginid', $this->_p_sets['pluginid']);
		$msg_title = array(); //标题
		$msg_desc = array();//摘要
		$viewurl = array();
		$msg_url = array();
		$msg_picurl = array();
		$i = 0 ;
		foreach($news as $val) {
			/** 组织查看链接 */
			if($val['is_check'] == 1) {
				$this->get_check_url($viewurl, $val['ne_id']);
				//消息审核发送
				if($i == 0){
					$s_news_check = new voa_s_oa_news_check();
					$news_check = $s_news_check->list_check_users($val['ne_id']);
					if($news_check) {
						$m_uids = array_column($news_check, 'm_uid');
						$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
						$users = $serv_m->fetch_all_by_ids($m_uids);
						$touser = implode('|', array_column($users, 'm_openid'));
						$toparty = '';
					}
				}

				$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
				$author = $serv_m->fetch($val['m_uid']);;
				$msg_title[] = '【预览】'.$val['title'];
				$msg_desc[] = '发送人：'.$author['m_username'];
			} else {
				//正式发布
				if($i == 0) {
					if ($val['is_all'] == 1) { //如果是发给全公司
						$touser = '@all';
						$toparty = '';
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
							$touser = implode('|', array_column($users, 'm_openid'));
							$toparty = implode('|', array_column($depms, 'cd_qywxid'));
						}
					}
				}
				$this->get_view_url($viewurl, $val['ne_id']);
				$title = '';
				if($val['is_secret'] == 1 && $val['is_check'] == 1){
					$title .= '[预览][保密]';
				}else{
					if ($val['is_secret'] == 1) {
						$title .= '[保密]';
					}elseif ($val['is_check'] == 1){
						$title .= '[预览]';
					}
				}
				$title .= rhtmlspecialchars($val['title']);
				$msg_title[] = $title;
				if ($val['summary']) {
					$msg_desc[]= '摘要：'.rhtmlspecialchars($val['summary']);
				}
			}
			$msg_url[] = $viewurl;
			$msg_picurl[] = voa_h_attach::attachment_url($val['cover_id'], 0);
			++$i;
		}
		// 发送消息
		voa_h_qymsg::push_news_send_queue($this->session, $msg_title, $msg_desc, $msg_url, $touser, $toparty, $msg_picurl);
	}

}
