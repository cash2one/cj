<?php
/**
 * voa_d_oa_showroom_article
 * 活动分类
 * Create By linshiling
 * $Author$
 * $Id$
 */

class voa_d_oa_campaign_type extends voa_d_abstruct {
	
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.campaign_type';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}
	
	//获取分类voa_d_oa_campaign_type::get_type(5);
	public static function get_type($typeid = 0)
	{
		static $type;
		if(!$type) {
			$t = new voa_d_oa_campaign_type();
			$list = $t->list_all();
			$type = array();
			if(!$list) $list = array();
			foreach ($list as $l)
			{
				$type[$l['id']] = $l['title'];
			}
		}
		return isset($type[$typeid]) ? $type[$typeid] : $type;
	}
}

