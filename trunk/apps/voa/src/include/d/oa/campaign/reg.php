<?php
/**
 * 活动报名表
 * Create By linshiling
 * $Author$
 * $Id$
 */

class voa_d_oa_campaign_reg extends voa_d_abstruct {
	
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.campaign_reg';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}
	
	//报名(防重复提交)
	public function save($data)
	{
		$where = array('actid' => $data['actid'], 'customerid' => $data['customerid'], 'saleid' => $data['saleid']);
		$rs = $this->get_by_conds($where);
		if(!$rs) {
			$reg = $this->insert($data);
			return $reg['id'];
		}
		return $rs['id'];
	}
}

