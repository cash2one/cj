<?php
/**
 * 分享记录
 * Create By linshiling
 * $Author$
 * $Id$
 */

class voa_d_oa_campaign_share extends voa_d_abstruct {
	
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.campaign_share';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}
	
	//保存分享记录
	public function save($actid, $saleid, $sharetime)
	{
		$data = array('actid' => $actid, 'saleid' => $saleid, 'sharetime' => $sharetime);
		$rs = $this->get_by_conds($data);
		if(!$rs) {
			$data['date'] = rgmdate($sharetime, 'Y-m-d');
			$reg = $this->insert($data);
			return $reg['id'];
		}
		return $rs['id'];
	}
}

