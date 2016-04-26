<?php
/**
 * 客户表
 * Create By linshiling
 * $Author$
 * $Id$
 */

class voa_d_oa_campaign_customer extends voa_d_abstruct {
	
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.campaign_customer';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}

	//保存到客户表(防重复)
	public function save($name, $mobile)
	{
		$data = array('name' => $name, 'mobile' => $mobile);
		$rs = $this->get_by_conds($data);
		if(!$rs) {
			$cus = $this->insert($data);
			return $cus['id'];
		}
		return $rs['id'];
	}
}

