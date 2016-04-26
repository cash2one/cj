<?php
/**
 * Create By wogu
 * $Author$
 * $Id$
 */

class voa_d_oa_exam_paperdetail extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.exam_paper_detail';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}

	public function delete_by_paperid($paperid) {
		return $this->_delete_real_by_conds(array('paper_id' => $paperid));
	}

	public function list_by_paperid($paperid) {
		return $this->list_by_conds(array('paper_id' => $paperid), null, array('orderby' => 'asc'));
	}

	public function real_delete_details($ids) {
		return $this->_delete_real($ids);
	}
}

