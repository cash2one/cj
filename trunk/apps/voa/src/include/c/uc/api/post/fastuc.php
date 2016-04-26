<?php

/**
 * voa_c_uc_home_fastuc
 * UC用户授权登录
 * https://uc.dev.vchangyi.com/uc/api/post/fastuc
 * $Author$
 * $Id$
 */
class voa_c_uc_api_post_fastuc extends voa_c_uc_api_base {

	/** 加密字符串，必须与 main_frontend_c_member_fastin 类定义一致 */
	private $__state_secret_key = '';

	public function execute() {

		$__service_corp_secret = config::get('voa.uc.service_corp_secret');
		$__service_corp_id = config::get('voa.uc.service_corp_id');

		$token_data = array(
			'corpid' => $__service_corp_id,//企业号（提供商）的corpid
			'provider_secret' => $__service_corp_secret,//提供商的secret，在提供商管理页面可见
		);

		//获取应用提供商凭证
		$urla = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_provider_token';
		$data = array();
		voa_h_func::get_json_by_post($data, $urla, json_encode($token_data));
		if (isset($data['errcode']) && 0 < $data['errcode']) {
			logger::error(var_export($data, true));
			$this->_set_errcode($data['errcode'] . ':' . $data['errmsg']);
			return false;
		}

		//获取企业号管理员登录信息
		$urlb = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_login_info?provider_access_token=' . $data['provider_access_token'];
		$post_data = array(
			'auth_code' => $this->_params['auth_code'],//授权企业号管理员登录产生的code
		);
		$userdata = array();
		voa_h_func::get_json_by_post($userdata, $urlb, json_encode($post_data));
		if (isset($userdata['errcode']) && 0 < $userdata['errcode']) {
			logger::error(var_export($userdata, true));
			$this->_set_errcode($userdata['errcode'] . ':' . $userdata['errmsg']);
			return false;
		}

		// 返回数据
		if (empty($userdata)) {
			// 如果微信端返回报错代码
			logger::error("网络错误:微信端没有返回数据,报错来源:voa_c_uc_api_post_fastuc" . var_export($this->_params, true));
			return false;
		}

		// 判断接口是否出错
		if (empty($userdata['corp_info']) || empty($userdata['corp_info']['corpid'])) {
			logger::error('未读取到绑定的企业号, 请稍后重试');
			return false;
		}

		// 查询corpid关联的企业
		$domains = $this->_get_enterprise_domain($userdata['corp_info']['corpid']);
		if (empty($domains)) {
			logger::error('查询corpid关联企业失败:' . print_r($userdata, true));
		}
		$data = array(
			'email' => !empty($userdata['user_info']['email']) ? $userdata['user_info']['email'] : '',
			'mobile' => !empty($userdata['user_info']['mobile']) ? $userdata['user_info']['mobile'] : '',
			'corpid' => $userdata['corp_info']['corpid'],
			'domains' => $domains
		);
		$this->result = $data;
		return true;

	}

	/**
	 * 获取企业号域名
	 * @param $corpid
	 * @return array
	 */
	protected function _get_enterprise_domain($corpid) {

		$serv_profile = &service::factory('voa_s_cyadmin_enterprise_profile');
		$list = $serv_profile->fetch_by_conditions(array('ep_wxcorpid' => $corpid));

		if (!empty($list) && is_array($list)) {
			return array_column($list, 'ep_name', 'ep_domain');
		}

		return array();
	}
}
