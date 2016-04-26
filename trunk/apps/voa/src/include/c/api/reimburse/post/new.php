<?php
/**
 * voa_c_api_reimburse_post_new
 * 新增报销
 * $Author$
 * $Id$
 */

class voa_c_api_reimburse_post_new extends voa_c_api_reimburse_base {

	public function execute() {

		/*需要的参数*/
		$fields = array(
			/*报销主题 */
			'subject' => array('type' => 'string_trim', 'required' => true),
			/*报销明细id*/
			'rbb_ids' => array('type' => 'string_trim', 'required' => true),
			/*报销审核人*/
			'approveuid' => array('type' => 'string_trim', 'required' => true),
		);

		/*基本验证检查*/
		if (!$this->_check_params($fields)) {
			return false;
		}
		/*报销主题*/
		if (empty($this->_params['subject'])) {
			return $this->_set_errcode(voa_errcode_api_reimburse::NEW_SUBJECT_NULL);
		}
		/*报销明细ID检查*/
		if (empty($this->_params['rbb_ids'])) {
			return $this->_set_errcode(voa_errcode_api_reimburse::NEW_RBB_ID_NULL);
		}
		/*审核人检查*/
		if (empty($this->_params['approveuid'])) {
			return $this->_set_errcode(voa_errcode_api_reimburse::NEW_APPROVEUID_NULL);
		}
		/*审核人不能是自己检查*/
		if ($this->_params['approveuid'] == startup_env::get('wbs_uid')) {
			return $this->_set_errcode(voa_errcode_api_reimburse::NEW_APPROVEUID_SET_NULL);
		}

		/*入库操作*/
		if (!$this->_add()) {
			return false;
		}

		$this->_result = array(
			'id' => $this->_return['rb_id']
		);

		return true;
	}

	/*
	 * 入库
	 * @return boolen 新增成功
	*/
	public function _add() {
		$uda = &uda::factory('voa_uda_frontend_reimburse_insert');
		/** 报销清单信息 */
		$reimburse = array();
		/** 审批人 */
		$mem = array();
		/** 抄送人 */
		$cculist = array();
		if (!$uda->reimburse_new($reimburse, $mem, $cculist)) {
			//$this->_error_message($uda->error);
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $reimburse['rb_id']);
		$content = "申请报销\n"
				 . "申请人：".$reimburse['m_username'].""."\n"
				 . "报销主题：".$reimburse['rb_subject']."\n"
				 . "报销金额：".round($reimburse['rb_expend'] / 100, 2)."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";

		/** 整理需要接收消息的用户 */
		$users = array($mem['m_uid'] => $mem['m_openid']);
		foreach ($cculist as $u) {
			if (startup_env::get('wbs_uid') == $u['m_uid']) {
				continue;
			}

			$users[$u['m_uid']] = $u['m_openid'];
		}

		/*返回数组*/
		$this->_return = $reimburse;
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
