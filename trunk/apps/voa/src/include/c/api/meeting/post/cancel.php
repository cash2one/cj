<?php
/**
 * voa_c_api_meeting_post_cancel
 * 会议取消
 * $Author$
 * $Id$
 */

class voa_c_api_meeting_post_cancel extends voa_c_api_meeting_base {

	public function execute() {
		// 请求参数
		$fields = array(
			// 日报ID
			'id' => array('type' => 'int', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		$mt_id = $this->_params['id'];
		$serv_mt = &service::factory('voa_s_oa_meeting', array('pluginid' => startup_env::get('pluginid')));
		$meeting = $serv_mt->fetch_by_id($mt_id);

		/** 确定用户是否有编辑权限 */
		if ($meeting['m_uid'] != startup_env::get('wbs_uid')) {
			//$this->_error_message('no_privilege');
			return $this->_set_errcode(voa_errcode_api_meeting::NO_PRIVILEGE);
		}

		/** 会议信息不存在 */
		if (!$this->_meeting_is_valid($meeting)) {
			//$this->_error_message('meeting_not_valid');
			return $this->_set_errcode(voa_errcode_api_meeting::MEETING_NOT_EXIST);
		}

		/** 如果为非submit */
		if (!$this->request->is_post()) {
			//$this->_error_message('非法操作');
			return $this->_set_errcode(voa_errcode_api_meeting::LIST_UNDEFINED_ACTION);
		}

		$serv_mm = &service::factory('voa_s_oa_meeting_mem', array('pluginid' => startup_env::get('pluginid')));
		try {
			/** 更新会议状态 */
			$serv_mt->update(array(
				'mt_status' => voa_d_oa_meeting::STATUS_CANCEL
			), array('mt_id' => $mt_id));

			/** 更新用户状态 */
			$serv_mm->update(array(
				'mm_status' => voa_d_oa_meeting_mem::STATUS_CANCEL
			), array('mt_id' => $mt_id));
		} catch (Exception $e) {
			$serv_mt->rollback();
			//$this->_error_message('操作失败');
			return $this->_set_errcode(voa_errcode_api_meeting::LIST_UNDEFINED_ACTION);
		}

		/** 把消息推入队列 */
		$this->_to_queue($meeting);
		return true;
	}

	/**
	 * 把消息推入队列
	 * @param array $meeting 会议信息
	 * @return boolean
	 */
	public function _to_queue($meeting) {
		/** 读取参会用户 */
		$serv_mem = &service::factory('voa_s_oa_meeting_mem');
		$mems = $serv_mem->fetch_by_mt_id($meeting['mt_id']);
		/** 整理需要接收消息的用户 */
		$uids = array();
		$users = array();
		$openids = array();
		foreach ($mems as $u) {
			if (startup_env::get('wbs_uid') == $u['m_uid']) {
				continue;
			}

			$uids[$u['m_uid']] = $u['m_uid'];
		}
		// 查询用户信息
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $serv_m->fetch_all_by_ids($uids);
		voa_h_user::push($users);
		/** 如果没有需要发送的用户 */
		if (empty($users)) {
			return true;
		}
		foreach ($users as $user) {
			$openids[] = $user['m_openid'];
		}

		/** 整理输出 */
		$uda_fmt = &uda::factory('voa_uda_frontend_meeting_format');
		if (!$uda_fmt->meeting($meeting)) {
			//$this->_error_message($uda_fmt->error, get_referer());
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}


		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $meeting['mt_id']);
		$content = "会议取消\n"
				 . "来自：".$meeting['m_username']."\n"
				 . "会议室：".$this->_rooms[$meeting['mr_id']]['mr_name']."\n"
				 . "会议时间：".$meeting['_created']."\n"
				 . "会议主题：".$meeting['mt_subject']."\n"
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

	}
}
