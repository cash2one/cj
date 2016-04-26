<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 16/3/21
 * Time: 14:29
 */

class voa_d_oa_questionnaire_classify extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.questionnaire_classify';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'qc_id';
		parent::__construct(null);
	}
}