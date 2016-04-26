<?php
/**
 * voa_d_oa_talk_viewer
 * 访客信息
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_talk_viewer extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.talk_viewer';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'tv_uid';

		parent::__construct(null);
	}

}
