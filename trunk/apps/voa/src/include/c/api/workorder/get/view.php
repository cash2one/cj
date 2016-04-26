<?php
/**
 * view.php
 * 派单 - 查看详情
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_workorder_get_view extends voa_c_api_workorder_abstract {

	/** 输出的图片附件宽度 */
	protected $_image_width = '120';

	public function execute() {

		// 需要的参数
		$fields = array(
			// 工单ID
			'id' => array('type' => 'int', 'required' => true),
			// 请求设备类型
			'device' => array('type' => 'string', 'required' => false),
			// 图片附件的宽度45\60\640
			'image_width' => array('type' => 'int', 'required' => false),
		);

		// 基本验证检查
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 是否以管理模式读取
		$is_admin = false;
		// 整理图片宽度
		$image_widths = config::get(startup_env::get('app_name').'.attachment.thumb_widths');
		if (!in_array($this->_params['image_width'], $image_widths)) {
			$this->_image_width = $image_widths[0];
		} else {
			$this->_image_width = $this->_params['image_width'];
		}

		$uda_view = &uda::factory('voa_uda_frontend_workorder_action_view');
		// 请求信息
		$request = array(
			'woid' => $this->_params['id'],
			'uid' => $this->_member['m_uid']
		);

		// 结果集合
		$result = array(
			'workorder' => array(),// 工单主表数据
			'receiver_list' => array(),// 接收人列表
			'log_list' => array(),// 工单操作日志
			'attachment_list' => array(),// 附件列表（以角色分组）
			'allow_action' => array(),// 可允许的操作动作
		);
		// 自UDA获取原始数据
		if (!$uda_view->detail($request, $result, $is_admin)) {
			$this->_errcode = $uda_view->errcode;
			$this->_errmsg = $uda_view->errmsg;
			return false;
		}

		// 当前浏览的人可执行的操作
		$uda_get = &uda::factory('voa_uda_frontend_workorder_get');
		$all_action = array();
		$uda_get->my_action($this->_member['m_uid'], $result['workorder'], $result['receiver_list']
				, $all_action, $is_admin);
		// 整理可用的用户信息
		foreach ($result['users'] as &$_user) {
			$_user = $this->_user_info_format($_user);
		}

		/** 格式化数据以输出 */
		// 载入格式化uda
		$uda_format = &uda::factory('voa_uda_frontend_workorder_format');
		// 设置输出时间格式
		$this->_set_date_format($this->_params['device']);
		// 格式化工单主表
		$uda_format->workorder($result['workorder'], $result['workorder'], $this->_date_format);
		// 格式化工单详情表
		$uda_format->workorder_detail($result['workorder_detail'], $result['workorder_detail'], $this->_date_format);
		// 格式化接收人列表
		$uda_format->workorder_receiver_list($result['receiver_list'], $result['receiver_list'], $this->_date_format);
		foreach ($result['receiver_list'] as &$_wor) {
			if (!isset($result['users'][$_wor['uid']])) {
				$_wor['user_info'] = $this->_user_info_format(array());
				continue;
			}
			$_wor['user_info'] = $result['users'][$_wor['uid']];
		}
		// 格式化工单执行人
		$uda_format->workorder_receiver($result['operator'], $result['operator'], $this->_date_format);
		// 格式化日志
		$uda_format->workorder_log_list($result['log_list'], $result['log_list'], $this->_date_format);
		foreach ($result['log_list'] as &$_log) {
			if (!isset($result['users'][$_log['uid']])) {
				$_log['user_info'] = $this->_user_info_format(array());
				continue;
			}
			$_log['user_info'] = $result['users'][$_log['uid']];
		}
		// End

		/** 整理具体输出 */
		// 工单主要信息
		$workorder = $result['workorder'];
		// 派单人信息
		$workorder['sender_info'] = $result['users'][$workorder['uid']];
		// 执行人信息
		$workorder['operator_info'] = isset($result['users'][$workorder['operator_uid']])
			? $result['users'][$workorder['operator_uid']] : $this->_user_info_format(array());
		// 工单附件
		if (isset($result['attachment_list'][voa_d_oa_workorder_attachment::ROLE_SENDER])) {
			$workorder['attachment_list'] = $result['attachment_list'][voa_d_oa_workorder_attachment::ROLE_SENDER];
			$workorder['attachment_list'] = $this->_attachment_format_list($workorder['attachment_list']);
		} else {
			$workorder['attachment_list'] = array();
		}
		// 执行结果
		$operation_result = $result['workorder_detail'];
		// 执行结果附带的附件
		$operation_result['attachment_list'] = array();
		if (isset($result['attachment_list'][voa_d_oa_workorder_attachment::ROLE_OPERATOR])) {
			$operation_result['attachment_list'] = $result['attachment_list'][voa_d_oa_workorder_attachment::ROLE_OPERATOR];
			$operation_result['attachment_list'] = $this->_attachment_format_list($operation_result['attachment_list']);
		}

		// 输出结果
		$this->_result = array(
			// 当前浏览者角色
			'role' => $this->_get_role($workorder),
			// 工单详情
			'workorder' => $workorder,
			// 执行结果
			'operation_result' => $operation_result,
			// 接收人列表
			'receiver_list' => $result['receiver_list'],
			// 操作日志
			'log_list' => $result['log_list'],
			// 可执行动作
			'allow_action' => $all_action
		);

		return true;
	}

	/**
	 * 针对工单的个人信息输出格式化
	 * @param array $user
	 * @return array
	 */
	protected function _user_info_format($user) {

		// 避免未定义的意外
		if (empty($user) || !isset($user['m_uid'])) {
			return array(
				'uid' => 0,
				'mobilephone' => '',
				'realname' => '',
				'face' => '',
			);
		}

		return array(
			'uid' => $user['m_uid'],
			'mobilephone' => $user['m_mobilephone'],
			'realname' => $user['_realname'],
			'face' => $user['_face'],
		);
	}

	/**
	 * 格式化附件列表
	 * @param array $list
	 * @return array
	 */
	protected function _attachment_format_list($list) {
		$format = array();
		foreach ($list as $_at) {
			$format[] = $this->_attachment_format($_at);
		}

		return $format;
	}

	/**
	 * 格式化单条附件信息
	 * @param array $attachment
	 * @return array
	 */
	protected function _attachment_format($attachment) {

		return array(
			'at_id' => $attachment[0]['at_id'],
			'isimage' => $attachment[0]['at_isimage'] ? 1 : 0,
			'filename' => $attachment[0]['at_filename'],
			'filesize' => $attachment[0]['at_filesize'],
			'src' => voa_h_attach::attachment_url($attachment[0]['at_id'], $this->_image_width),
			'url' => voa_h_attach::attachment_url($attachment[0]['at_id']),
		);
	}

	/**
	 * 获取当前浏览者的角色身份
	 * @param array $workorder
	 * @return array
	 */
	protected function _get_role($workorder) {

		/**
		 * 由于当前工单已经确定了权限，只有接收人、派单人、执行人可见
		 * 因此，如果不是派单人也不是执行人，那么认为其就是接收人
		 */

		if ($this->_member['m_uid'] == $workorder['uid']) {
			// 派单人
			$roleid = voa_d_oa_workorder_attachment::ROLE_SENDER;
		} elseif ($this->_member['m_uid'] == $workorder['operator_uid']) {
			// 执行人
			$roleid = voa_d_oa_workorder_attachment::ROLE_OPERATOR;
		} else {
			// 其他人接收人
			$roleid = voa_d_oa_workorder_attachment::ROLE_RECEIVER;
		}

		$uda_get = &uda::factory('voa_uda_frontend_workorder_get');
		return array(
			'roleid' => $roleid,
			'rolename' => $uda_get->attachment_roles[$roleid],
		);

	}

}
