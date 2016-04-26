<?php
/**
 * 任务推进
 * $Author$
 * $Id$
 */

class voa_c_frontend_project_advanced extends voa_c_frontend_project_base {
	/** 任务id */
	protected $_p_id = 0;
	/** 任务信息 */
	protected $_project = array();
	/** 任务参与人 uid */
	protected $_p_uids = array();
	/** 任务参与人信息 */
	protected $_p_users = array();
	/** 退出任务的人员 */
	protected $_quit_uids = array();

	public function execute() {
		$this->_p_id = intval($this->request->get('p_id'));
		/** 读取任务信息 */
		$serv_p = &service::factory('voa_s_oa_project', array('pluginid' => startup_env::get('pluginid')));
		$this->_project = $serv_p->fetch_by_id($this->_p_id);
		if (empty($this->_project)) {
			$this->_error_message('该任务不存在或已删除');
		}

		/** 判断是否发起者 */
		if ($this->_project['m_uid'] != startup_env::get('wbs_uid')) {
			$this->_error_message('您没有此权限');
		}

		/** 过滤任务信息 */
		$fmt = uda::factory('voa_uda_frontend_project_format');
		$fmt->project($this->_project);   
		
		/** 读取任务所有用户 */
		$serv_pm = &service::factory('voa_s_oa_project_mem', array('pluginid' => startup_env::get('pluginid')));
		$mems = $serv_pm->fetch_by_p_id($this->_p_id);
		
		foreach ($mems as $m) {
			if (voa_d_oa_project_mem::STATUS_CC == $m['pm_status'] || voa_d_oa_project_mem::STATUS_OUTOF == $m['pm_status']) {
				continue;
			}

			/** 如果是退出状态 */
			if (voa_d_oa_project_mem::STATUS_QUIT == $m['pm_status']) {
				$this->_quit_uids[$m['m_uid']] = $m['m_uid'];
				continue;
			}

			$this->_p_uids[$m['m_uid']] = $m['m_uid'];
		}

		/** 读取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$this->_p_users = $serv_m->fetch_all_by_ids($this->_p_uids);
		voa_h_user::push($this->_p_users);

		/** 处理提交 */
		if ($this->_is_post()) {
			$this->_submit();
			return true;
		}

		/** 所有任务人员uid */
		$this->view->set('proj_uids', implode(',', $this->_p_uids));
		$this->view->set('ac', $this->action_name);
		$this->view->set('users', $this->_p_users);
		$this->view->set('refer', get_referer());
		$this->view->set('p_id', $this->_p_id);

		$this->_output('project/advanced');
	}

	/** 处理提交 */
	protected function _submit() {
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$serv_pm = &service::factory('voa_s_oa_project_mem', array('pluginid' => startup_env::get('pluginid')));
		$serv_pp = &service::factory('voa_s_oa_project_proc', array('pluginid' => startup_env::get('pluginid')));

		$message = trim($this->request->get('message'));
		if (empty($message)) {
			$this->_error_message('推进消息不能为空');
		}

		$project_uids = trim($this->request->get('project_uids'));
		$p_uids = explode(',', $project_uids);
		if (empty($project_uids) || empty($p_uids)) {
			$this->_error_message('任务人员不能为空');
		}

		/** 读取任务人员信息 */
		$new_uids = array_diff($p_uids, $this->_p_uids);
		$new_users = array();
		if (!empty($new_uids)) {
			$new_users = $serv_m->fetch_all_by_ids($new_uids);
			if (empty($new_users)) {
				$this->_error_message('任务人员不能为空');
			}
		}

		/** 数据入库 */
		try {
			$serv_m->begin();

			/** 新增任务人员 */
			foreach ($new_uids as $uid) {
				/** 如果是新加入的用户 */
				if (empty($this->_quit_uids[$uid])) {
					$serv_pm->insert(array(
						'p_id' => $this->_p_id,
						'm_uid' => $uid,
						'm_username' => $new_users[$uid]['m_username'],
						'pm_status' => voa_d_oa_project_mem::STATUS_NORMAL
					));
				} else {
					/** 如果该用户是之前退出的 */
					$serv_pm->update(array(
						'pm_progress' => 0,
						'pm_status' => voa_d_oa_project_mem::STATUS_NORMAL
					), array('p_id' => $this->_p_id, 'm_uid' => $uid));
				}

				/** 非任务初始人员, 增加一条进度信息 */
				$serv_pp->insert(array(
					'p_id' => $this->_p_id,
					'm_uid' => $uid,
					'm_username' => $new_users[$uid]['m_username'],
					'pp_message' => '加入'
				));
			}

			$quit_uids = array_diff($this->_p_uids, $p_uids);
			foreach ($quit_uids as $uid) {
				/** 当剔除发起人时, 则把状态改成不参加即可 */
				if ($uid == $this->_project['m_uid']) {
					$status = voa_d_oa_project_mem::STATUS_OUTOF;
				} else {
					$status = voa_d_oa_project_mem::STATUS_QUIT;
				}

				$serv_pm->update(array(
					'p_id' => $this->_p_id,
					'm_uid' => $uid,
					'm_username' => $this->_p_users[$uid]['m_username'],
					'pm_status' => $status
				), array('p_id' => $this->_p_id, 'm_uid' => $uid));

				/** 退出时, 增加一条退出记录(进度) */
				$serv_pp->insert(array(
					'p_id' => $this->_p_id,
					'm_uid' => $uid,
					'm_username' => $this->_p_users[$uid]['m_username'],
					'pp_message' => '退出'
				));
			}

			$serv_m->commit();
		} catch (Exception $e) {
			$serv_m->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->_error_message('任务新增失败');
		}

		/** 给抄送人发送模板消息 */
		$allusers = array_merge($this->_p_users, $new_users);
		foreach ($quit_uids as $uid) {
			unset($allusers[$uid]);
		}

		/** 把微信消息推入队列 */
		$this->_to_queue($allusers, $message);

		$this->_success_message('任务推进操作成功', '/project/view/'.$this->_p_id);
	}

	/**
	 * 把微信消息推入队列
	 * @param array $users 用户信息列表
	 * @param string $message 推进事项
	 */
	protected function _to_queue($users, $message) {
		/** 取openid */
		$openids = array();
		foreach ($users as $u) {
			$openids[] = $u['m_openid'];
		}

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $this->_project['p_id']);
		$content = "任务推进\n"
				 . "来自：".$this->_project['m_username']."\n"
				 . "任务名：".$this->_project['_subject']."\n"
				 . "推进事项：".$message."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";

		$data = array(
			'mq_touser' => implode('|', $openids),
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => (int)$this->_plugin['cp_agentid'],
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($data);

		/** 写入 cookie, 刷新页面时发送 */
		$this->set_queue_session(array($data['mq_id']));
		return true;
	}
}
