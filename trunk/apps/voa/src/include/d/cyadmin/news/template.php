<?php
/**
 * voa_d_cyadmin_enterprise_app
 * 畅移后台/企业应用
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_d_cyadmin_news_template extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.news_templates';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'ne_id';
		parent::__construct();
	}

}
