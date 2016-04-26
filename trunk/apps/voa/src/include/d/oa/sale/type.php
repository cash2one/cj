<?php
/**
 * voa_d_oa_sale_type
 * 
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_sale_type extends voa_d_abstruct {

    /**
     * 自定义字段
     */
    const TYPE_FIELD = 1;
    /**
     * 自定义状态
     */
    const TYPE_STATUS = 2;
    /**
     * 自定义来源
     */
    const TYPE_SOURCE = 3;

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.sale_type';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'stid';

		parent::__construct(null);
	}

}
