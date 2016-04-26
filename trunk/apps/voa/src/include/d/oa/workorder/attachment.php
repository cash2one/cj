<?php
/**
 * attachment.php
 * 派单 - 附件表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_oa_workorder_attachment extends voa_d_abstruct {

	/** 一个工单最多允许提交的附件数 */
	const COUNT_MAX = 12;

	/** 附件上传人角色：派单人 */
	const ROLE_SENDER = 1;
	/** 附件上传人角色：执行人 */
	const ROLE_OPERATOR = 2;
	/** 附件上传人角色：接收人 */
	const ROLE_RECEIVER = 3;

	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.workorder_attachment';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'woatid';

		parent::__construct(null);
	}

}
