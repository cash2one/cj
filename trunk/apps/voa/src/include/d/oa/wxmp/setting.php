<?php
/**
 * voa_d_oa_wxmp_setting
 * 微信公众号配置
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_wxmp_setting extends voa_d_abstruct {
	// 数据类型: 数组
	const TYPE_ARRAY = 1;
	// 数据类型: 字串
	const TYPE_NORMAL = 0;

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.wxmp_setting';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'key';
		// 字段前缀
		$this->_prefield = '';

		parent::__construct();
	}
}

