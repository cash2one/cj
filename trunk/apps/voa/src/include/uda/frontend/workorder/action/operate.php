<?php
/**
 * operate.php
 * 派单 - 执行动作
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_workorder_action_operate extends voa_uda_frontend_workorder_abstract {

	/** 外部请求的参数 */
	protected $_extrequest = array(
		'woid' => 0,// 工单ID
		'uid' => 0,// 请求操作的人员ID
		'action' => '',// 请求执行的动作
		'data' => array(),// 请求执行动作的附加数据，具体动作附加的数据也不同
	);
	/** 请求执行的动作附加数据，等同 this->_extrequest['data'] */
	protected $_request_data = array();
	/** 是否是管理模式请求 */
	protected $_request_admin = false;

	/** 工单主表信息 */
	protected $_workorder = array();
	/** 获取当前工单所有收单人列表信息 */
	protected $_receiver_list = array();

	/** 需要更新的工单数据*/
	protected $_update_workorder = false;
	/** 需要更新的执行人数据 */
	protected $_update_operator_self = false;
	/** 需要更新其他接收人的数据 */
	protected $_update_receiver_other = false;
	/** 需要更新所有收单人的数据 */
	protected $_update_receiver_all = false;
	/** 需要更新、添加的工单详情数据 */
	protected $_update_workorder_detail = false;
	/** 需要写入到log日志的操作备注文字 */
	protected $_log_reason = '';

	/** 上传的附件id数组 */
	protected $_at_ids = array();
	/** 附件上传者角色：1\2\3见voa_d_oa_workorder_attachment定义 */
	protected $_at_role = false;

	/** 数据表操作对象：工单主表 */
	protected $_d_workorder = null;
	/** 数据表操作对象：附件表 */
	protected $_d_workorder_attachment = null;
	/** 数据表操作对象：详情表 */
	protected $_d_workorder_detail = null;
	/** 数据表操作对象：操作日志表 */
	protected $_d_workorder_log = null;
	/** 数据表操作对象：接收人表 */
	protected $_d_workorder_receiver = null;

	public function __construct() {
		parent::__construct();

		$this->_d_workorder = new voa_d_oa_workorder();
		$this->_d_workorder_attachment = new voa_d_oa_workorder_attachment();
		$this->_d_workorder_detail = new voa_d_oa_workorder_detail();
		$this->_d_workorder_log = new voa_d_oa_workorder_log();
		$this->_d_workorder_receiver = new voa_d_oa_workorder_receiver();
	}

	/**
	 * 执行操作
	 * @param array $request 请求的操作
	 * + uid 当前请求人
	 * + woid 请求的工单ID
	 * + action 请求的动作 cancel...
	 * + admin 是否为管理操作
	 * + data 附加的数据，根据具体动作要求也不一样
	 * @param array $result (引用结果)返回的结果
	 * @param boolean $is_admin 是否是管理模式
	 * @return boolean
	 */
	public function go($request, &$result = array(), $is_admin = false) {

		// 返回的结果
		$result = array();

		$this->_request_admin = $is_admin;

		// 请求参数的可用性检查
		if (empty($request['woid'])) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATE_WORKORDER_ID_NULL);
		}
		if (empty($request['uid'])) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATE_WORKORDER_REQUEST_UID_NULL);
		}
		if (empty($request['action']) || !isset($this->actions[$request['action']])) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATE_ACTION_UNKNOW,
					$request['action'] ? $request['action'] : 'null');
		}
		// 将请求的参数赋值给内部成员
		foreach ($this->_extrequest as $_key => $_default_value) {
			if (!isset($request[$_key])) {
				$this->_extrequest[$_key] = $_default_value;
			} else {
				$this->_extrequest[$_key] = $request[$_key];
			}
		}
		$this->_request_data = $this->_extrequest['data'];

		// 读取工单主表
		if (!$this->_workorder()) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATE_WORKORDER_NOT_EXISTS,
					$this->_extrequest['woid']);
		}
		// 如果工单已被撤销将无法进行任何操作
		if ($this->_workorder['wostate'] == voa_d_oa_workorder::WOSTATE_CANCEL) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATE_WORKORDER_CANCEL,
					$this->actions[$this->_extrequest['action']]);
		}
		// 如果工单已完成，将不做任何操作
		if ($this->_workorder['wostate'] == voa_d_oa_workorder::WOSTATE_COMPLETE) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATE_WORKORDER_COMPLETE,
					$this->actions[$this->_extrequest['action']]);
		}

		// 读取所有的接收人
		$this->_get_receiver_list();

		// 检查是否具有执行请求动作的权限
		$uda_get = &uda::factory('voa_uda_frontend_workorder_get');
		// 当前请求动作人员可执行的动作列表
		$my_allow_actions = array();
		$uda_get->my_action($this->_extrequest['uid'], $this->_workorder, $this->_receiver_list, $my_allow_actions);
		// 无权进行当前操作
		if (!in_array($this->_extrequest['action'], $my_allow_actions)) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATE_ACTION_NO_POWER, $this->_extrequest['action']);
		}

		// 获取需要执行具体的操作数据
		$method = '_action_go_'.$this->_extrequest['action'];
		if (!$this->$method()) {
			return false;
		}

		// 实际更新数据表操作
		if (!$this->_update_workorder()) {
			return false;
		}

		return true;
	}

	/**
	 * 获取工单主表信息
	 * @return boolean
	 */
	protected function _workorder() {

		$this->_workorder = $this->_d_workorder->get($this->_extrequest['woid']);
		if (empty($this->_workorder)) {
			return false;
		}

		return true;
	}

	/**
	 * 获取所有执行人列表
	 * @return void
	 */
	protected function _get_receiver_list() {

		$receiver_list = array();
		foreach ($this->_d_workorder_receiver->list_by_conds(array('woid' => $this->_extrequest['woid'])) as $_wor) {
			$receiver_list[$_wor['uid']] = $_wor;
		}
		$this->_receiver_list = $receiver_list;
		unset($receiver_list, $_wor);
	}

	/**
	 * 执行拒绝接单操作
	 * + request['data']['reason'] 操作原因
	 * @return boolean
	 */
	protected function _action_go_refuse() {

		if (empty($this->_request_data['reason'])) {
			$this->set_errmsg(voa_errcode_oa_workorder::OPERATE_REFUSE_REASON_NULL);
		}

		// 检查拒绝原因文字是否合法
		$uda_validator = &uda::factory('voa_uda_frontend_workorder_validator');
		if (!$uda_validator->log_reason($this->_request_data['reason'])) {
			$this->errcode = $uda_validator->errcode;
			$this->errmsg = $uda_validator->errmsg;
			return false;
		}

		// 工单更新数据
		$this->_update_workorder = array(
			'wostate' => voa_d_oa_workorder::WOSTATE_REFUSE,
			'confirmtime' => 0,
			'completetime' => 0
		);
		// 执行人
		$this->_update_operator_self = array(
			'worstate' => voa_d_oa_workorder_receiver::WORSTATE_REFUSE,
			'actiontime' => startup_env::get('timestamp'),
			'completetime' => 0
		);
		// 其他收单人更新数据，当前人拒绝了，其他人则维持原有状态保持不变
		$this->_update_receiver_other = false;
		// 日志备注
		$this->_log_reason = $this->_request_data['reason'];

		return true;
	}

	/**
	 * 执行工单确认操作
	 * @return boolean
	 */
	protected function _action_go_confirm() {

		// 工单表更新
		$this->_update_workorder = array(
			'wostate' => voa_d_oa_workorder::WOSTATE_CONFIRM,
			'operator_uid' => $this->_extrequest['uid'],
			'confirmtime' => startup_env::get('timestamp'),
			'completetime' => 0,
		);
		// 执行人
		$this->_update_operator_self = array(
			'worstate' => voa_d_oa_workorder_receiver::WORSTATE_CONFIRM,
			'actiontime' => startup_env::get('timestamp'),
			'completetime' => 0,
		);
		// 其他收单人，由于执行人已确认，其他人不能再进行操作
		$this->_update_receiver_other = array(
			'worstate' => voa_d_oa_workorder_receiver::WORSTATE_ROBBED,
			'completetime' => 0,
		);

		return true;
	}

	/**
	 * 派单人执行撤单操作
	 * @return boolean
	 */
	protected function _action_go_cancel() {

		// 工单表更新
		$this->_update_workorder = array(
			'wostate' => voa_d_oa_workorder::WOSTATE_CANCEL,
			'canceltime' => startup_env::get('timestamp'),
		);
		// 收单人表更新
		$this->_update_receiver_all = array(
			'worstate' => voa_d_oa_workorder_receiver::WORSTATE_CANCEL,
		);

		return true;
	}

	/**
	 * 执行完成工单操作
	 * + request['data']['at_ids'] 附件id
	 * + request['data']['caption'] 完成说明
	 * @return boolean
	 */
	protected function _action_go_complete() {

		if (empty($this->_request_data['caption'])) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATOR_CAPTION_NULL);
		}

		$uda_validator = &uda::factory('voa_uda_frontend_workorder_validator');
		if (!$uda_validator->detail_caption($this->_request_data['caption'])) {
			return false;
		}

		// 工单表更新
		$this->_update_workorder = array(
			'wostate' => voa_d_oa_workorder::WOSTATE_COMPLETE,
			'operator_uid' => $this->_extrequest['uid'],
			'completetime' => startup_env::get('timestamp'),
		);
		// 执行人更新自己
		$this->_update_operator_self = array(
			'worstate' => voa_d_oa_workorder_receiver::WORSTATE_MYCOMPLETE,
			'actiontime' => startup_env::get('timestamp'),
			'completetime' => startup_env::get('timestamp')
		);
		// 其他人标记已完成
		$this->_update_receiver_other = array(
			'worstate' => voa_d_oa_workorder_receiver::WORSTATE_COMPLETE,
		);
		// 完成工单的详情
		$this->_update_workorder_detail = array(
			'woid' => $this->_extrequest['woid'],
			'caption' => $this->_request_data['caption'],
		);
		// 日志备注
		$this->_log_reason = $this->_request_data['caption'];

		/**
		 * ##############
		 * 注意！以下只允许操作附件，不允许赋值其他操作！！
		 * ##############
		 */
		// 不存在附件
		if (empty($this->_request_data['at_ids'])) {

			// 系统要求完成工单时必须上传附件
			if ($this->plugin_setting['complete_upload_count_min'] > 0) {
				return $this->set_errmsg(voa_errcode_oa_workorder::COMPLETE_ATTACHMENT_REQUIRED);
			}

			// 允许不上传附件，直接返回
			return true;
		}

		// 筛选出可用的at_id，避免非法提交
		$tmp = $this->_request_data['at_ids'];
		if (is_scalar($this->_request_data['at_ids'])) {
			$tmp = explode(',', $this->_request_data['at_ids']);
		}
		// 附件id提交错误，报错
		if (!is_array($tmp)) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATOR_ATID_ERROR);
		}

		// 提取不重复有效的at_id
		$at_ids = array();
		foreach ($tmp as $_aid) {
			$_aid = trim($_aid);
			if (!is_numeric($_aid) || $_aid < 0 || isset($at_ids[$_aid])) {
				continue;
			}
			$at_ids[$_aid] = $_aid;
		}
		unset($tmp, $_aid);

		// 没有有效的附件ID，直接报错
		if (empty($at_ids)) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATOR_ATID_NULL);
		}

		$serv_attachment = &service::factory('voa_s_oa_common_attachment');
		// 工单实际需要的at_ids
		$wo_at_ids = array();
		foreach ($serv_attachment->fetch_by_ids($at_ids) as $_at) {

			// 不是本人上传的则忽略
			if ($_at['m_uid'] != $this->_extrequest['uid']) {
				continue;
			}
			// 上传时间太久了也忽略，这里只是一个硬性的判断，也许业务并不需要，可移除
			//if (startup_env::get('timestamp') - $_at['at_created'] > 43200) {
				//continue;
			//}
			$wo_at_ids[] = $_at['at_id'];
		}

		// 没有有效的附件ID，报错
		if (empty($wo_at_ids)) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATOR_ATTACHMENT_ERROR);
		}

		// 要求附件数量了，则判断
		$count = count($this->_at_ids);
		// 附件数不符合要求，小于规定的数量
		if ($this->plugin_setting['complete_upload_count_min'] > 1 && $count < $this->plugin_setting['complete_upload_count_min']) {
			return $this->set_errmsg(voa_errcode_oa_workorder::COMPLETE_ATTACHMENT_COUNT_ERROR
					, $this->plugin_setting['complete_upload_count_min']);
		}
		// 系统要求附件最少2个但上传超过要求数*2  或 附件最少要求数大于6个
		if ($count > voa_d_oa_workorder_attachment::COUNT_MAX) {
			return $this->set_errmsg(voa_errcode_oa_workorder::COMPLETE_ATTACHMENT_COUNT_MAX, voa_d_oa_workorder_attachment::COUNT_MAX);
		}

		// 存在本人真实上传的附件，赋值以便于后面导入数据
		$this->_at_ids = $wo_at_ids;
		// 附件上传者类型，执行人
		$this->_at_role = voa_d_oa_workorder_attachment::ROLE_OPERATOR;

		unset($wo_at_ids, $_at);

		return true;
	}

	/**
	 * 执行人取消接单操作
	 * + request_data['reason'] 取消原因
	 * @return boolean
	 */
	protected function _action_go_mycancel() {

		if (empty($this->_request_data['reason'])) {
			$this->set_errmsg(voa_errcode_oa_workorder::OPERATE_REFUSE_REASON_NULL);
		}

		// 检查拒绝原因文字是否合法
		$uda_validator = &uda::factory('voa_uda_frontend_workorder_validator');
		if (!$uda_validator->receiver_reason($this->_request_data['reason'])) {
			$this->errcode = $uda_validator->errcode;
			$this->errmsg = $uda_validator->errmsg;
			return false;
		}

		/**
		 * 执行人取消接单操作会存在两种情况：
		 * 1. 工单只有唯一一个接收人，则禁止取消接单
		 * 2. 工单多个接收人，则允许接单
		 */
		if (count($this->_receiver_list) <= 1) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATOR_NOT_OTHER);
		}

		// 工单表
		$this->_update_workorder = array(
			'wostate' => voa_d_oa_workorder::WOSTATE_WAIT,// 由于执行人已取消，则工单重新置于等待执行状态
			'operator_uid' => 0,// 执行者重置为无人状态
			'confirmtime' => 0,// 确认时间重置为0
			'completetime' => 0,
		);
		// 执行人
		$this->_update_operator_self = array(
			'worstate' => voa_d_oa_workorder_receiver::WORSTATE_MYCANCEL,
			'actiontime' => startup_env::get('timestamp'),
			'completetime' => 0
		);
		// 其他收单人，不做任何操作
		$this->_update_receiver_other = false;
		// 操作日志
		$this->_log_reason = $this->_request_data['reason'];

		return true;
	}



	/**
	 * 执行人更新自己的数据
	 * @return boolean
	 */
	protected function _update_db_operator_self() {

		if (!$this->_update_operator_self) {
			return true;
		}

		// 找到待更新的数据 —— 实际是找到当前执行人
		$this->_d_workorder_receiver->update_by_conds(array(
			'woid' => $this->_extrequest['woid'],
			'uid' => $this->_extrequest['uid']
		), $this->_update_operator_self);

		return true;
	}

	/**
	 * 更新当前工单执行人以外的其他收单人
	 * @return boolean
	 */
	protected function _update_db_receiver_other() {

		if (!$this->_update_receiver_other) {
			return true;
		}

		// 找到待更新的人 —— 实际是找到当前工单收单人除了当前执行人以外的其他收单人
		$this->_d_workorder_receiver->update_by_conds(array(
			'woid' => $this->_extrequest['uid'],
			'uid<>?' => $this->_extrequest['uid'],
		), $this->_update_receiver_other);

		return true;
	}

	/**
	 * 对所有收单人进行数据更新
	 * @return boolean
	 */
	protected function _update_db_receiver_all() {

		if (!$this->_update_receiver_all) {
			return true;
		}

		$this->_d_workorder_receiver->update_by_conds(array(
				'woid' => $this->_extrequest['woid']
			), $this->_update_receiver_all);

		return true;
	}

	/**
	 * 写入操作日志
	 * @return void
	 */
	protected function _write_log() {

		// 获取当前操作人的地理位置信息
		$mylocation = voa_h_location::get_address($this->_extrequest['uid']);
		// 地理位置表更新
		$log = array(
			'woid' => $this->_extrequest['woid'],
			'uid' => $this->_extrequest['uid'],
			'action' => $this->_extrequest['action'],
			'time' => startup_env::get('timestamp'),
			'ip' => $mylocation['ip'],
			'long' => $mylocation['long'],
			'lat' => $mylocation['lat'],
			'location' => $mylocation['address'],
			'reason' => $this->_log_reason
		);
		$this->_d_workorder_log->insert($log);
		unset($mylocation);
	}

	/**
	 * 更新工单执行全部数据
	 * @return boolean
	 */
	protected function _update_workorder() {

		// 上传了附件，但未指定附件上传者角色类型
		if ($this->_at_ids && (!$this->_at_role || !isset($this->attachment_roles[$this->_at_role]))) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATOR_ATTACHMENT_ROLE_ERROR, $this->_at_role);
		}

		try {

			$this->_d_workorder->beginTransaction();

			// 更新工单表
			$this->_d_workorder->update($this->_extrequest['woid'], $this->_update_workorder);

			// 更新收单人表：当前执行人
			$this->_update_db_operator_self();
			// 更新收单人表：除执行人外的其他收单人
			$this->_update_db_receiver_other();
			// 更新收单人表：全部收单人
			$this->_update_db_receiver_all();

			// 写入操作日志
			$this->_write_log();

			// 更新详情表
			if (!empty($this->_update_workorder_detail)) {

				// 检查详情信息数据是否存在
				$wh = $this->_d_workorder_detail->get_by_conds(array('woid' => $this->_extrequest['woid']));
				if ($wh) {
					// 存在详情数据，则更新
					$this->_d_workorder_detail->update($this->_extrequest['woid'], $this->_update_workorder_detail);
				} else {
					// 不存在则新增
					$this->_d_workorder_detail->insert($this->_update_workorder_detail);
				}
			}

			// 上传了附件
			if ($this->_at_ids) {
				foreach ($this->_at_ids as $_at_id) {
					$attachment = array(
						'woid' => $this->_extrequest['woid'],
						'at_id' => $_at_id,
						'uid' => $this->_extrequest['uid'],
						'role' => $this->_at_role,
						'time' => startup_env::get('timestamp')
					);
					$this->_d_workorder_attachment->insert($attachment);
				}
			}

			$this->_d_workorder->commit();

		} catch (Exception $e) {
			$this->_d_workorder->rollBack();
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATE_WORKORDER_DB_ERROR);
		}

		return true;
	}

}
