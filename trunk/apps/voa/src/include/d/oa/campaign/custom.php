<?php
/**
 * 自定义报名信息
 * Create By linshiling
 * $Author$
 * $Id$
 */

class voa_d_oa_campaign_custom extends voa_d_abstruct {
	
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.campaign_custom';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}
	
}

