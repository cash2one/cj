<?php
/**
 * receiver.php
 * 派单 - 接收人表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_oa_workorder_receiver extends voa_d_abstruct {

	/** 接单状态：待确认 */
	const WORSTATE_WAIT = 1;
	/** 接单状态：已拒绝 */
	const WORSTATE_REFUSE = 2;
	/** 接单状态：已确认 */
	const WORSTATE_CONFIRM = 3;
	/** 接单状态：我已完成 */
	const WORSTATE_MYCOMPLETE = 4;
	/** 接单状态：被抢单 */
	const WORSTATE_ROBBED = 5;
	/** 接单状态：接单人撤单 */
	const WORSTATE_MYCANCEL = 6;
	/** 接单状态：别人已完成 */
	const WORSTATE_COMPLETE = 7;
	/** 接单状态：派单人撤单 */
	const WORSTATE_CANCEL = 99;

	/** 地理位置最短字符 */
	const LENGTH_LOCATION_MIN = 0;
	/** 地理位置最长字符 */
	const LENGTH_LOCATION_MAX = 100;

	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.workorder_receiver';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'worid';

		parent::__construct(null);
	}

}
