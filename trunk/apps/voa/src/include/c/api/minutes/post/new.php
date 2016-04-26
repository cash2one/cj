<?php
/**
 * voa_c_api_minutes_post_new
 * 新的会议纪要
 * $Author$
 * $Id$
 */

class voa_c_api_minutes_post_new extends voa_c_api_minutes_base {

	public function execute() {

		//调试信息
		/*date_default_timezone_set('PRC');
		ini_set('display_errors', 1);
		error_reporting(E_ALL & ~E_NOTICE);
		header('Content-Type:text/html;charset=utf-8');
		$request = controller_request::get_instance();
		$this->_params = array(
			'subject' =>	'一条会议记录',
			'message' =>	'这儿是详细的会议说明',
			'recvuids' =>	'1',
			'carboncopyuids' =>	'2,4',
		);
		$request->set_params($this->_params);*/
		
		/*需要的参数*/
		$fields = array(
			/*会议记录主题 */
			'subject' => array('type' => 'string_trim', 'required' => true),
			/*会议记录内容*/
			'message' => array('type' => 'string_trim', 'required' => true),
			/*会议记录接收人*/
			'recvuids' => array('type' => 'string_trim', 'required' => true),
			/*会议记录抄送人*/
			'carboncopyuids' => array('type' => 'string_trim', 'required' => false),
		);

		/*基本验证检查*/
		if (!$this->_check_params($fields)) {
			return false;
		}

		/*会议记录主题*/
		if (empty($this->_params['subject'])) {
			return $this->_set_errcode('subject is null');
		}

		/*会议记录内容检查*/
		if (empty($this->_params['message'])) {
			return $this->_set_errcode('message is null');
		}

		/*会议记录接收人检查*/
		if (empty($this->_params['recvuids'])) {
			return $this->_set_errcode('recvuids is null');
		}

		/*接收人号不能是自己检查*/
		if ($this->_params['recvuids'] == startup_env::get('wbs_uid')) {
			return $this->_set_errcode('recvuids is self');
		}

		/*入库操作*/
		if (!$this->_add()) {
			return $this->_set_errcode('add fail');
		}

		$this->_result = array(
			'mi_id' => $this->_return['mi_id']
		);
		return true;
	}
	/*
	 * 入库
	 * @return boolen 新增成功
	*/
	public function _add() {
		$insert = &uda::factory('voa_uda_frontend_minutes_insert');
		/** 会议记录主题/内容/接收人/抄送 */
		$minutes = array();
		$post = array();
		$join_list = array();
		$ccu_list = array();
		if (!$insert->minutes_new($minutes, $post, $join_list, $ccu_list)) {
			//$this->_error_message($insert->error);
			return $this->_set_errcode($insert->errmsg);
		}
		
		$this->_return = $minutes;
		
		/** 更新草稿信息 */
		$this->_update_draft(array_keys($join_list), array_keys($ccu_list));

		/** 整理输出 */
		$uda_fmt = &uda::factory('voa_uda_frontend_minutes_format');
		if (!$uda_fmt->minutes($minutes)) {
			//$this->_error_message($uda_fmt->error, get_referer());
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
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

		return true;
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
