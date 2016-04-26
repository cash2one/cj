<?php
/**
 * 巡店主题表
 * $Author$
 * $Id$
 */

class voa_d_oa_inspect extends voa_d_abstruct {
	// 待巡
	const TYPE_WAITING = 1;
	// 进行中
	const TYPE_DOING = 2;
	// 已巡
	const TYPE_DONE = 3;

	// 待巡
	const TYPE_WAITING_TEXT = '待巡';
	// 进行中
	const TYPE_DOING_TEXT = '进行中';
	// 已巡
	const TYPE_DONE_TEXT = '已巡' ;

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.inspect';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'ins_id';
		// 字段前缀
		$this->_prefield = 'ins_';

		parent::__construct();
	}
}
