<?php
/**
 * voa_d_oa_talk_lastview
 * 最后查看聊天记录信息的时间
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_talk_lastview extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.talk_lastview';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'cs_id';

		parent::__construct(null);
	}

}
