<?php
/**
 * Create By wogu
 * $Author$
 * $Id$
 */

class voa_d_oa_exam_paper extends voa_d_abstruct {

	public static $STATUS = array('草稿', '未开始', '正在考试中', '已结束', '已终止');

	public static $TYPES = array('自主选题', '固定规则抽题', '随机生成题目');

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.exam_paper';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}
}

