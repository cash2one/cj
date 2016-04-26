<?php
/**
 * voa_d_oa_sale_business
 * 
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_sale_business extends voa_d_abstruct {

	public static $type = array(
		1 => '初步沟通',
		2 => '立项跟踪',
		3 => '呈报方案',
		4 => '商务谈判',
		5 => '赢单',
		6 => '输单'
	);

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.sale_business';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'bid';

		parent::__construct(null);
	}

}
