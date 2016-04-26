<?php
/**
 * 新增投票
 * $Author$
 * $Id$
 */

class voa_c_frontend_vote_new extends voa_c_frontend_vote_base {

	public function execute() {
		/** 处理提交 */
		if ($this->_is_post()) {
			$this->_new();
		}

		/** 起始年月日 */
		$range_start = rgmdate(startup_env::get('timestamp'), 'Y-m-d');

		$this->view->set('form_action', "/vote/new?handlekey=post");
		$this->view->set('range_start', $range_start);
		$this->view->set('start_selected', $range_start);
		$this->view->set('end_selected', $range_start);
		$this->view->set('ac', $this->action_name);
		$this->view->set('vote', array());

		$this->_output('vote/post');
	}

	/** 获取可投票用户 */
	function _get_permit_user() {
		$friend = (int)$this->request->get('friend');
		if (empty($friend)) {
			return array(array(array('m_uid' => 0)), voa_d_oa_vote::FRIEND_ONLY);
		}

		$uids = trim($this->request->get('uids'));
		$uid_arr = explode(',', $this->request->get('uids'));
		/** 剔重 */
		$uid_arr = array_unique($uid_arr);
		/** 剔除当前用户和空字串 */
		$uid_arr = array_diff($uid_arr, array(''));
		$users = array();
		$friend = voa_d_oa_vote::FRIEND_ALL;
		if (!empty($uid_arr)) {
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $serv_m->fetch_all_by_ids($uid_arr);
		}

		/** 根据用户数判断 */
		if (!empty($users) ) {
			$friend = voa_d_oa_vote::FRIEND_ONLY;
			$users[$this->_user['m_uid']] = $this->_user;
		} else {
			$users = array(array('m_uid' => 0));
		}

		return array($users, $friend);
	}

	/** 新增投票操作 */
	function _new() {
		/** 投票开始/结束时间 */
		$begintime = rstrtotime($this->request->get('begintime'));
		$endtime = rstrtotime($this->request->get('endtime')) + 86400;
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

		/** 所有选项 */
		$options = array();
		foreach ($this->request->get('options') as $v) {
			$v = trim($v);
			if (!empty($v)) {
				$options[] = $v;
			}
		}

		if (2 > count($options)) {
			$this->_error_message('请输入投票选项, 最少2个选项');
		}

		/** 最少/多项 */
		$minchoices = intval($this->request->get('minchoices'));
		$maxchoices = intval($this->request->get('maxchoices'));
		$minchoices = 1 > $minchoices ? 1 : $minchoices;
		$maxchoices = $minchoices > $maxchoices ? $minchoices : $maxchoices;

		/** 对内还是对外 */
		$inout = intval($this->request->get('inout'));

		/** 获取指定投票用户 */
		list($users, $friend) = $this->_get_permit_user();

		$serv_v = &service::factory('voa_s_oa_vote', array('pluginid' => startup_env::get('pluginid')));
		$serv_vo = &service::factory('voa_s_oa_vote_option', array('pluginid' => startup_env::get('pluginid')));
		$serv_vpu = &service::factory('voa_s_oa_vote_permit_user', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv_v->begin();

			/** 入库 */
			$vote = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'v_subject' => $subject,
				'v_message' => $message,
				'v_begintime' => $begintime,
				'v_endtime' => $endtime,
				'v_friend' => $friend,
				'v_ismulti' => self::IS_SINGLE,
				'v_minchoices' => $minchoices,
				'v_maxchoices' => $maxchoices,
				'v_isopen' => $this->_p_sets['verify'] ? self::IS_CLOSE : self::IS_OPEN,
				'v_inout' => $inout,
				'v_status' => 0 == $this->_p_sets['verify'] ? voa_d_oa_vote::STATUS_APPROVE : voa_d_oa_vote::STATUS_NORMAL
			);
			$v_id = $serv_v->insert($vote, true);
			$vote['v_id'] = $v_id;

			/** 选项入库 */
			foreach ($options as $v) {
				$serv_vo->insert(array(
					'v_id' => $v_id,
					'vo_option' => $v
				));
			}

			/** 投票用户 */
			foreach ($users as $u) {
				$serv_vpu->insert(array(
					'v_id' => $v_id,
					'm_uid' => $u['m_uid'],
					'm_username' => $u['m_username']
				));
			}

			$serv_v->commit();
		} catch (Exception $e) {
			$serv_v->rollback();
			$this->_error_message('投票新增操作失败');
		}

		/** 消息发送 */
		$this->_to_queue($vote, $users);

		/** 增加成功 */
		$this->_success_message('投票新增操作成功', "/vote/view/{$v_id}");
	}

	/**
	 * 把消息推入队列
	 * @param array $vote 投票信息
	 * @param array $userlist 接收用户
	 */
	protected function _to_queue($vote, $userlist) {
		/** 整理需要接收消息的用户 */
		$users = array();
		if (voa_d_oa_vote::FRIEND_ONLY == $vote['v_friend']) {
			foreach ($userlist as $u) {
				if (startup_env::get('wbs_uid') == $u['m_uid']) {
					continue;
				}

				$users[] = $u['m_openid'];
			}
		} else {
			$users = array(0 => '@all');
		}

		/** 判断是否需要发送 */
		if (empty($users)) {
			return true;
		}

		/** 格式化 */
		$fmt = &uda::factory('voa_uda_frontend_vote_format');
		if (!$fmt->vote($vote)) {
			$this->_error_message($fmt->error);
		}

		/** 组织查看链接 */
		$viewurl = '';
		$this->get_view_url($viewurl, $vote['v_id']);
		$content = "微评选：".$vote['v_subject']."\n"
				 . "开始时间：".$vote['_begintime']."\n"
				 . "结束时间：".$vote['_endtime']."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";

		$data = array(
			'mq_touser' => implode('|', $users),
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => $this->_plugin['cp_agentid'],
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($data);

		/** 写入 cookie, 刷新页面时发送 */
		$this->set_queue_session(array($data['mq_id']));
	}
}


