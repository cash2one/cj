<?php
/**
 * log.php
 * 派单 - 工单操作日志表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_oa_workorder_log extends voa_d_abstruct {

	/** 动作类型: 派单人-发新工单 */
	const ACTION_SENDER_SEND = voa_d_oa_workorder::ACTION_SEND;
	/** 动作类型: 派单人-撤销工单 */
	const ACTION_SENDER_CANCEL = voa_d_oa_workorder::ACTION_CANCEL;
	/** 动作类型: 接收人-拒绝接单 */
	const ACTION_REFUSE = voa_d_oa_workorder::ACTION_REFUSE;
	/** 动作类型: 接收人-确认接单 */
	const ACTION_CONFIRM = voa_d_oa_workorder::ACTION_CONFIRM;
	/** 动作类型: 接收人-取消接单 */
	const ACTION_CANCEL = voa_d_oa_workorder::ACTION_CANCEL;
	/** 动作类型: 接收人-完成接单 */
	const ACTION_COMPLETE = voa_d_oa_workorder::ACTION_COMPLETE;
	/** 未知动作 */
	const ACTION_UNKNOWN = voa_d_oa_workorder::ACTION_UNKNOWN;

	/** 地理位置最短字符 */
	const LENGTH_LOCATION_MIN = 0;
	/** 地理位置最长字符 */
	const LENGTH_LOCATION_MAX = 100;

	/** 操作原因最短字符 */
	const LENGTH_REASON_MIN = 0;
	/** 操作原因最长字符 */
	const LENGTH_REASON_MAX = 240;

	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.workorder_log';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'wologid';

		parent::__construct(null);
	}

}
