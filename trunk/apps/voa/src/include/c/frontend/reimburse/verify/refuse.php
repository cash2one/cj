<?php
/**
 * 拒绝申请
 * $Author$
 * $Id$
 */

class voa_c_frontend_reimburse_verify_refuse extends voa_c_frontend_reimburse_verify {

	public function execute() {
		/** 权限检查 */
		$this->_chk_permit();

		$uda = &uda::factory('voa_uda_frontend_reimburse_update');
		if (!$uda->reimburse_refuse($this->_reimburse, $this->_proc)) {
			$this->_error_message($uda->error, get_referer());
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
		$message = $this->request->get('message');
		$content = "报销已驳回\n"
				 . "审批人：".$this->_proc['m_username'].""."\n"
				 . "报销主题：".$this->_reimburse['rb_subject']."\n"
				 . "理由：{$message}\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";

		/** 整理需要接收消息的用户 */
		$mem = voa_h_user::get($this->_reimburse['m_uid']);

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

		$this->_success_message('操作成功', "/reimburse/view/".$this->_reimburse['rb_id']);
	}
}
