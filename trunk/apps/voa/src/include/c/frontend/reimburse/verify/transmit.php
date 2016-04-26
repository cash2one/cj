<?php
/**
 * 同意并转审批
 * $Author$
 * $Id$
 */

class voa_c_frontend_reimburse_verify_transmit extends voa_c_frontend_reimburse_verify {

	public function execute() {
		/** 权限检查 */
		$this->_chk_permit();

		if ($this->_is_post()) {
			return $this->_submit();
		}

		$this->view->set('refer', get_referer());
		$this->view->set('rb_id', $this->_reimburse['rb_id']);
		$this->view->set('navtitle', '转审批');

		$this->_output('reimburse/transmit');
	}

	/** 同意转审批的提交 */
	protected function _submit() {
		$mem = array();
		$uda = &uda::factory('voa_uda_frontend_reimburse_update');
		if (!$uda->reimburse_transmit($this->_reimburse, $this->_proc, $mem)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 整理输出 */
		$uda_fmt = &uda::factory('voa_uda_frontend_reimburse_format');
		if (!$uda_fmt->reimburse($this->_reimburse)) {
			$this->_error_message($uda_fmt->error, get_referer());
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
		/**foreach ($cculist as $u) {
			$users[$u['m_uid']] = $u['m_openid'];
		}*/

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

		$this->_success_message('操作成功', "/reimburse/view/".$this->_reimburse['rb_id']);
	}
}
