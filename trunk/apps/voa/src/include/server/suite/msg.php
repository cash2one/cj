<?php
/**
 * voa_server_suite_msg
 * 微信企业套件普通消息处理
 *
 * $Author$
 * $Id$
 */


class voa_server_suite_msg {
	/** 微信 service 实例 */
	protected $_wxserv;
	/** 取应用信息 */
	protected $_plugin = array();

	/**
	 * __construct
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct() {

		$this->_wxserv = voa_wxqysuite_service::instance();
	}

    /** ticket 消息 */
    public function suite_ticket($args) {

    	// 更新 ticket
    	$serv_ticket = &service::factory('voa_s_uc_suite');
    	$serv_ticket->update(array('su_ticket' => $args['suite_ticket']), "`su_suite_id`='{$args['suite_id']}'");

    	return 'success';
    }

    /** 改变授权消息 */
    public function change_auth($args) {

    	$this->_auth($args);

    	return 'success';
    }

    /** 取消授权消息 */
    public function cancel_auth($args) {

    	$this->_auth($args);

    	return 'success';
    }

    protected function _auth($args) {

    	$corpid = $args['auth_corp_id'];
    	$serv_ep = &service::factory('voa_s_cyadmin_enterprise_profile');
    	if (!$eps = $serv_ep->fetch_by_conditions(array('ep_wxcorpid' => $corpid), 0, 10)) {
    		logger::error(var_export($_GET, true)."\n".$xml);
    		return 'success';
    	}

    	$ep = array_pop($eps);
    	$sig = voa_h_func::sig_create($args, startup_env::get('timestamp'));
    	$scheme = config::get('voa.oa_http_scheme');
    	$url = $scheme.$ep['ep_domain'].'/api/wxqysuite/post/auth/?sig='.$sig.'&ts='.startup_env::get('timestamp');
    	$data = array();
    	if (!voa_h_func::get_json_by_post($data, $url, $args)) {
    		logger::error($url."\n".var_export($args, true));
    		return 'success';
    	}

    	return true;
    }

}

