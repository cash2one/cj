<?php
/**
 * voa_uda_frontend_news_abstract
 * 统一数据访问/新闻公告/基类
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_news_abstract extends voa_uda_frontend_base {
	// 商品配置
	protected $_sets = array();
	// 插件名称
	protected $_ptname = array();
	// 分类
	protected  $_categories = array();
	//状态
	protected $_status = array(
		voa_d_oa_news::IS_DRAFT => '草稿',
		voa_d_oa_news::IS_PUBLISH => '已发布'
	);
	//消息保密
	protected $_secret = array(
		voa_d_oa_news::IS_CLOSE => '关闭',
		voa_d_oa_news::IS_OPEN => '开启 '
	);
	public function __construct($ptname = null) {

		parent::__construct($ptname);
		// 取应用配置
		$this->_sets = voa_h_cache::get_instance()->get('plugin.news.setting', 'oa');
		$this->_ptname = $ptname;
		$s_category = new voa_s_oa_news_category();
		$this->_categories = $s_category->list_all();
	}

}
