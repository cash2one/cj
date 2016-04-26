<?php
/**
 * voa_uda_frontend_talk_abstract
 *
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_talk_abstract extends voa_uda_frontend_base {
	/** 应用的唯一标识名 */
	protected $_plugin_identifier = 'travel';
	/** 应用设置信息 */
	protected $_plugin_setting = array();
	/** 站点全局设置 */
	protected $_setting = array();

	public function __construct($ptname = array()) {

		parent::__construct();

		// 当前应用的设置信息
		$this->_plugin_setting = voa_h_cache::get_instance()->get('plugin.'.$this->_plugin_identifier.'.setting', 'oa');

		// 站点全局配置
		$this->_setting = voa_h_cache::get_instance()->get('setting', 'oa');
	}

	/**
	 * 获取工单详情页的微信企业号授权链接
	 * @param string $url (引用结果)链接字符串
	 * @param number $goodsid 商品id
	 * @param number $tv_id 客户id
	 * @return boolean
	 */
	public function get_view_url(&$url, $goodsid, $tv_id) {

		// 站点使用的传输协议，自全局配置读取
		$url = config::get(startup_env::get('app_name').'.oa_http_scheme');
		// 站点域名
		$url .= $this->_setting['domain'].'/frontend/talk/index?';
		// 应用ID
		$url .= 'pluginid='.$this->_plugin_setting['pluginid'];
		// 执行的动作
		$url .= '&__view=chat_sale&__params[goods_id]='.$goodsid.'&__params[tv_id]='.$tv_id;

		// 生成链接
		$url = voa_wxqy_service::instance()->oauth_url($url);

		return true;
	}

}
