<?php
/**
 * voa_c_api_meeting_post_new
 * 新建日报
 * $Author$
 * $Id$
 */
class voa_c_api_meeting_post_new extends voa_c_api_meeting_base {

	public function execute() {
		// 需要的参数
		$fields = array(
			// 会议日期
			'date' => array('type' => 'string_trim', 'required' => true),
			// 会议开始时间
			'begin_hm' => array('type' => 'string_trim', 'required' => true),
			// 会议结束时间
			'end_hm' => array('type' => 'string_trim', 'required' => true),
			// 会议室
			'mr_id' => array('type' => 'string_trim', 'required' => true),
			//会议主题
			'subject' => array('type' => 'string_trim', 'required' => true),
			// 会议议题
			'message' => array('type' => 'string_trim', 'required' => true),
			//参与人
			'join_uids' => array('type' => 'string_trim', 'required' => true),
		);

		// 基本验证检查
		if (!$this->_check_params($fields)) {
			//return false;
		}
		// 会议日期检查
		if (empty($this->_params['date'])) {
			return $this->_set_errcode(voa_errcode_api_meeting::NEW_DATE_NULL);
		}
		// 会议开始时间检查
		if (empty($this->_params['begin_hm'])) {
			return $this->_set_errcode(voa_errcode_api_meeting::NEW_BEGIN_NULL);
		}
		if (is_numeric($this->_params['begin_hm'])) {
			$beginhm = (int)$this->_params['begin_hm'];
		} else {
			$beginhm = rstrtotime($this->_params['begin_hm']);
		}
		// 会议结束时间
		if (empty($this->_params['end_hm'])) {
			return $this->_set_errcode(voa_errcode_api_meeting::NEW_END_NULL);
		}
		if (is_numeric($this->_params['end_hm'])) {
			$endhm = (int)$this->_params['end_hm'];
		} else {
			$endhm = rstrtotime($this->_params['end_hm']);
		}
		if ($beginhm >= $endhm) {
			return $this->_set_errcode(voa_errcode_api_meeting::NEW_TIME_ERROR);
		}
		// 会议室检查
		if (empty($this->_params['mr_id'])) {
			return $this->_set_errcode(voa_errcode_api_meeting::NEW_MRID_NULL);
		}
		// 会议主题检查
		if (empty($this->_params['subject'])) {
			return $this->_set_errcode(voa_errcode_api_meeting::NEW_SUBJECT_NULL);
		}
		// 参与人
		if (empty($this->_params['join_uids'])) {
			return $this->_set_errcode(voa_errcode_api_meeting::NEW_JOINUIDS_NULL);
		}

		//入库操作
		if (!$this->_add()) {
			return false;
		}

		$this->_result = array(
			'id' => $this->_return['mt_id']
		);
		return true;
	}

	/*
	 * 入库
	 * @return boolen 新增成功
	*/
	public function _add() {
		$uda = &uda::factory('voa_uda_frontend_meeting_insert');
		/** 会议信息 */
		$meeting = array();
		/** 用户列表 */
		$user_list = array();
		if (!$uda->meeting_new($meeting, $user_list)) {
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}
		$this->_return = $meeting;
		/** 把消息推入队列 */
		$this->_to_queue($meeting, $user_list);

		return true;
	}

	/**
	 * 把消息推入队列
	 * @param array $meeting 会议信息
	 * @param array $result 用户确认结果
	 * @return boolean
	 */
	public function _to_queue($meeting, $result) {
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
		$content = " 您有一个新会议 \n"
					 . "会议时间：".$meeting['_created']."\n"
					 		. "会议主题：".$meeting['mt_subject']."\n"
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
