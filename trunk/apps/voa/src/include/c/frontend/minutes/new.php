<?php
/**
 * 新的会议纪要
 * $Author$
 * $Id$
 */

class voa_c_frontend_minutes_new extends voa_c_frontend_minutes_base {
	protected $_subject;
	protected $_message;

	public function execute() {
		if ($this->_is_post()) {
			/** 调用处理函数 */
			$this->_add();
			return false;
		}

		/** 取草稿信息 */
		$data = array();
		$this->_get_draft($data);

		$this->view->set('cculist', $data['ccusers']);
		$this->view->set('message', $data['message']);
		$this->view->set('accepters', $data['accepters']);
		$this->view->set('action', $this->action_name);
		$this->view->set('minutes', array());

		$this->_output('minutes/post');
	}

	public function _add() {
		$insert = &uda::factory('voa_uda_frontend_minutes_insert');
		/** 会议记录主题/内容/接收人/抄送 */
		$minutes = array();
		$post = array();
		$join_list = array();
		$ccu_list = array();
		if (!$insert->minutes_new($minutes, $post, $join_list, $ccu_list)) {
			$this->_error_message($insert->error);
			return false;
		}

		/** 更新草稿信息 */
		$this->_update_draft(array_keys($join_list), array_keys($ccu_list));

		/** 整理输出 */
		$uda_fmt = &uda::factory('voa_uda_frontend_minutes_format');
		if (!$uda_fmt->minutes($minutes)) {
			$this->_error_message($uda_fmt->error, get_referer());
			return false;
		}

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $minutes['mi_id']);
		$content = "收到会议记录\n"
				 . "主题：".$minutes['mi_subject']."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";

		/** 整理需要接收消息的用户 */
		$users = array();
		$this->__get_openids($users, $join_list);
		$this->__get_openids($users, $ccu_list);

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

		$this->_success_message('发布会议纪要成功', "/minutes/view/{$minutes['mi_id']}");
	}

	/**
	 * 获取用户信息中的 openid
	 * @param array $openids openid 数组
	 * @param array $users 用户信息数组
	 */
	private function __get_openids(&$openids, $users) {
		foreach ($users as $u) {
			if (startup_env::get('wbs_uid') == $u['m_uid']) {
				continue;
			}

			$openids[$u['m_uid']] = $u['m_openid'];
		}

		return true;
	}
}
