<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/21
 * Time: 12:25
 */
class voa_d_oa_comment extends voa_d_abstruct {

	const FIRST = 0;
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.comment';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}
}
