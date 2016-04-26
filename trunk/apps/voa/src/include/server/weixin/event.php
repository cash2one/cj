<?php
/**
 * voa_server_weixin_event
 * 微信事件消息接口服务基类
 *
 * $Author$
 * $Id$
 */


class voa_server_weixin_event {
	protected $_wxserv;

	/**
	 * __construct
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct() {
		$this->_wxserv = voa_weixin_service::instance();
		$this->_setting = voa_h_cache::get_instance()->get('setting', 'oa');
	}

	/** 关注事件 */
	public function subscribe($args) {

		return true;
	}

	/** 扫码消息 */
	public function scan($args) {
		//XXX 可能需要修改
		$wxscreen = new voa_weixin_wxscreen();
		$ww_id = $wxscreen->get_wwid($this->_wxserv, $this->_wxserv->msg['event_key']);
		if ($ww_id) {
			/** 尝试二维码上墙签到 */
			$this->_wxserv->response_text(voa_h_wxwall::wxwall_success_login_msg());
			return true;
		}

		/** 签到 */
		$sign = new voa_sign_handle();
		$sign->sign($args['user'], voa_sign_handle::TYPE_QRCODE);
		return true;
	}

	/** 地理位置事件 */
	public function location($args) {
		$user = $args['user'];
		/** 返回位置的消息模板 */

		/** 地理位置信息入库 */
		$serv = &service::factory('voa_s_oa_weixin_location', array('pluginid' => 0));
		$serv->insert(array(
			'm_uid' => $user['m_uid'],
			'm_username' => $user['m_username'],
			'wl_latitude' => $this->_wxserv->msg['latitude'],
			'wl_longitude' => $this->_wxserv->msg['longitude'],
			'wl_precision' => $this->_wxserv->msg['precision'],
			'wl_ip' => controller_request::get_instance()->get_client_ip()
		));

		return true;
	}

	/** 菜单点击事件 */
	public function click($args) {
		/** 读取插件信息 */
		$serv = &service::factory('voa_s_oa_common_plugin', array('pluginid' => 0));
		$plugin = $serv->fetch_by_identifier($this->_wxserv->msg['event_key']);
		/** 如果插件不存在, 则 */
		if (empty($plugin)) {
			logger::error("plugin is not exists.");
			$this->_wxserv->response_text('无效的关键字:'.$this->_wxserv->msg['event_key']);
			return false;
		}

		/** 返回该菜单的链接 */
		$scheme = config::get('voa.oa_http_scheme');
		$url = $this->_wxserv->oauth_url_base($scheme.$this->_setting['domain'].'/'.$plugin['cp_url']);
		$this->_wxserv->response_text("<a href='{$url}'>".$plugin['cp_name']."</a>");
		return true;
	}
}
