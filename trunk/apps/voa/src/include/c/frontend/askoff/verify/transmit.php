<?php
/**
 * 同意并转审批
 * $Author$
 * $Id$
 */

class voa_c_frontend_askoff_verify_transmit extends voa_c_frontend_askoff_verify {

	public function execute() {
		/** 权限检查 */
		$this->_chk_permit();

		if ($this->_is_post()) {
			return $this->_submit();
		}

		$this->view->set('refer', get_referer());
		$this->view->set('ao_id', $this->_askoff['ao_id']);
		$this->view->set('navtitle', '转审批');

		$this->_output('askoff/transmit');
	}

	/** 同意转审批的提交 */
	protected function _submit() {
		$uda = &uda::factory('voa_uda_frontend_askoff_update');
		$approve_proc = array();
		$cculist = array();
		if (!$uda->askoff_transmit($this->_askoff, $this->_proc, $approve_proc, $cculist)) {
			$this->_error_message($uda->errmsg);
			return false;
		}

		/** 推送消息到队列 */
		$this->_to_queue($approve_proc, $cculist);

		$this->_success_message('操作成功', "/askoff/view/".$this->_askoff['ao_id']);
	}

	/**
	 * 把消息推送到队列
	 * @param array $mem
	 * @param array $cculist
	 */
	protected function _to_queue($approve_proc, $cculist) {
		/** 取请假详情 */
		$serv_pt = &service::factory('voa_s_oa_askoff_post');
		$post = $serv_pt->fetch_first_by_ao_id($this->_askoff['ao_id']);

		/** 格式化请假信息 */
		$uda_fmt = &uda::factory('voa_uda_frontend_askoff_format');
		$uda_fmt->askoff($this->_askoff);

		/** 取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member');
		$mems = $serv_m->fetch_all_by_ids(array($this->_askoff['m_uid'], $approve_proc['m_uid']));

		/** 组织查看链接 */
		$viewurl = '';
		$this->get_view_url($viewurl, $this->_askoff['ao_id']);
		$content = "审批状态\n"
				 . $this->_proc['m_username']." 已同意\n"
				 . "并转 ".$approve_proc['m_username']." 进行审批\n"
				 . "------------------\n"
				 . "申请人：".$this->_askoff['m_username']."\n"
				 . "请假类别：".$this->_p_sets['types'][$this->_askoff['ao_type']]."\n"
				 . "请假时长：".$this->_askoff['_timespace']."\n"
				 . "开始日期：".$this->_askoff['_begintime_md']."\n"
				 . "结束日期：".$this->_askoff['_endtime_md']."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";

		/** 抄送人信息列表 */
		$users = array($approve_proc['m_uid'] => $mems[$approve_proc['m_uid']]['m_openid']);
		$users[$this->_askoff['m_uid']] = $mems[$this->_askoff['m_uid']]['m_openid'];
		foreach ($cculist as $u) {
			$users[$u['m_uid']] = $u['m_openid'];
		}

		/** 给请假人发送微信模板消息 */
		$data = array(
			'mq_touser' => implode('|', $users),
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => startup_env::get('agentid'),
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($data);

		/** 写入 cookie, 刷新页面时发送 */
		$this->set_queue_session(array($data['mq_id'], $data['mq_id']));

		return true;
	}
}
