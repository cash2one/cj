<?php
/**
 * view.php
 * 派单 - 读取工单详情
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_workorder_action_view extends voa_uda_frontend_workorder_abstract {

	/** 当前工单ID */
	protected $_request_woid = 0;
	/** 当前请求浏览的人员UID */
	protected $_request_uid = 0;
	/** 当前请求是否以管理模式 */
	protected $_request_admin = false;
	/** 工单主表数据 */
	protected $_workorder = array();
	/** 工单执行人表数据 */
	protected $_receiver_list = array();

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 查看工单详情
	 * @param array $request 查看请求的数据
	 * + uid 当前请求浏览的人
	 * + woid 当前请求读取的工单ID
	 * @param array $result (引用结果)详情结果集合
	 * @param boolean $is_admin 是否使用管理模式
	 * @return boolean
	 */
	public function detail($request, &$result, $is_admin = false) {

		// 确实是否使用管理模式读取
		$this->_request_admin = $is_admin;

		// 未指定工单ID
		if (empty($request['woid'])) {
			return $this->set_errmsg(voa_errcode_oa_workorder::WORKORDER_ID_NULL);
		}

		// 工单ID
		$this->_request_woid = $request['woid'];
		// 请求人
		$this->_request_uid = empty($request['uid']) ? 0 : $request['uid'];

		// 读取当前工单主表信息
		if (!$this->_workorder()) {
			return $this->set_errmsg(voa_errcode_oa_workorder::WORKORDER_NOT_EXISTS, $this->_request_woid);
		}
		// 读取工单所有的接收人
		$this->_receiver_list();

		// 检查当前请求浏览的人的权限
		if (!$this->_check_power()) {
			return false;
		}

		// 所有工单参与人信息
		$users = array();
		$this->_users($users);

		// 工单执行完毕的详情报告
		$detail = array();
		$this->_detail($detail);

		// 工单全部接收人列表
		$receiver_list = array();
		// 只有管理模式 或者 派单人可见全部接收人列表
		if ($this->_request_admin || $this->_request_uid == $this->_workorder['uid']) {
			$receiver_list = $this->_receiver_list;
		} else {
			// 只可见自己
			foreach ($this->_receiver_list as $_rl) {
				if ($_rl['uid'] == $this->_request_uid) {
					$receiver_list[] = $_rl;
					break;
				}
			}
		}

		// 工单实际执行人
		$operator = array();
		if ($this->_workorder['operator_uid']) {
			// 存在实际执行人
			$operator = $this->_receiver_list[$this->_workorder['operator_uid']];
		} elseif (count($this->_receiver_list) <= 1) {
			// 如果接收人只有一个，则认为其就是执行人
			$operator = array_shift($this->_receiver_list);
		}

		// 拒绝理由
		$this->_workorder['refuse_reason'] = '';

		// 返回结果
		$result = array(
			'workorder' => $this->_workorder,
			'workorder_detail' => $detail,
			'receiver_list' => $receiver_list,
			'operator' => $operator,
			'users' => $users,
			'log_list' => array(),
			'attachment_list' => array(),
		);
		// 获取操作日志
		$this->_log($result['log_list']);
		// 获取附件信息
		$this->_attachment($result['attachment_list']);

		// 获取拒绝理由
		if ($result['log_list']) {
			foreach ($result['log_list'] as $_log) {
				if ($_log['action'] == voa_d_oa_workorder::ACTION_REFUSE) {
					$result['workorder']['refuse_reason'] = $_log['reason'];
					break;
				}
			}
		}

		return true;
	}

	/**
	 * 获取工单主表信息
	 * @return boolean
	 */
	protected function _workorder() {

		$d_workorder = new voa_d_oa_workorder();
		$this->_workorder = $d_workorder->get($this->_request_woid);
		if (empty($this->_workorder)) {
			return false;
		}

		return true;
	}

	/**
	 * 获取所有执行人列表
	 * @return boolean
	 */
	protected function _receiver_list() {

		$d_workorder_receiver = new voa_d_oa_workorder_receiver();
		$receiver_list = array();
		foreach ($d_workorder_receiver->list_by_conds(array('woid' => $this->_request_woid)) as $op) {
			$receiver_list[$op['uid']] = $op;
		}

		$this->_receiver_list = $receiver_list;
		unset($receiver_list);

		return true;
	}

	/**
	 * 检查当前请求浏览的人读取工单的权限
	 * @return boolean
	 */
	protected function _check_power() {

		// 管理模式请求 或 当前请求人是派单人，则有权
		if ($this->_request_admin || $this->_request_uid == $this->_workorder['uid']) {
			return true;
		}

		// 当前请求人不在工单接收列表内，则无权
		if (!isset($this->_receiver_list[$this->_request_uid])) {
			return $this->set_errmsg(voa_errcode_oa_workorder::USER_NOT_OPERATOR);
		}

		// 工单已进入确认阶段，或者已完成，则判断工单执行人是否是当前浏览的人
		if ($this->_workorder['operator_uid'] > 0 && $this->_workorder['operator_uid'] != $this->_request_uid) {
			return $this->set_errmsg(voa_errcode_oa_workorder::WORKORDER_WORKING);
		}

		return true;
	}

	/**
	 * 读取工单详情表
	 * @param array $wo_detail (引用结果)详情表数据
	 * @return boolean
	 */
	protected function _detail(&$wo_detail) {

		// 读取工单详情
		$wo_detail = array();
		$d_workorder_detail = new voa_d_oa_workorder_detail();
		$wo_detail = $d_workorder_detail->get_by_conds(array('woid' => $this->_request_woid));
		// 不存在工单详情，则使用默认字段填充，避免键名缺失
		if (!$wo_detail) {
			$wo_detail = $d_workorder_detail->get_default_value();
			$wo_detail['woid'] = $this->_request_woid;
		}

		return true;
	}

	/**
	 * 全部工单参与人信息
	 * @param array $users
	 * @return boolean
	 */
	protected function _users(&$users) {

		// 工单的所有全部参与人uid
		$uids = array_keys($this->_receiver_list);
		// 派单人
		$uids[] = $this->_workorder['uid'];

		// 读取全部参与人信息
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $serv_m->fetch_all_by_ids($uids);
		voa_h_user::push($users);
		// 格式化参与人信息
		$uda_member_format = &uda::factory('voa_uda_frontend_member_format');
		$uda_member_format->data_list($users, 'm_uid', $users);

		return true;
	}

	/**
	 * 读取所有操作日志
	 * @param array $log_list (引用结果)
	 * @return boolean
	 */
	protected function _log(&$log_list) {

		$log_list = array();
		$conds = array(
			'woid' => $this->_request_woid,
		);

		// 读取记录
		$d_workorder_log = new voa_d_oa_workorder_log();
		$log_list = $d_workorder_log->list_by_conds($conds, array(), array('wologid' => 'DESC'));

		return true;
	}

	/**
	 * 读取工单附件
	 * @param array $attachment_list (引用结果)
	 * @return boolean
	 */
	protected function _attachment(&$attachment_list) {

		$attachment_list = array();

		// 读取工单所有的附件
		$d_attachment = new voa_d_oa_workorder_attachment();
		$conds = array('woid' => $this->_request_woid);
		$w_list = $d_attachment->list_by_conds($conds);
		if (empty($w_list)) {
			return true;
		}

		// 找到所有的附件ID
		$at_ids = array();
		foreach ($w_list as $_at) {
			if (!isset($at_ids[$_at['at_id']])) {
				$at_ids[$_at['at_id']] = $_at['at_id'];
			}
		}

		// 找到所有附件原始数据
		$ats = array();
		$serv_attachment = &service::factory('voa_s_oa_common_attachment');
		$list = $serv_attachment->fetch_by_ids($at_ids);
		if (empty($list)) {
			return true;
		}

		// 按附件上传者角色分组输出
		$attachment_list = array();
		foreach ($w_list as $_w_at) {
			if (!isset($list[$_w_at['at_id']])) {
				continue;
			}
			// 输出附件原始信息和派单附件信息
			// array(原始, 派单)
			$attachment_list[$_w_at['role']][$_w_at['at_id']] = array($list[$_w_at['at_id']], $_w_at);
		}

		return true;
	}

}
