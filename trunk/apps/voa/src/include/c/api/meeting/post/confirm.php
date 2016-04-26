<?php
/**
 * voa_c_api_meeting_post_confirm
 * 确认参加会议
 * $Author$
 * $Id$
 */

class voa_c_api_meeting_post_confirm extends voa_c_api_meeting_base {

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

		/** 读取会议信息 */
		$serv_mt = &service::factory('voa_s_oa_meeting', array('pluginid' => startup_env::get('pluginid')));
		$meeting = $serv_mt->fetch_by_id($mt_id);

		/** 会议信息不存在 */
		if (empty($meeting)) {
			//$this->_error_message('meeting_not_exist');
			return $this->_set_errcode(voa_errcode_api_meeting::MEETING_NOT_EXIST);
		}

		/** 判断当前用户是否为参会人 */
		$serv_mm = &service::factory('voa_s_oa_meeting_mem', array('pluginid' => startup_env::get('pluginid')));
		$user = $serv_mm->fetch_by_mt_id_uid($mt_id, startup_env::get('wbs_uid'));
		/** 如果参会人不存在 */
		if (empty($user)) {
			//$this->_error_message('no_privilege');
			return $this->_set_errcode(voa_errcode_api_meeting::NO_PRIVILEGE);
		}

		try {
			$serv_mt->begin();
			/** 更新记录状态 */
			$serv_mm->update(array(
				'mm_status' => voa_d_oa_meeting_mem::STATUS_CONFIRM
			), array('mm_id' => $user['mm_id']));

			/** 更新确认参加的人数 */
			$serv_mt->update(array(
				'mt_agreenum' => $meeting['mt_agreenum'] + 1
			), array('mt_id' => $mt_id));

			$serv_mt->commit();
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
		/** 整理输出 */
		$uda_fmt = &uda::factory('voa_uda_frontend_meeting_format');
		if (!$uda_fmt->meeting($meeting)) {
			//$this->_error_message($uda_fmt->error, get_referer());
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		/** 读取用户 */
		$mem = voa_h_user::get($meeting['m_uid']);
		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $meeting['mt_id']);
		$content = startup_env::get('wbs_username')." 参加会议\n"
				 . "会议主题：".$meeting['mt_subject']."\n"
				 . "会议时间：".$meeting['_created']."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";

		$data = array(
			'mq_touser' => $mem['m_openid'],
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

