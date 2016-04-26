<?php
/**
 * voa_c_admincp_setting_servicetype_modify
 * 企业后台 - 系统设置 - 服务类型设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_setting_servicetype_modify extends voa_c_admincp_setting_servicetype_base {

	/** 是否开启app服务，启用后，用户将会选择是否启用微信企业号 */
	protected $_open_app_use = false;

	/** 当前系统环境内的 corp_id 值 */
	protected $_setting_corp_id = '';

	/** 当前系统环境内的 corp_secret 值 */
	protected $_setting_corp_secret = '';

	/** 当前系统环境内的 qrcode 值 */
	protected $_setting_qrcode = '';

	/** 是否是首次启用系统 */
	protected $_is_first_run = false;

	/** 用户提交的CorpID */
	protected $_post_corpid = null;
	/** 用户提交的Secret */
	protected $_post_secret = null;
	/** 用户提交的二维码图片地址 */
	protected $_post_qrcode = null;

	public function execute() {

		// 是否是首次使用系统
		$this->_is_first_run = empty($this->_setting['not_first_start']);

		if ($this->_is_post()) {

			$this->_post_qrcode = (string)$this->request->post('qrcode');
			$this->_post_qrcode = trim($this->_post_qrcode);

			// 提交更新
			$this->_submit_use_suite();
			return true;
		}

		$this->_setting_qrcode = !empty($this->_setting['qrcode']) ? $this->_setting['qrcode'] : '';
		$this->view->set('qrcode', rhtmlspecialchars($this->_setting_qrcode));

		$this->output('setting/servicetype');
		return true;
	}

	/**
	 * 提交更改
	 * @return boolean
	 */
	protected function _submit_use_suite() {

		// 默认只开启了微信企业号服务
		$ep_wxqy = voa_d_oa_common_setting::WXQY_AUTH;

		// 待更新的数据
		$update = array();
		$update['ep_wxqy'] = $ep_wxqy;

		if (!($result = $this->_check_qrcode()) || !empty($result['errcode'])) {
			return $this->_msg($result);
		}

		$update['qrcode'] = $this->_post_qrcode;

		// 标记为非首次进入系统
		$update['not_first_start'] = 1;

		// 更新企业本地
		$serv_setting = &service::factory('voa_s_oa_common_setting');
		if ($serv_setting->update_setting($update)) {

			// 更新系统缓存
			voa_h_cache::get_instance()->get('setting', 'oa', true);

			// 清除微信的 token 缓存
			$serv_weixin_setting = &service::factory('voa_s_oa_weixin_setting');
			$serv_weixin_setting->update(array('ws_value' => 0), array('ws_key' => 'token_expires'));
			$serv_weixin_setting->update(array('ws_value' => ''), array('ws_key' => 'access_token'));

			// 更新主站后台
			// 主站后台的api url
			$api_url = config::get(startup_env::get('app_name').'.rpc.cyadmin_api.api_url');
			// 调用主站后台
			$rpc_oa = new voa_client_oa(config::get(startup_env::get('app_name').'.rpc.client.auth_key'));
			$method = 'cyadmin_enterprise.update_profile';

			$args = array();
			$args['ep_id'] = $this->_setting['ep_id'];
			$args['ep_wxqy'] = $ep_wxqy;
			$args['ep_qrcode'] = $this->_post_qrcode;

			// 呼叫方法请求
			$result = $rpc_oa->call($api_url, $method, $args);
			if (!$result) {
				$this->_msg(array('errcode' => $rpc_oa->errno, 'errmsg' => $rpc_oa->errmsg, 'result' => array()));
			}
		}

		// 统一跳到应用列表
		$url = $this->cpurl($this->_module, 'application', 'list', $this->_module_plugin_id);
		$this->_msg($this->_output_result(), '服务类型设置操作完毕', $url, false);
		return true;
	}

	/**
	 * 检查二维码地址
	 * @return boolean
	 */
	protected function _check_qrcode() {

		$this->_post_qrcode = trim($this->_post_qrcode);
		if (empty($this->_post_qrcode)) {
			return $this->_output_result(1003, '请填写微信企业号的'.$this->_wechat_noun_list['qrcode_url']);
		}

		$parse_url = @parse_url($this->_post_qrcode);
		if (stripos($this->_post_qrcode, 'http') !== 0 || $parse_url === false) {
			return $this->_output_result(1004, '请正确填写微信企业号二维码图片地址');
		}
		if (isset($parse_url['query'])) {
			$arr = array();
			parse_str($parse_url['query'], $arr);
			if (isset($arr['url'])) {
				// 如果添加的是二维码的跳转链接，则解释出真实的二维码地址
				$this->_post_qrcode = trim($arr['url']);
			}
		}

		return $this->_output_result();
	}

	/**
	 * 输出结果集
	 * @param number $errcode 错误码
	 * @param string $errmsg 错误消息
	 * @param array $result 输出结果
	 * @return array
	 */
	protected function _output_result($errcode = 0, $errmsg = 'OK', $result = array()) {
		$data = array(
			'errcode' => $errcode,
			'errmsg' => $errmsg,
			'result' => $result
		);

		return $data;
	}

	/**
	 * 输出消息带判断
	 * @param array $r
	 * @param string $message
	 * @param string $url
	 */
	protected function _msg($r, $message = '', $url = '') {
		if ($this->request->post('action') == 'submit-json') {
			// 输出json
			if ($message) {
				$r['result']['message'] = $message;
			}
			if ($url) {
				$r['result']['url'] = $url;
			}
			echo rjson_encode($r);
		} else {
			if ($message) {
				$r['errmsg'] = $message;
			}
			$this->message($r['errcode'] > 0 ? 'error' : 'success', $r['errmsg'], $url, false);
		}
		exit;
	}










	/**
	 * 暂时废弃的方法
	 * by Deepseath 20141211
	 */
	public function __execute() {

		// 设置只允许使用微信企业号服务，而不允许使用app服务
		$this->_open_app_use = false;

		// 是否是首次使用系统
		$this->_is_first_run = empty($this->_setting['not_first_start']);

		// 注入微信名词
		$this->_wechat_lang_set();

		if ($this->_is_first_run) {
			// 如果是首次使用则假定默认使用“微信企业号服务”
			$this->_setting['ep_wxqy'] = 1;
		}

		if (empty($this->_setting['ep_id']) || !is_numeric($this->_setting['ep_id'])) {
			$this->message('error', '对不起，发生内部错误（无法找到企业ID信息），请联系客服人员解决');
		}

		$this->_setting_corp_id = !empty($this->_setting['corp_id']) ? $this->_setting['corp_id'] : '';
		$this->_setting_corp_secret = !empty($this->_setting['corp_secret']) ? $this->_setting['corp_secret'] : '';
		$this->_setting_qrcode = !empty($this->_setting['qrcode']) ? $this->_setting['qrcode'] : '';

		if ($this->_is_post()) {

			$this->_post_corpid = (string)$this->request->post('corp_id');
			$this->_post_secret = (string)$this->request->post('corp_secret');
			$this->_post_qrcode = (string)$this->request->post('qrcode');

			$this->_post_corpid = trim($this->_post_corpid);
			$this->_post_secret = trim($this->_post_secret);
			$this->_post_qrcode = trim($this->_post_qrcode);

			if ($this->_setting_corp_id && preg_match('/^[a-z_0-9]+$/i', $this->_setting_corp_id) && !$this->_is_first_run) {
				// 历史CorpID不为空，且 不是首次使用系统，则禁止再次修改CorpID —— 仍旧使用原来的CorpID
				//$this->_post_corpid = $this->_setting_corp_id;
			}

			// 处理各种动作
			switch ($this->request->post('action')) {
				case 'check_corpid_secret':// 检查 corpid 和 secret
					$result = $this->_check_corpid_secret();
					echo rjson_encode($result);
					exit;
				case 'check_addressbook_power':// 检查是否具有通讯录读取权限
					$result = $this->_check_wechat_power_by_addressbook();
					echo rjson_encode($result);
					exit;
				default:// 提交更新请求
					$this->_submit();
			}

			return true;
		}

		$this->view->set('corp_id', rhtmlspecialchars($this->_setting_corp_id));
		$this->view->set('corp_secret', rhtmlspecialchars($this->_setting_corp_secret));
		$this->view->set('qrcode', rhtmlspecialchars($this->_setting_qrcode));
		$this->view->set('ep_wxqy', empty($this->_setting['ep_wxqy']) ? 0 : $this->_setting['ep_wxqy']);
		$this->view->set('open_app_use', $this->_open_app_use);
		$this->view->set('is_first_run', $this->_is_first_run);

		$this->output('setting/servicetype');
	}

	/**
	 * 提交更改，暂时已废弃
	 * by Deepseath 20141211
	 */
	protected function _submit() {

		// by Deepseath@20141210
		$ep_wxqy = voa_d_oa_common_setting::WXQY_CLOSE;
		if ($this->_open_app_use) {
			// 开启了app应用服务
			$ep_wxqy = $this->request->post('ep_wxqy');
			$ep_wxqy = $ep_wxqy == voa_d_oa_common_setting::WXQY_MANUAL ? voa_d_oa_common_setting::WXQY_MANUAL : voa_d_oa_common_setting::WXQY_AUTH;
		} else {
			// 只开启了微信企业号服务
			$ep_wxqy = voa_d_oa_common_setting::WXQY_AUTH;
		}

		// 待更新的数据
		$update = array();
		$update['ep_wxqy'] = $ep_wxqy;

		if ($ep_wxqy) {
			// 如果设置启用微信企业号，则检查CorpID和Secret

			if (!($result = $this->_check_corpid_secret())) {
				return $this->_msg($result);
			}

			if (!($result = $this->_check_wechat_power_by_addressbook())) {
				return $this->_msg($result);
			}

			$update['corp_id'] = $this->_post_corpid;
			$update['corp_secret'] = $this->_post_secret;
			$update['qrcode'] = $this->_post_qrcode;
		}

		if ($this->_is_first_run) {
			// 如果是首次进入系统，则标记更新为非首次进入系统
			$update['not_first_start'] = 1;
		}

		// 更新企业本地
		$serv_setting = &service::factory('voa_s_oa_common_setting');
		if ($serv_setting->update_setting($update)) {

			// 更新系统缓存
			voa_h_cache::get_instance()->get('setting', 'oa', true);

			// 清除微信的 token 缓存
			$serv_weixin_setting = &service::factory('voa_s_oa_weixin_setting');
			$serv_weixin_setting->update(array('ws_value' => 0), array('ws_key' => 'token_expires'));
			$serv_weixin_setting->update(array('ws_value' => ''), array('ws_key' => 'access_token'));

			/* 更新主站后台 */
			// 主站后台的api url
			$api_url = config::get(startup_env::get('app_name').'.rpc.cyadmin_api.api_url');
			// 调用主站后台
			$rpc_oa = new voa_client_oa(config::get(startup_env::get('app_name').'.rpc.client.auth_key'));
			$method = 'cyadmin_enterprise.update_profile';

			$args = array();
			$args['ep_id'] = $this->_setting['ep_id'];
			$args['ep_wxqy'] = $ep_wxqy;
			$args['ep_wxcorpid'] = $this->_post_corpid;
			$args['ep_wxcorpsecret'] = $this->_post_secret;
			$args['ep_qrcode'] = $this->_post_qrcode;

			// 呼叫方法请求
			$result = $rpc_oa->call($api_url, $method, $args);
			if (!$result) {
				$this->_msg(array('errcode' => $rpc_oa->errno, 'errmsg' => $rpc_oa->errmsg, 'result' => array()));
			}
		}

		if ($this->_is_first_run) {
			// 如果是首次使用则跳转到通讯录同步页面
			$url = $this->cpurl('manage', 'member', 'impqywx');
		} else {
			// 非首次启用则回到本页
			$url = $this->cpurl($this->_module, $this->_operation, $this->_subop);
		}
		$this->_msg($this->_output_result(), '服务类型设置操作完毕', $url, false);
	}

	/**
	 * 检查CorpID、secret以及二维码图片url
	 * @return array
	 */
	protected function _check_corpid_secret() {

		if (empty($this->_post_corpid)) {
			return $this->_output_result(1001, '请填写微信企业号的 '.$this->_wechat_noun_list['corpid'].' 值');
		}

		if (empty($this->_post_secret)) {
			return $this->_output_result(1002, '请填写微信企业号的 '.$this->_wechat_noun_list['secret'].' 值');
		}

		$this->_post_qrcode = trim($this->_post_qrcode);
		if (empty($this->_post_qrcode)) {
			return $this->_output_result(1003, '请填写微信企业号的'.$this->_wechat_noun_list['qrcode_url']);
		}

		$parse_url = @parse_url($this->_post_qrcode);
		if (stripos($this->_post_qrcode, 'http') !== 0 || $parse_url === false) {
			return $this->_output_result(1004, '请正确填写微信企业号二维码图片地址');
		}
		if (isset($parse_url['query'])) {
			$arr = array();
			parse_str($parse_url['query'], $arr);
			if (isset($arr['url'])) {
				// 如果添加的是二维码的跳转链接，则解释出真实的二维码地址
				$this->_post_qrcode = trim($arr['url']);
			}
		}

		// 检查 CorpID 和 Secret 的合法可用性
		$wxqy_service = new voa_wxqy_service();
		if (!$wxqy_service->corpid_corpsecret_testing($this->_post_corpid, $this->_post_secret)) {
			return $this->_output_result(1005, '您填写的微信企业号 '.$this->_wechat_noun_list['corpid'].' 和 '.$this->_wechat_noun_list['secret'].' 不正确，请返回修改');
		}

		return $this->_output_result();
	}

	/**
	 * 通过尝试读取通讯录来判断用户是否开启了获取通讯录接口的权限
	 * @return array
	 */
	protected function _check_wechat_power_by_addressbook() {

		// 无权使用通讯录接口权限的错误代码标记
		$errcodes = array('60011');
		$wxqy_addressbook = &voa_wxqy_addressbook::instance();
		$result = array();
		if (!$wxqy_addressbook->addressbook_power_testing($this->_post_corpid, $this->_post_secret)) {
			if (in_array($wxqy_addressbook->errcode, $errcodes)) {
				// 属于无权的错误代码，单独来提醒用户
				return $this->_output_result('1006-'.$wxqy_addressbook->errcode, '对不起，您可能没有正确“开启通讯录管理权限”，请按照操作说明检查一下。');
			} else {
				return $this->_output_result('1006-'.$wxqy_addressbook->errcode, $wxqy_addressbook->errmsg);
			}
		}

		return $this->_output_result();
	}

}
