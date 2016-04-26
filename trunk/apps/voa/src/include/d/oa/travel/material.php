<?php
/**
 * voa_d_oa_travel_material
 * 素材
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_travel_material extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.travel_material';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'mtid';

		parent::__construct(null);
	}

}
