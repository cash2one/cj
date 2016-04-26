<?php
/**
 * voa_d_oa_auth_insert
 * PCauth登录/入库
 * Created by zhoutao.
 * Created Time: 2015/7/3  18:01
 */

class voa_d_oa_auth_insert extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.member_loginqrcode';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'auth_id';

		parent::__construct(null);
	}

}
