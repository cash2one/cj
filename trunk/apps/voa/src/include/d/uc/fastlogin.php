<?php
/**
 * voa_d_uc_fastlogin
 * 快速登录关联表
 * Created by zhoutao.
 * Created Time: 2015/6/18  10:56
 */

class voa_d_uc_fastlogin extends voa_d_abstruct {

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_uc.fastlogin';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'fast_id';
		// 字段前缀
		$this->_prefield = '';

		parent::__construct();
	}
}
