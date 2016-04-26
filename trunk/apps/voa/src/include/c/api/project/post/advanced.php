<?php
/**
 * voa_c_api_project_post_advanced
 * 任务推进接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_project_post_advanced extends voa_c_api_project_base {

	/** 任务信息 */
	protected $_project = array();
	/** 任务参与人 uid */
	protected $_p_uids = array();
	/** 任务参与人信息 */
	protected $_p_users = array();
	/** 退出任务的人员 */
	protected $_quit_uids = array();

	public function execute() {

		// 请求参数
		$fields = array(
			// 任务ID
			'id' => array('type' => 'int', 'required' => true),
			// 推进消息
			'message' => array('type' => 'string_trim', 'required' => true),
			// 任务人员id，以半角逗号分隔
			'uids' => array('type' => 'string_trim', 'required' => true)
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		if (empty($this->_params['message'])) {
			return $this->_set_errcode(voa_errcode_api_project::ADVANCED_MESSAGE_NULL);
		}

		$p_uids = explode(',', $this->_params['uids']);
		if (empty($this->_params['uids']) || empty($p_uids)) {
			return $this->_set_errcode(voa_errcode_api_project::ADVANCED_UIDS_NULL);
		}

		// 读取任务信息
		$serv_p = &service::factory('voa_s_oa_project', array('pluginid' => startup_env::get('pluginid')));
		$this->_project = $serv_p->fetch_by_id($this->_params['id']);
		if (empty($this->_project)) {
			return $this->_set_errcode(voa_errcode_api_project::ADVANCED_NOT_EXISTS);
		}

		// 判断是否发起者
		if ($this->_project['m_uid'] != $this->_member['m_uid']) {
			return $this->_set_errcode(voa_errcode_api_project::ADVANCED_NO);
		}

		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$serv_pm = &service::factory('voa_s_oa_project_mem', array('pluginid' => startup_env::get('pluginid')));
		$serv_pp = &service::factory('voa_s_oa_project_proc', array('pluginid' => startup_env::get('pluginid')));

		// 读取任务所有用户
		$mems = $serv_pm->fetch_by_p_id($this->_params['id']);
		foreach ($mems as $m) {

			if (voa_d_oa_project_mem::STATUS_CC == $m['pm_status'] || voa_d_oa_project_mem::STATUS_OUTOF == $m['pm_status']) {
				continue;
			}

			// 如果是退出状态
			if (voa_d_oa_project_mem::STATUS_QUIT == $m['pm_status']) {
				$this->_quit_uids[$m['m_uid']] = $m['m_uid'];
				continue;
			}

			$this->_p_uids[$m['m_uid']] = $m['m_uid'];
		}

		// 读取用户信息
		$this->_p_users = $serv_m->fetch_all_by_ids($this->_p_uids);
		voa_h_user::push($this->_p_users);

		// 读取任务人员信息
		$new_uids = array_diff($p_uids, $this->_p_uids);
		$new_users = array();
		if (!empty($new_uids)) {
			$new_users = $serv_m->fetch_all_by_ids($new_uids);
			if (empty($new_users)) {
				return $this->_set_errcode(voa_errcode_api_project::ADVANCED_NEW_UIDS_NULL);
			}
		}

		/** 数据入库 */
		try {
			$serv_m->begin();

			// 新增任务人员
			foreach ($new_uids as $uid) {

				if (empty($this->_quit_uids[$uid])) {
					// 如果是新加入的用户

					if (!isset($new_users[$uid])) {
						// 不存在的用户
						continue;
					}

					$serv_pm->insert(array(
						'p_id' => $this->_params['id'],
						'm_uid' => $uid,
						'm_username' => $new_users[$uid]['m_username'],
						'pm_status' => voa_d_oa_project_mem::STATUS_NORMAL
					));
				} else {
					// 如果该用户是之前退出的
					$serv_pm->update(array(
						'pm_progress' => 0,
						'pm_status' => voa_d_oa_project_mem::STATUS_NORMAL
					), array('p_id' => $this->_params['id'], 'm_uid' => $uid));
				}

				// 非任务初始人员, 增加一条进度信息
				$serv_pp->insert(array(
					'p_id' => $this->_params['id'],
					'm_uid' => $uid,
					'm_username' => $new_users[$uid]['m_username'],
					'pp_message' => '加入'
				));
			}

			// 处理退出人员
			$quit_uids = array_diff($this->_p_uids, $p_uids);
			foreach ($quit_uids as $uid) {
				if ($uid == $this->_project['m_uid']) {
					//  当剔除发起人时, 则把状态改成不参加即可
					$status = voa_d_oa_project_mem::STATUS_OUTOF;
				} else {
					// 否则，标记为退出
					$status = voa_d_oa_project_mem::STATUS_QUIT;
				}

				$serv_pm->update(array(
					'p_id' => $this->_params['id'],
					'm_uid' => $uid,
					'm_username' => $this->_p_users[$uid]['m_username'],
					'pm_status' => $status
				), array('p_id' => $this->_params['id'], 'm_uid' => $uid));

				// 退出时, 增加一条退出记录(进度)
				$serv_pp->insert(array(
					'p_id' => $this->_params['id'],
					'm_uid' => $uid,
					'm_username' => $this->_p_users[$uid]['m_username'],
					'pp_message' => '退出'
				));
			}

			$serv_m->commit();
		} catch (Exception $e) {
			$serv_m->rollback();
			return $this->_set_errcode(voa_errcode_api_project::ADVANCED_FAILED);
		}

		if (!empty($this->_setting['ep_wxqy'])) {
			// 发送微信消息

			// 给抄送人发送模板消息
			$allusers = array_merge($this->_p_users, $new_users);
			foreach ($quit_uids as $uid) {
				unset($allusers[$uid]);
			}

			// 把微信消息推入队列
			$this->_to_queue($allusers, $this->_params['message']);
		}

		// 读取任务人员/抄送人信息
		$serv_pm = &service::factory('voa_s_oa_project_mem', array('pluginid' => $this->_pluginid));
		$allmems = $serv_pm->fetch_by_p_id($this->_params['id']);
		// 参与人员的UID
		$puids = array();
		// 参与人员详情的列表
		$userlist = array();
		foreach ($allmems as $m) {
			$userlist[] = array(
				'pmid' => (int)$m['pm_id'],
				'uid' => (int)$m['m_uid'],
				'username' => (string)$m['m_username'],
				'progress' => (int)$m['pm_progress'],
				'status' => (string)isset($this->_status_maps[$m['pm_status']]) ? $this->_status_maps[$m['pm_status']] : 'normal',
				'updated' => (int)$m['pm_updated']
			);
			$puids[$m['m_uid']] = $m['m_uid'];
		}
		// 找到参与人员的用户信息
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $serv_m->fetch_all_by_ids($puids);
		foreach ($users as $u) {
			voa_h_user::push($u);
		}
		unset($puids);
		// 为参与项目的人员列表注入头像信息
		foreach ($userlist as &$m) {
			$m['avatar'] = (string)voa_h_user::avatar($m['uid'], isset($users[$m['uid']]) ? $users[$m['uid']] : array());
		}

		$this->_result = array('userlist' => $userlist);

		return true;
	}

	/**
	 * 把微信消息推入队列
	 * @param array $users 用户信息列表
	 * @param string $message 推进事项
	 */
	protected function _to_queue($users, $message) {
		// 取openid
		$openids = array();
		foreach ($users as $u) {
			$openids[] = $u['m_openid'];
		}

		// 发送微信消息
		$viewurl = '';
		$this->get_view_url($viewurl, $this->_project['p_id']);
		$content = "任务推进\n"
				. "来自：".$this->_project['m_username']."\n"
					 . "任务名：".rhtmlspecialchars($this->_project['p_subject'])."\n"
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

		// 写入 cookie, 刷新页面时发送
		voa_h_qymsg::set_queue_session(array($data['mq_id']), $this->session);
		return true;
	}

}
