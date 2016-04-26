<?php
/**
 * 确认参加会议
 * $Author$
 * $Id$
 */

class voa_c_frontend_meeting_confirm extends voa_c_frontend_meeting_base {

	public function execute() {
		/** 读取会议信息 */
		$mt_id = rintval($this->request->get('mt_id'));
		$serv_mt = &service::factory('voa_s_oa_meeting', array('pluginid' => startup_env::get('pluginid')));
		$meeting = $serv_mt->fetch_by_id($mt_id);

		/** 会议信息不存在 */
		if (empty($meeting)) {
			$this->ajax(0, '会议不存在');
		}
		
		if ($meeting['mt_status'] == voa_d_oa_meeting::STATUS_CANCEL) {
			$this->ajax(0, '会议已取消');
		}

		/** 判断当前用户是否为参会人 */
		$serv_mm = &service::factory('voa_s_oa_meeting_mem', array('pluginid' => startup_env::get('pluginid')));
		$user = $serv_mm->fetch_by_mt_id_uid($mt_id, startup_env::get('wbs_uid'));
		/** 如果参会人不存在 */
		if (empty($user)) {
			$this->ajax(0, '无权限');
		}

		// 如果记录状态已经改变
		if (voa_d_oa_meeting_mem::STATUS_NORMAL != $user['mm_status']) {
			$this->ajax(0, '不能重复操作');
		}

		try {
			$serv_mt->begin();
			/** 更新记录状态 */
			$serv_mm->update(array(
				'mm_status' => voa_d_oa_meeting_mem::STATUS_CONFIRM
			), array('mm_id' => $user['mm_id']));

			/** 更新确认参加的人数 */
			$serv_mt->update(array(
				'mt_agreenum' => $meeting['mt_agressnum'] + 1
			), array('mt_id' => $mt_id));

			$serv_mt->commit();
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
		/** 整理输出 */
		$uda_fmt = &uda::factory('voa_uda_frontend_meeting_format');
		if (!$uda_fmt->meeting($meeting)) {
			$this->_error_message($uda_fmt->error, get_referer());
			return false;
		}

		/** 读取用户 */
		$mem = voa_h_user::get($meeting['m_uid']);
		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $meeting['mt_id']);
		$content = startup_env::get('wbs_username')." 参加会议\n"
				 . "会议主题：".$meeting['mt_subject']."\n"
				 . "会议时间：".$meeting['_begintime']."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";

		$data = array(
			'mq_touser' => $mem['m_openid'],
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

