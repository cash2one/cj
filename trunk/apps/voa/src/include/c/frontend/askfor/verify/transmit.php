<?php
/**
 * 同意并转审批
 * $Author$
 * $Id$
 */

class voa_c_frontend_askfor_verify_transmit extends voa_c_frontend_askfor_verify {

	public function execute() {
		/** 权限检查 */
		$this->_chk_permit();

		if ($this->_is_post()) {
			return $this->_submit();
		}

		$this->view->set('refer', get_referer());
		$this->view->set('af_id', $this->_askfor['af_id']);
		$this->view->set('navtitle', '转审批');

		$this->_output('askfor/transmit');
	}

	/** 同意转审批的提交 */
	protected function _submit() {
		/** 审核人 uid */
		$approveuid = rintval($this->request->get('approveuid'));
		/** 自己不能审批自己的申请 */
		if ($approveuid == $this->_askfor['m_uid']) {
			$this->_error_message('askfor_verify_self', get_referer());
		}

		/** 抄送人 */
		$ccuids = explode(",", $this->request->get('carboncopyuids'));
		/** 从进度中读取抄送人和审核人记录 */
		$all_uids = $ccuids;
		$all_uids = array_diff($all_uids, array($this->_proc['m_uid']));
		/** 获取审核人进度记录和已存在进度的用户 uid */
		list($approve_proc, $new_uids) = $this->_get_procs($all_uids, $approveuid);

		/** 读取用户信息, 包括审批人和抄送人信息 */
		$ccuids[] = $approveuid;
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$cculist = $serv_m->fetch_all_by_ids($ccuids);

		/** 审批人 */
		$mem = array();
		/** 从用户列表中取出审批人信息 */
		foreach ($cculist as $k => $v) {
			if ($approveuid == $v['m_uid']) {
				$mem = $v;
				break;
			}
		}

		/** 如果用户不存在 */
		if (empty($mem)) {
			$this->_error_message('member_not_exist', get_referer());
		}

		try {
			$serv_m->begin();

			/** 转审批信息入库 */
			$serv_p = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
			$afp_id = $serv_p->insert(array(
				'af_id' => $this->_askfor['af_id'],
				'm_uid' => $mem['m_uid'],
				'm_username' => $mem['m_username'],
				'afp_status' => voa_d_oa_askfor_proc::STATUS_NORMAL
			), true);

			/** 更新审批以及审批进度状态 */
			$this->_update_status($this->_askfor['af_id'], array(
				'afp_id' => $afp_id,
				'af_status' => voa_d_oa_askfor::STATUS_APPROVE_APPLY
			), $this->_proc['afp_id'], array(
				'afp_status' => voa_d_oa_askfor_proc::STATUS_APPROVE_APPLY,
				'afp_note' => $this->request->get('note')
			));

			/** 删除抄送记录 */
			if (!empty($approve_proc)) {
				$serv_p->delete_by_ids($approve_proc['afp_id']);
			}

			/** 抄送人信息入库 */
			foreach ($cculist as $v) {
				/** 如果是自己 */
				if ($v['m_uid'] == startup_env::get('wbs_uid') || $approveuid == $v['m_uid']) {
					continue;
				}

				$serv_p->insert(array(
					'af_id' => $this->_askfor['af_id'],
					'm_uid' => $v['m_uid'],
					'm_username' => $v['m_username'],
					'afp_status' => voa_d_oa_askfor_proc::STATUS_CARBON_COPY
				));
			}

			$serv_m->commit();
		} catch (Exception $e) {
			$serv_m->rollback();
			/** 入库操作失败 */
			$this->_error_message('操作失败', get_referer());
		}

		/** 推送消息到队列 */
		$this->_to_queue($mem, $cculist);

		$this->_success_message('操作成功', "/askfor/view/".$this->_askfor['af_id']);
	}

	/**
	 * 把消息推送到队列
	 * @param array $mem
	 * @param array $cculist
	 */
	protected function _to_queue($mem, $cculist) {
		/** 取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member');
		$mems = $serv_m->fetch_all_by_ids(array($this->_askfor['m_uid'], $mem['m_uid']));

		/** 发送微信模板消息 */
		$content = "[审批]:".$this->_askfor['af_message']."\r已转给".$mem['m_username'];
		$data = array(
			'mq_touser' => $mems[$this->_askfor['m_uid']]['m_openid'],
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => startup_env::get('agentid'),
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($data);

		/** 抄送人信息列表 */
		$users = array();
		foreach ($cculist as $u) {
			$users[] = $u['m_openid'];
		}

		/** 组织查看链接 */
		$viewurl = '';
		$this->get_view_url($viewurl, $this->_askfor['af_id']);
		/** 给审批人发送微信模板消息 */
		$content = "[审批]:".$this->_askfor['af_message']."\r<a href='".$viewurl."'>查看</a>";
		$adata = array(
			'mq_touser' => implode('|', $users),
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => $this->_plugin['cp_agentid'],
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($adata);

		/** 写入 cookie, 刷新页面时发送 */
		$this->set_queue_session(array($data['mq_id'], $adata['mq_id']));
	}

	/**
	 * 读取审核人进度
	 * @param array $uids uid 数组
	 * @param int $approveuid 审核人 uid
	 * @return 返回审核人进度和新抄送人 uid 数组
	 */
	protected function _get_procs($uids, $approveuid) {
		/** 审核人进度信息 */
		$approve_proc = array();
		$uids[] = $approveuid;
		$serv_p = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
		$procs = $serv_p->fetch_by_af_id_uids($this->_askfor['af_id'], $uids);
		/** 已经存在于进度的用户 uid */
		$exist_uids = array();
		foreach ($procs as $k => $v) {
			$exist_uids[] = $v['m_uid'];
			/** 如果当前审核人已经在进度中有记录, 则 */
			if ($v['m_uid'] == $approveuid) {
				$approve_proc = $v;
				if ($v['afp_status'] != voa_d_oa_askfor_proc::STATUS_CARBON_COPY) {
					$this->_error_message('askfor_duplicte_user', get_referer());
				}
			}
		}

		return array($approve_proc, array_diff($uids, $exist_uids));
	}
}
