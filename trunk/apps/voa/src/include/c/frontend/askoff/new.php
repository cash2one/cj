<?php
/**
 * 新的请假申请
 * $Author$
 * $Id$
 */

class voa_c_frontend_askoff_new extends voa_c_frontend_askoff_base {

	public function execute() {
		if ($this->_is_post()) {
			/** 调用处理函数 */
			$this->_add();
			return false;
		}

		/** 取草稿信息 */
		$data = array();
		$this->_get_draft($data);
		$data['message'] = isset($data['message']) ? $data['message'] : '';
		$data['accepter'] = isset($data['accepter']) ? $data['accepter'] : array();
		$data['ccusers'] = isset($data['ccusers']) ? $data['ccusers'] : array();

		/** 30 天前的日期 */
		$range_start = rgmdate(startup_env::get('timestamp') - 2592000, 'Y-m-d');

		$this->view->set('action', $this->action_name);
		$this->view->set('askoff', array('_message' => $data['message']));
		$this->view->set('accepter', $data['accepter']);
		$this->view->set('ccusers', $data['ccusers']);
		$this->view->set('types', $this->_p_sets['types']);
		$this->view->set('range_start', $range_start);
		$this->view->set('start_selected', 31);

		// 赋值jsapi接口需要的ticket
		$this->_get_jsapi("['chooseImage', 'previewImage', 'uploadImage']");

		$this->_output('askoff/post');
	}

	protected function _add() {
		$uda = &uda::factory('voa_uda_frontend_askoff_insert');
		/** 请假信息 */
		$askoff = array();
		/** 请假详情信息 */
		$post = array();
		/** 审批人信息 */
		$mem = array();
		/** 抄送人信息 */
		$cculist = array();
		if (!$uda->askoff_new($askoff, $post, $mem, $cculist)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 更新草稿信息 */
		$this->_update_draft($mem['m_uid'], array_keys($cculist));

		/** 整理输出 */
		$uda_fmt = &uda::factory('voa_uda_frontend_askoff_format');
		if (!$uda_fmt->askoff($askoff)) {
			$this->_error_message($uda_fmt->error, get_referer());
			return false;
		}

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $askoff['ao_id']);
		$content = "待 ".$mem['m_username']." 审核\n"
				 . "申请人：".$this->_user['m_username']."\n"
				 . "请假类别：".$this->_p_sets['types'][$askoff['ao_type']]."\n"
				 . "请假时长：".$askoff['_timespace']."\n"
				 . "开始时间：".$askoff['_begintime_ymdhi']."\n"
				 . "结束时间：".$askoff['_endtime_ymdhi']."\n"
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

		$this->_success_message('请假操作成功', "/askoff/view/{$askoff['ao_id']}");
	}
}

