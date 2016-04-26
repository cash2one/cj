<?php
/**
 * voa_d_oa_travel_customer_remark
 * 配置信息
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_travel_customer_remark extends voa_d_abstruct {
	// 备注类型
	const TYPE_TEXT = 1;
	const TYPE_PIC = 2;
	const TYPE_VOICE = 3;
	const TYPE_REMIND = 4;

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.travel_customer_remark';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'crk_id';

		parent::__construct(null);
	}

}

