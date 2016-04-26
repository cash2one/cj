<?php
/**
 * voa_d_uc_fastinformation
 * 快速登录微信返回的信息表
 * Created by zhoutao.
 * Created Time: 2015/6/18  10:57
 */

class voa_d_uc_fastinformation extends voa_d_abstruct {

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_uc.fastinformation';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'fa_id';
		// 字段前缀
		$this->_prefield = '';

		parent::__construct();
	}
}
