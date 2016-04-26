<?php
/**
 * $Author$
 * $Id$
 */

class voa_d_oa_sign_record extends voa_d_abstruct {
	/** 主键 */
	private static $__pk = 'sr_id';
	/** 未知 */
	const STATUS_UNKNOWN = 0;
	/** 正常出勤 */
	const STATUS_WORK = 1;
	/** 迟到 */
	const STATUS_LATE = 2;
	/** 早退 */
	const STATUS_LEAVE = 4;
	/** 旷工 */
	const STATUS_ABSENT = 8;
	/** 请假 */
	const STATUS_OFF = 16;
	/** 出差 */
	const STATUS_EVECTION = 32;
	/** 删除 */
	const STATUS_REMOVE = 64;
	/**全部数据*/
	const TYPE_ALL = 0;
	/** 上班状态值 */
	const TYPE_ON = 1;
	/** 下班状态值 */
	const TYPE_OFF = 2;
	/** 上报状态值 */
	const TYPE_UP = 3;
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.sign_record';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		
		$this->_prefield = 'sr_';
		/** 主键 */
		$this->_pk = 'sr_id';

		parent::__construct(null);
	}

}

