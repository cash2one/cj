<?php
/**
 * 新增报销
 * $Author$
 * $Id$
 */

class voa_c_frontend_reimburse_new extends voa_c_frontend_reimburse_base {

	public function execute() {
		if ($this->_is_post()) {
			/** 调用处理函数 */
			$this->_add();
			return false;
		}

		/** 读取清单信息 */
		$bills = array();
		$this->_get_bill_by_uid($bills, startup_env::get('wbs_uid'), 0, 0, self::BILL_NORMAL);

		/** 获取清单id */
		$at_ids = array();
		foreach ($bills as $b) {
			$at_ids[$b['at_id']] = $b['at_id'];
		}

		/** 读取附件 */
		$attachs = array();
		$this->_get_attach_by_at_id($attachs, $at_ids);
		/** 根据清单id整理附件 */
		$tmp_at = array();
		foreach ($attachs as $at) {
			$tmp_at[$at['rbb_id']] = $at;
		}

		$attachs = $tmp_at;

		$this->view->set('action', $this->action_name);
		$this->view->set('form_action', '/reimburse/new');
		$this->view->set('approveuid', '');
		$this->view->set('reimburse', array());
		$this->view->set('bills', $bills);
		$this->view->set('attachs', $attachs);
		$this->view->set('types', $this->_p_sets['types']);
		$this->view->set('navtitle', '新建报销');

		// 赋值jsapi接口需要的ticket
		$this->_get_jsapi("['chooseImage', 'previewImage', 'uploadImage']");

		$this->_output('reimburse/post');
	}

	protected function _add() {
		$uda = &uda::factory('voa_uda_frontend_reimburse_insert');
		/** 报销清单信息 */
		$reimburse = array();
		/** 审批人 */
		$mem = array();
		/** 抄送人 */
		$cculist = array();
		if (!$uda->reimburse_new($reimburse, $mem, $cculist)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 整理输出 */
		$uda_fmt = &uda::factory('voa_uda_frontend_reimburse_format');
		if (!$uda_fmt->reimburse($reimburse)) {
			$this->_error_message($uda_fmt->error, get_referer());
			return false;
		}

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $reimburse['rb_id']);
		$content = "申请报销\n"
				 . "申请人：".$reimburse['m_username'].""."\n"
				 . "报销主题：".$reimburse['rb_subject']."\n"
				 . "报销金额：".$reimburse['_expend']."\n"
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

		/** 写入 cookie, 刷新页面时发送 */
		$this->set_queue_session(array($data['mq_id']));

		$this->_success_message('报销操作成功', "/reimburse/view/{$reimburse['rb_id']}");
	}
}
