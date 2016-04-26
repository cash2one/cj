<?php
/**
 * voa_server_weopen_event
 * 微信开放平台事件消息接口服务基类
 *
 * $Author$
 * $Id$
 */


class voa_server_weopen_event {
	// service
	protected $_weserv;

	/**
	 * __construct
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct() {

		$this->_weserv = voa_weixinopen_service::instance();
	}

	// ticket 事件
	public function ticket($args) {

		$appid = $this->_weserv->msg['app_id'];
		$ticket = $this->_weserv->msg['component_verify_ticket'];
		// 更新 ticket
		$serv = new voa_s_uc_weopen();
		$serv->update_by_conds(array('appid' => $appid), array('ticket' => $ticket));

		return true;
	}

	// 取消授权
	public function unauthorized($args) {

		// appid
		$appid = $this->_weserv->msg['app_id'];
		// 已授权的 appid
		$auth_appid = $this->_weserv->msg['authorizer_appid'];

		// 读取记录
		$serv = &service::factory('voa_s_uc_enterprise_weopen');
		$weopen = $serv->get_by_conds(array('appid' => $auth_appid));
		if (empty($weopen)) {
			return true;
		}

		// 更新记录状态
		$serv->update($weopen['ewid'], array('state' => voa_d_uc_enterprise_weopen::STATE_UNAUTH));

		$sig = voa_h_func::sig_create($args, startup_env::get('timestamp'));
		$url = 'https://'.$weopen['domain'].'/api/weopen/post/unauth/?sig='.$sig.'&ts='.startup_env::get('timestamp');
		$data = array();
		$pdata = array('appid' => $appid);
		if (!voa_h_func::get_json_by_post($data, $url, $pdata)) {
			logger::error($url."\n".var_exoprt($args));
			return 'success';
		}

		return true;
	}

}
