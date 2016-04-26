<?php
/**
 * workorder.php
 * 派单 - 主表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_oa_workorder extends voa_d_abstruct {

	/** 派发新工单 */
	const ACTION_SEND = 'send';
	/** 执行拒绝操作 */
	const ACTION_REFUSE = 'refuse';
	/** 执行确认操作 */
	const ACTION_CONFIRM = 'confirm';
	/** 执行已完成操作 */
	const ACTION_COMPLETE = 'complete';
	/** 派单人撤销派单 */
	const ACTION_CANCEL = 'cancel';
	/** 收单人撤销接单 */
	const ACTION_MYCANCEL = 'mycancel';
	/** 未知动作 */
	const ACTION_UNKNOWN = 'unknown';

	/** 工单状态：待执行  */
	const WOSTATE_WAIT = 1;
	/** 工单状态：已拒绝 */
	const WOSTATE_REFUSE = 2;
	/** 工单状态：已确认 */
	const WOSTATE_CONFIRM = 3;
	/** 工单状态：已完成 */
	const WOSTATE_COMPLETE = 4;
	/** 工单状态：派单人已撤单 */
	const WOSTATE_CANCEL = 99;

	/** 备注文字最短字符 */
	const LENGTH_REMARK_MIN = 0;
	/** 备注文字最长字符 */
	const LENGTH_REMARK_MAX = 255;

	/** 联系人最短字符 */
	const LENGTH_CONTACTER_MIN = 0;
	/** 联系人最长字符 */
	const LENGTH_CONTACTER_MAX = 32;

	/** 联系电话最短字符 */
	const LENGTH_PHONE_MIN = 0;
	/** 联系电话最长字符 */
	const LENGTH_PHONE_MAX = 32;

	/** 联系地址最短字符 */
	const LENGTH_ADDRESS_MIN = 0;
	/** 联系地址最长字符 */
	const LENGTH_ADDRESS_MAX = 200;

	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.workorder';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'woid';

		parent::__construct(null);
	}

}
