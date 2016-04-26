<?php
/**
 * voa_d_oa_jobtrain_article
 * Create By wowxavi
 * $Author$
 * $Id$
 */

class voa_d_oa_jobtrain_article extends voa_d_abstruct {

	const TYPE_ARTICLE = 0;
	const TYPE_AUDIO = 1;
	const TYPE_VIDEO = 2;

	public static $TYPES = array('文章内容', '音图内容', '视频内容');

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.jobtrain_article';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}
	
}