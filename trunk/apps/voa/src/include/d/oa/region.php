<?php
/**
 * 城市地区
 * User: Muzhitao
 * Date: 2015/12/24 0024
 * Time: 18:29
 * Email：muzhitao@vchangyi.com
 */

class voa_d_oa_region extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.region';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'region_id';
		parent::__construct(null);
	}
}