<?php

/**
 * voa_d_oa_campaign_customcols
 * 活动推广 自定义字段
 * User: Muzhitao
 * Date: 2015/8/26 0026
 * Time: 14:02
 */

class voa_d_oa_campaign_customcols extends voa_d_abstruct {
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.campaign_customcols';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'afcc_id';

		parent::__construct(null);
	}
}

//end

