<?php
/**
 * voa_c_api_reimburse_post_transmit
 * 同意并转审批
 * $Author$
 * $Id$
 */

class voa_c_api_reimburse_post_transmit extends voa_c_api_reimburse_verify {

	public function execute() {
		/*需要的参数*/
		$fields = array(
			/*请假ID*/
			'rb_id' => array('type' => 'int', 'required' => true),
			/*审核人*/
			'approveuid' => array('type' => 'string_trim', 'required' => true),
			/*留言*/
			'message' => array('type' => 'string_trim', 'required' => true),
		);

		/*基本验证检查*/
		if (!$this->_check_params($fields)) {
			//return false;
		}
		/*审核人*/
		if (empty($this->_params['approveuid'])) {
			return $this->_set_errcode(voa_errcode_api_reimburse::NEW_APPROVEUID_NULL);
		}
		/*留言检查*/
		if (empty($this->_params['message'])) {
			return $this->_set_errcode(voa_errcode_api_reimburse::NEW_MESSAGE_NULL);
		}

		/** 权限检查 */
		$this->_chk_permit();

		/*入库操作*/
		if (!$this->_submit()) {
			return false;
		}

		$this->_result = array(
			'id' => $this->_reimburse['rb_id']
		);

		return true;
	}

	/** 同意转审批的提交 */
	public function _submit() {
		$mem = array();
		$uda = &uda::factory('voa_uda_frontend_reimburse_update');
		if (!$uda->reimburse_transmit($this->_reimburse, $this->_proc, $mem)) {
			//$this->_error_message($uda->error);
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		/** 整理输出 */
		$uda_fmt = &uda::factory('voa_uda_frontend_reimburse_format');
		if (!$uda_fmt->reimburse($this->_reimburse)) {
			//$this->_error_message($uda_fmt->error, get_referer());
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $this->_reimburse['rb_id']);
		$content = "报销已转审批\n"
				 . "等待 ".$this->_proc['m_username']." 审批\n"
				 . "报销主题：".$this->_reimburse['rb_subject']."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";

		/** 整理需要接收消息的用户 */
		$sponsor_user = voa_h_user::get($this->_reimburse['m_uid']);
		$users = array(
			$sponsor_user['m_uid'] => $sponsor_user['m_openid'],
			$mem['m_uid'] => $mem['m_openid']
		);

		$data = array(
			'mq_touser' => implode('|', $users),
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
