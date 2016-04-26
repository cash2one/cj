<?php
/**
 * 同意并转审批
 * voa_c_api_askfor_post_transmit
 * $Author$
 * $Id$
 */

class voa_c_api_askfor_post_transmit extends voa_c_api_askfor_verify {

	public function execute() {

		// 需要的参数
		$fields = array(
			/*审批ID*/
			'af_id' => array('type' => 'int', 'required' => true),
			/*审核人*/
			'approveuid' => array('type' => 'string_trim', 'required' => true),
			/*留言*/
			'message' => array('type' => 'string_trim', 'required' => true),
			/*抄送人*/
			'carboncopyuids' => array('type' => 'string_trim', 'required' => false),
		);

		/*基本验证检查*/
		if (!$this->_check_params($fields)) {
			return false;
		}
		
		/*审核人*/
		if (empty($this->_params['approveuid'])) {
			return $this->_set_errcode(voa_errcode_api_askfor::APPROVEUID_NULL);
		}
		/*留言*/
		if (empty($this->_params['message'])) {
			return $this->_set_errcode(voa_errcode_api_askfor::MESSAGE_NULL);
		}

		/** 权限检查 */
		if(!$this->_chk_permit()) {
			return false;
		}

		//入库操作
		if (!$this->_submit()) {
			return false;
		}
		
		return true;
	}

	/** 同意转审批的提交 */
	protected function _submit() {
		$uda = &uda::factory('voa_uda_frontend_askfor_update');
		$approve_proc = array();
		$cculist = array();
		if (!$uda->askfor_transmit($this->_askfor, $this->_proc, $approve_proc, $cculist)) {
			return false;
		}

		/** 推送消息到队列 */
		$this->_to_queue($approve_proc, $cculist);

		return true;
	}

	/**
	 * 把消息推送到队列
	 * @param array $mem
	 * @param array $cculist
	 */
	protected function _to_queue($approve_proc, $cculist) {
		/** 取审批详情 */
		$serv_pt = &service::factory('voa_s_oa_askfor_comment');
		$post = $serv_pt->fetch_by_id($this->_askfor['af_id']);

		/** 格式化审批信息 */
		$uda_fmt = &uda::factory('voa_uda_frontend_askfor_format');
		$uda_fmt->askfor($this->_askfor);

		/** 取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member');
		$mems = $serv_m->fetch_all_by_ids(array($this->_askfor['m_uid'], $approve_proc['m_uid']));

		/** 组织查看链接 */
		$viewurl = '';
		$this->get_view_url($viewurl, $this->askfor['af_id']);
		$content = "审批状态\n"
				 . $this->_proc['m_username']." 已同意\n"
				 . "并转 ".$approve_proc['m_username']." 进行审批\n"
				 . "------------------\n"
				 . "申请人：".$this->_askfor['m_username']."\n"
				 . "申请主题：".$this->_askfor['ao_subject']."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";

		/** 抄送人信息列表 */
		$users = array($approve_proc['m_uid'] => $mems[$approve_proc['m_uid']]['m_openid']);
		foreach ($cculist as $u) {
			$users[$u['m_uid']] = $u['m_openid'];
		}

		/** 给审批人发送微信模板消息 */
		$data = array(
			'mq_touser' => implode('|', $users),
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => $cur_plugin['cp_agentid'],
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($data);

		return true;
	}
}
