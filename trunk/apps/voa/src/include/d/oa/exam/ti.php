<?php
/**
 * Create By wogu
 * $Author$
 * $Id$
 */

class voa_d_oa_exam_ti extends voa_d_abstruct {

	const TYPE_DAN = 0;

	const TYPE_TIAN = 1;

	const TYPE_PAN = 2;

	const TYPE_DUO = 3;

	public static $TYPES = array('单选题', '问答题', '判断题', '多选题');

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.exam_ti';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}
	/**
	 * 获取实际存在的题目
	 * @param array $ids
	 * @return array $list
	 */
	public function list_by_ids_real($ids) {
		$ids = implode(',', $ids);
		$sql = "SELECT * FROM ".$this->_table." WHERE `id` IN($ids)";
		return $this->_getAll($sql);
	}
}

