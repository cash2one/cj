<?php
/**
 * voa_d_oa_talk_wechat
 * 聊天记录信息
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_talk_wechat extends voa_d_abstruct {
	// 访客发言
	const TYPE_VIEWER = 1;
	// 销售发言
	const TYPE_SALES = 2;

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.talk_wechat';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'tw_id';

		parent::__construct(null);
	}

}
