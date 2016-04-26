<?php
/**
 * 新增微信墙主题
 * $Author$
 * $Id$
 */

class voa_c_frontend_wxwall_new extends voa_c_frontend_wxwall_base {

	public function execute() {
		$sets = voa_h_cache::get_instance()->get('plugin.wxwall.setting', 'oa');
		/** 处理提交 */
		if ($this->_is_post()) {
			/** 微信墙开始/结束时间 */
			$begintime = rstrtotime($this->request->get('begintime').' 00:00');
			$endtime = rstrtotime($this->request->get('endtime').' 23:59');
			if ($endtime < startup_env::get('timestamp')) {
				$this->_error_message('结束时间必须大于当前时间');
			}

			if ($begintime >= $endtime) {
				$this->_error_message('结束时间必须大于开始时间');
			}

			/** 主题/内容 */
			$subject = trim($this->request->get('subject'));
			$message = trim($this->request->get('message'));
			if (0 >= strlen($subject)) {
				$this->_error_message('主题不能为空');
			}

			/** 是否启用/回复是否需要验证/最大回复数 */
			$postverify = intval($this->request->get('postverify'));
			$maxpost = intval($this->request->get('maxpost'));

			$serv_w = &service::factory('voa_s_oa_wxwall', array('pluginid' => startup_env::get('pluginid')));
			try {
				$serv_w->begin();

				/** 入库 */
				$wxwall = array(
					'm_uid' => startup_env::get('wbs_uid'),
					'm_username' => startup_env::get('wbs_username'),
					'ww_subject' => $subject,
					'ww_message' => $message,
					'ww_begintime' => $begintime,
					'ww_endtime' => $endtime,
					'ww_isopen' => self::IS_OPEN,
					'ww_postverify' => $postverify,
					'ww_maxpost' => $maxpost,
					'ww_status' => 0 == $sets['verify'] ? self::STATUS_APPROVE : self::STATUS_NORMAL
				);
				$ww_id = $serv_w->insert($wxwall, true);

				/** 管理用户名 */
				$admin = 'wx_'.rstrtolower(random(2)).$ww_id;
				/** 密码 */
				$passwd = random(8);
				/** 干扰码 */
				$salt = random(4);
				$serv_w->update(array(
					'ww_admin' => $admin,
					'ww_passwd' => $this->_generate_passwd(md5($passwd), $salt),
					'ww_salt' => $salt
				), array('ww_id' => $ww_id));

				/** 用户名/密码 */
				$wxwall['ww_admin'] = $admin;
				$wxwall['_passwd'] = $passwd;

				$serv_w->commit();
			} catch (Exception $e) {
				$serv_w->rollback();
				$this->_error_message('微信墙新增操作失败');
			}

			/** 推入消息队列 */
			$this->_to_queue($wxwall);

			/** 增加成功 */
			$this->_success_message('微信墙新增操作成功', "/wxwall/view/{$ww_id}");
		}

		/** 审核人 */
		$uid_str = $sets['verify_uid'];
		$vy_uids = explode(',', $uid_str);
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$vy_users = $serv_m->fetch_all_by_ids($vy_uids);
		voa_h_user::push($vy_users);
		/** 起始时间 */
		$this->view->set('range_start', rgmdate(startup_env::get('timestamp'), 'Y-m-d'));
		/** action */
		$this->view->set('form_action', '/wxwall/new?handlekey=post');
		$this->view->set('vy_users', $vy_users);
		$this->view->set('verify', $sets['verify']);
		$this->view->set('wxwall', array());
		$this->view->set('ac', $this->action_name);
		$this->view->set('navtitle', '新微信墙');

		$this->_output('wxwall/post');
	}

	/**
	 * 把消息推入队列
	 * @param array $wxwall 微信墙信息
	 */
	protected function _to_queue($wxwall) {
		$content = "[主题] ".$wxwall['ww_subject']."\r"
				 . '[管理地址] '.config::get(startup_env::get('app_name') . '.oa_http_scheme').$this->_setting['domain'].'/'.config::get('voa.wxwall_path')."\r"
				 . '[管理用户] '.$wxwall['ww_admin']."\r"
				 . '[管理密码] '.$wxwall['_passwd'];

		$data = array(
			'mq_touser' => $this->_user['m_openid'],
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => (int)$this->_plugin['cp_agentid'],
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($data);

		/** 写入 cookie, 刷新页面时发送 */
		$this->set_queue_session(array($data['mq_id']));
	}
}

