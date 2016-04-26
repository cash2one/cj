<?php
/**
 * voa_c_api_dailyreport_post_reply
 * 日报回复接口
 * $Author$
 * $Id$
 */
class voa_c_api_dailyreport_post_reply extends voa_c_api_dailyreport_base {

	public function execute() {
		// 请求参数
		$fields = array(
			// 日报ID
			'dr_id' => array(
				'type' => 'string',
				'required' => true
			),
			// 回复信息
			'message' => array(
				'type' => 'string_trim',
				'required' => true
			)
		);
		if (! $this->_check_params($fields)) {
			return false;
		}
		// 报告信息入库
		$uda = &uda::factory('voa_uda_frontend_dailyreport_insert');
		$post = array();
		$dailyreport = array();
		if (! $uda->dailyreport_reply($post, $dailyreport)) {
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		$this->_result = array(
			'drp_id' => $post['drp_id'],
			'dr_id' => $post['dr_id'],
			'uid' => $post['m_uid'],
			'username' => $post['m_username'],
			'avatar' => voa_h_user::avatar($post['m_uid'], $this->_member),
			'message' => $post['drp_message'],
			'created' => $post['drp_created']
		);

		// 发送消息通知
		$uda->send_wxqymsg_news($this->session, $dailyreport, 'reply', $this->_member['m_uid']);

		return true;
	}
}
