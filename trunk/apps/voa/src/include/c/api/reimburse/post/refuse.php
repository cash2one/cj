<?php
/**
 * voa_c_api_reimburse_post_refuse
 * 拒绝申请
 * $Author$
 * $Id$
 */

class voa_c_api_reimburse_post_refuse extends voa_c_api_reimburse_verify {

	public function execute() {
		/*请求参数*/
		$fields = array(
			/*请假ID*/
			'rb_id' => array('type' => 'int', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		/*请假内容检查*/
		if (empty($this->_params['rb_id'])) {
			return $this->_set_errcode(voa_errcode_api_reimburse::ASKOFF_NOT_EXIST);
		}

		/** 权限检查 */
		$this->_chk_permit();

		$uda = &uda::factory('voa_uda_frontend_reimburse_update');
		if (!$uda->reimburse_refuse($this->_reimburse, $this->_proc)) {
			//$this->_error_message($uda->error, get_referer());
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
		$content = "报销已驳回\n"
				 . "审批人：".$this->_proc['m_username'].""."\n"
				 . "报销主题：".$this->_reimburse['rb_subject']."\n"
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
		// 写入 cookie, 刷新页面时发送
		voa_h_qymsg::set_queue_session(array($data['mq_id']), $this->session);

		return true;

	}
}
