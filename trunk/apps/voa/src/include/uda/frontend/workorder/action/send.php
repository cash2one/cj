<?php
/**
 * send.php
 * 派单 - 新建工单、派发新工单
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_workorder_action_send extends voa_uda_frontend_workorder_abstract {

	/** 提交的参数检查校验方法映射表 */
	protected $_param_validator_methods = array();

	public function __construct() {
		parent::__construct();

		// 校验方法映射表，方法名来自 voa_uda_frontend_workorder_validator
		// 不需要校验的参数则为空
		$this->_param_validator_methods = array(
			'remark' => 'workorder_remark',
			'receiver_uids' => 'receiver_uids',
			'contacter' => 'workorder_contacter',
			'phone' => 'workorder_phone',
			'address' => 'workorder_address'
		);
	}

	/**
	 * 发出新工单
	 * @param array $workorder 工单数据
	 * array(
	 * 	'remark' => string,// 工单备注
	 * 	'receiver_uids' => mixed,// 分配接单人uid，可以number（单人），也可以数组（多人），也可以使用半角逗号分隔（多人）
	 * 	'contacter' => string,// 联系人
	 * 	'phone' => string,// 联系电话
	 * 	'address' => string,// 联系地址
	 * 	'uid' => number,// 当前派单人uid
	 * )
	 * @param array $workorder 工单详情
	 * @param boolean $is_admin 是否是管理模式
	 * @return boolean
	 */
	public function create($request, &$workorder, $is_admin = false) {

		// 检查派单人
		if (empty($request['uid'])) {
			return $this->set_errmsg(voa_errcode_oa_workorder::WORKORDER_UID_NULL);
		}

		// 避免未提交的数据存在
		foreach ($this->_param_validator_methods as $_key => $_method) {
			if (!isset($request[$_key])) {
				$request[$_key] = '';
			}
		}

		// 检查是否允许派单
		$uda_get = &uda::factory('voa_uda_frontend_workorder_get');
		$check_allow_send_request = array(
			'uid' => $request['uid'],
			'is_admin' => false,
		);
		if (!$uda_get->allow_send($check_allow_send_request)) {
			$this->errcode = $uda_get->errcode;
			$this->errmsg = $uda_get->errmsg;
			return false;
		}

		// 数据合法性验证并整理请求的数据
		if (!$this->_check_add($request)) {
			return false;
		}

		// 检查是否存在给自己派单的情况
		if (in_array($request['uid'], $request['receiver_uids'])) {
			foreach ($request['receiver_uids'] as $_key => $_uid) {
				if ($_uid == $request['uid']) {
					// 移除派单人自己
					unset($request['receiver_uids'][$_key]);
				}
			}
			if (empty($request['receiver_uids'])) {
				return $this->set_errmsg(voa_errcode_oa_workorder::SEND_DO_NOT_SEND_SELF);
			}
		}

		// 整理工单数据
		$workorder = array();
		if (!$this->_tidy_workorder($request, $workorder)) {
			return false;
		}

		$d_workorder = new voa_d_oa_workorder();
		$d_workorder_detail = new voa_d_oa_workorder_detail();
		$d_workorder_receiver = new voa_d_oa_workorder_receiver();
		$d_workorder_log = new voa_d_oa_workorder_log();
		try {

			$d_workorder->beginTransaction();

			// 写入工单表
			$workorder = $d_workorder->insert($workorder);
			$workorder_detail = array('woid' => $workorder['woid']);
			// 写入工单详情表
			$d_workorder_detail->insert($workorder_detail);
			// 写入接收人表
			$this->_write_table_receiver($workorder['woid'], $request['receiver_uids'], $d_workorder_receiver);
			// 写入操作日志
			$this->_write_table_log($workorder, $d_workorder_log);

			$d_workorder->commit();

		} catch (Exception $e) {
			$d_workorder->rollBack();
			return $this->set_errmsg(voa_errcode_oa_workorder::SEND_WORKORDER_DB_ERROR);
		}

		return true;
	}

	/**
	 * 整理提交的工单数据
	 * @param array $request 提交请求的数据
	 * @param array $workorder (引用结果)工单主表数据
	 * @return boolean
	 */
	protected function _tidy_workorder($request, &$workorder) {

		// 待写入的工单数据
		$workorder = array();
		// 需要从外部提取的数据
		$fields = array('uid', 'contacter', 'phone', 'address', 'remark');
		// 检查数据是否存在
		foreach ($fields as $key) {
			if (!isset($request[$key])) {
				$this->set_errmsg(voa_errcode_oa_workorder::SEND_WORKORDER_PARAM_LOSE, $key);
				return false;
			}
			$workorder[$key] = $request[$key];
		}

		// 标记为新工单 - 待处理
		$workorder['wostate'] = voa_d_oa_workorder::WOSTATE_WAIT;
		// 由于尚未有人执行，则设置执行人为0
		$workorder['operator_uid'] = 0;
		// 派单时间
		$workorder['ordertime'] = $this->_timestamp;
		// 撤单时间
		$workorder['canceltime'] = 0;
		// 接单人确认时间，新工单为0
		$workorder['confirmtime'] = 0;
		// 工单尚未执行，完成时间为0
		$workorder['completetime'] = 0;

		return true;
	}

	/**
	 * 将数据写入接收人表
	 * @param number $woid 工单ID
	 * @param array $receiver_uids 接收人列表
	 * @param object $d_workorder_receiver 表操作对象
	 * @return void
	 */
	protected function _write_table_receiver($woid, $receiver_uids, $d_workorder_receiver) {

		// 遍历所有接收人
		foreach ($receiver_uids as $_uid) {
			$_receiver_data = array(
				'worstate' => voa_d_oa_workorder_receiver::WORSTATE_WAIT,
				'woid' => $woid,
				'uid' => $_uid,
				'ordertime' => $this->_timestamp,
				'actiontime' => 0,
				'completetime' => 0,
			);
			$d_workorder_receiver->insert($_receiver_data);
		}

	}

	/**
	 * 写入操作日志表数据
	 * @param array $workorder 工单主表数据
	 * @param object $d_workorder_log 日志表操作对象
	 * @return void
	 */
	protected function _write_table_log($workorder, $d_workorder_log) {

		// 获取派单人地理位置信息
		$location = voa_h_location::get_address($workorder['uid'], null, $this->plugin_setting['longlat_expire']);

		$log_data = array(
			'woid' => $workorder['woid'],
			'uid' => $workorder['uid'],
			'action' => voa_d_oa_workorder::ACTION_SEND,
			'time' => $this->_timestamp,
			'ip' => $location['ip'],
			'long' => $location['long'],
			'lat' => $location['lat'],
			'location' => $location['address'],
			'reason' => $workorder['remark']
		);

		// 写入日志表数据
		$d_workorder_log->insert($log_data);
	}

	/**
	 * 新增工单的数据检查
	 * 本方法提供的数据必须经键名存在性校验
	 * 本方法不对不存在的键名进行校验
	 * @param array $workorder
	 * + remark
	 * + operator_uid
	 * + contacter
	 * + phone
	 * + address
	 * @return boolean
	 */
	protected function _check_add(&$data) {

		if (!is_array($data)) {
			return true;
		}

		$uda_validator = &uda::factory('voa_uda_frontend_workorder_validator');
		foreach ($data as $key => &$value) {

			// 不存在的校验方法则忽略
			if (!isset($this->_param_validator_methods[$key])) {
				continue;
			}

			// 需要调用的校验方法
			$method = $this->_param_validator_methods[$key];
			// 不需要校验
			if (!$method) {
				continue;
			}
			// 校验方法是否存在
			if (!method_exists($uda_validator, $method)) {
				return $this->set_errmsg(voa_errcode_oa_workorder::SEND_VALIDATOR_METHOD_NOT_EXISTS, $method);
			}
			// 检查数据
			if (!$uda_validator->$method($value)) {
				$this->errcode = $uda_validator->errcode;
				$this->errmsg = $uda_validator->errmsg;
				return false;
			}
		}

		return true;
	}

}
