<?php
/**
 * 会议取消
 * $Author$
 * $Id$
 */

class voa_c_frontend_meeting_cancel extends voa_c_frontend_meeting_base {

	public function execute() {
		/** 读取会议信息 */
		$mt_id = rintval($this->request->get('mt_id'));
		$serv_mt = &service::factory('voa_s_oa_meeting', array('pluginid' => startup_env::get('pluginid')));
		$meeting = $serv_mt->fetch_by_id($mt_id);

		/** 确定用户是否有编辑权限 */
		if ($meeting['m_uid'] != startup_env::get('wbs_uid')) {
			$this->ajax(0, '无权限');
		}

		/** 会议信息不存在 */
		if (!$this->_meeting_is_valid($meeting)) {
			$this->ajax(0, '会议不存在');
		}

		// 如果已经更新了状态
		if (voa_d_oa_meeting::STATUS_CANCEL == $meeting['mt_status']) {
			$this->ajax(0, '不能重复操作');
		}

		$serv_mm = &service::factory('voa_s_oa_meeting_mem', array('pluginid' => startup_env::get('pluginid')));
		try {
			// 更新会议状态
			$serv_mt->update(array(
				'mt_status' => voa_d_oa_meeting::STATUS_CANCEL
			), array('mt_id' => $mt_id));

			// 更新用户状态
			/*$serv_mm->update(array(
				'mm_status' => voa_d_oa_meeting_mem::STATUS_CANCEL
			), array('mt_id' => $mt_id));*/
		} catch (Exception $e) {
			$serv_mt->rollback();
			$this->ajax(0, '操作失败');
		}

		/** 把消息推入队列 */
		$this->_to_queue($meeting);

		$this->ajax(1);
	}

	/**
	 * 把消息推入队列
	 * @param array $meeting 会议信息
	 * @return boolean
	 */
	protected function _to_queue($meeting) {
		/** 读取参会用户 */
		$serv_mem = &service::factory('voa_s_oa_meeting_mem');
		$mems = $serv_mem->fetch_by_mt_id($meeting['mt_id']);
		$m_uids = array();
		foreach ($mems as $_u) {
			$m_uids[] = $_u['m_uid'];
		}
		unset($_u);
		if (empty($m_uids)) {
			return true;
		}
		$user_list = voa_h_user::get_multi($m_uids);
		if (empty($user_list)) {
			return true;
		}

		/** 整理需要接收消息的用户 */
		$users = array();
		foreach ($user_list as $u) {
			if (startup_env::get('wbs_uid') == $u['m_uid']) {
				continue;
			}

			$users[$u['m_uid']] = $u['m_openid'];
		}

		/** 如果没有需要发送的用户 */
		if (empty($users)) {
			return true;
		}

		/** 整理输出 */
		$uda_fmt = &uda::factory('voa_uda_frontend_meeting_format');
		if (!$uda_fmt->meeting($meeting)) {
			$this->_error_message($uda_fmt->error, get_referer());
			return false;
		}

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $meeting['mt_id']);
		$content = "会议取消\n"
				 . "来自：".$meeting['m_username']."\n"
				 . "会议室：".$this->_rooms[$meeting['mr_id']]['mr_name']."\n"
				 . "会议时间：".$meeting['_begintime']."\n"
				 . "会议主题：".$meeting['mt_subject']."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";

		$data = array(
			'mq_touser' => implode('|', $users),
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
