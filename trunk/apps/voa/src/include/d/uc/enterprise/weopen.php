<?php
/**
 * voa_d_uc_enterprise_weopen
 * 微信开放平台企业和服务信息关联表
 *
 * $Author$
 * $Id$
 */

class voa_d_uc_enterprise_weopen extends voa_d_abstruct {
	// 未授权
	const STATE_CREATE = 0;
	// 已授权
	const STATE_AUTH = 1;
	// 已取消授权
	const STATE_UNAUTH = 2;

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_uc.enterprise_weopen';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'ewid';

		parent::__construct();
	}

}
