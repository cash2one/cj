<?php
/**
 * detail.php
 * 派单 - 工单执行详情表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_oa_workorder_detail extends voa_d_abstruct {

	/** 执行说明文字内容最短字符 */
	const LENGTH_CAPTION_MIN = 0;
	/** 执行说明文字内容最长字符 */
	const LENGTH_CAPTION_MAX = 240;

	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.workorder_detail';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'woid';

		parent::__construct(null);
	}

}
