<?php
/**
 * voa_d_oa_travel_diyindex
 * 素材
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_travel_diyindex extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.travel_diyindex';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'tiid';

		parent::__construct(null);
	}

}
