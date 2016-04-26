<?php
/**
 * voa_c_api_askoff_post_new
 * 新建请假
 * $Author$
 * $Id$
 */
class voa_c_api_askoff_post_new extends voa_c_api_askoff_base {

	public function execute() {

		/*需要的参数*/
		$fields = array(
			/*请假内容*/
			'message' => array('type' => 'string_trim', 'required' => true),
			/*审核人uid*/
			'approveuid' => array('type' => 'int', 'required' => true),
			/*抄送人员uid*/
			'carboncopyuids' => array('type' => 'string_trim', 'required' => false),
			/*请假类别*/
			'type' => array('type' => 'string_trim', 'required' => true),
			/*请假开始时间*/
			'begintime' => array('type' => 'string_trim', 'required' => true),
			/*请假结束时间*/
			'endtime' => array('type' => 'string_trim', 'required' => true),
		);

		/*基本验证检查*/
		if (!$this->_check_params($fields)) {
			return false;
		}

		/*接收人员检查*/
		if (empty($this->_params['approveuid'])) {
			return $this->_set_errcode(voa_errcode_api_askoff::NEW_ACCEPTER_NULL);
		}

		/*接收人号不能是自己 检查*/
		if ($this->_params['approveuid'] == $this->_member['m_uid']) {
			return $this->_set_errcode(voa_errcode_api_askoff::NEW_APPROVEUID_SET_NULL);
		}

		/*入库操作*/
		if (!$this->_add()) {
			return false;
		}

		$this->_result = array(
			'ao_id' => $this->_return['ao_id']
		);
		return true;
	}

	/*
	 * 入库
	 * @return boolen 新增成功
	*/
	protected function _add() {
		$uda = &uda::factory('voa_uda_frontend_askoff_insert');
		$member = &uda::factory('voa_uda_frontend_member_get');

		/** 请假信息 */
		$askoff = array();

		/** 请假详情信息 */
		$post = array();

		/** 审批人信息 */
		$mem = array();

		/** 抄送人信息 */
		$cculist = array();

		if (!$uda->askoff_new($askoff, $post, $mem, $cculist)) {
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		$this->_return = $askoff;
		/** 更新草稿信息 */
		$this->_update_draft($mem['m_uid'], array_keys($cculist));

		/** 整理输出 */
		$uda_fmt = &uda::factory('voa_uda_frontend_askoff_format');
		if (!$uda_fmt->askoff($askoff)) {
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			//$this->_error_message($uda_fmt->error, get_referer());
			return false;
		}

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $askoff['ao_id']);
		$content = "待 ".$mem['m_username']." 审核\n"
				 . "申请人：".$this->_member['m_username']."\n"
				 . "请假类别：".$this->_p_sets['types'][$askoff['ao_type']]."\n"
				 . "请假天数：".$askoff['_days']." 天"."\n"
				 . "开始时间：".$askoff['_begintime_md']."\n"
				 . "结束时间：".$askoff['_endtime_md']."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";

		/** 整理需要接收消息的用户 */
		$users = array($mem['m_uid'] => $mem['m_openid']);
		foreach ($cculist as $u) {
			if (startup_env::get('wbs_uid') == $u['m_uid']) {
				continue;
			}
			$users[$u['m_uid']] = $u['m_openid'];
		}

		$data = array(
			'mq_touser' => implode('|', $users),
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => (int)$this->_plugin['cp_agentid'],
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($data);

		return true;
	}

}
