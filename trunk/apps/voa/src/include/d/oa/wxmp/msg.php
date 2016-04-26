<?php
/**
 * voa_d_oa_wxmp_msg
 * 微信公众号消息
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_wxmp_msg extends voa_d_abstruct {

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.wxmp_msg';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'wmid';
		// 字段前缀
		$this->_prefield = '';

		parent::__construct();
	}
}

