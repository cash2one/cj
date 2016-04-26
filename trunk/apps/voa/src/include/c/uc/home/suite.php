<?php
/**
 * voa_c_uc_home_suite
 * uc/suite
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_home_suite extends voa_c_uc_home_base {

	public function execute() {

		$ac = (string)$this->request->get('ac');
		$acs = array('toqy', 'fromqy');
		$ac = empty($ac) || !is_array($acs) ? 'toqy' : $ac;

		$func = '_'.$ac;
		$this->$func();

		return true;
	}

	protected function _toqy() {

		$domain = (string)$this->request->get('domain');
		$suiteid = (string)$this->request->get('suiteid');
		$appids = (string)$this->request->get('appids');

		// 如果 $appids 为空, 则授权所有的应用
		$noagent = config::get(startup_env::get('app_name').'.suite.noagent');
		if (empty($appids) && !in_array($suiteid, $noagent)) {
			$plgin2appid = config::get(startup_env::get('app_name').'.suite.plgin2appid');
			$appids = empty($plgin2appid[$suiteid]) ? '' : implode(',', $plgin2appid[$suiteid]);
		}

		// 应用套件授权地址
		$serv = voa_wxqysuite_service::instance();
		// 设置授权配置
		if (!empty($appids)) {
			$serv->set_auth_session($suiteid, $appids);
		}

		// 生成 sig
		$data = array(
			'domain' => $domain,
			'suiteid' => $suiteid,
			'appids' => $appids
		);

		// 获取授权地址
		$params = array(
			'domain' => $domain,
			'suiteid' => $suiteid,
			'appids' => $appids,
			'sig' => voa_h_func::sig_create($data),
			'ts' => startup_env::get('timestamp')
		);
		$url = config::get('voa.uc_url').'uc/home/suite?ac=fromqy';
		if (!$url = $serv->get_oauth_url($suiteid, urlencode($url), implode('|', $params))) {
			$this->_error_message('授权操作失败, 请稍后重新尝试');
		}

		$this->view->set('url', $url);
		$this->output('uc/redirect');

		return true;
	}

	protected function _fromqy() {

		$state = (string)$this->request->get('state');
		list($domain, $suiteid, $appids, $sig, $ts) = explode('|', $state);
		$auth_code = (string)$this->request->get('auth_code');

		// 验证 sig
		$params = array(
			'domain' => $domain,
			'suiteid' => $suiteid,
			'appids' => $appids,
			'ts' => $ts,
			'sig' => $sig
		);
		// 官网url
		$cy_site = config::get('voa.main_url');
		if (!voa_h_func::sig_check($params)) {
			$this->response->set_redirect($cy_site);
			return true;
		}

		// 如果域名为空, 则
		if (empty($domain)) {
			if (!$this->_new_enterprise($auth_code, $suiteid, $appids)) {
				$this->response->set_redirect("{$cy_site}topic/wechat");
			}

			return true;
		}

		$scheme = config::get('voa.oa_http_scheme');
		$this->response->set_redirect($scheme."{$domain}/admincp/setting/application/list/?auth_code=".$auth_code.'&suiteid='.$suiteid.'&appids='.$appids);

		return true;
	}

	// 新企业
	protected function _new_enterprise($auth_code, $suiteid, $appids) {

		// 获取授权信息
		$serv = voa_wxqysuite_service::instance();
		$authdata = array();
		if (!$serv->get_permanent_code($authdata, $auth_code, $suiteid, false)) {
			return false;
		}

		$corpid = $authdata['auth_corp_info']['corpid'];
		// 授权套件信息入库
		$serv_pa = &service::factory('voa_s_uc_preauth');
		if ($preauth = $serv_pa->get($corpid)) {
			$serv_pa->update($corpid, array(
				'suiteid' => $suiteid,
				'authdata' => serialize($authdata)
			));
		} else {
			$serv_pa->insert(array(
				'corpid' => $corpid,
				'suiteid' => $suiteid,
				'authdata' => serialize($authdata)
			));
		}

		// 判断授权站点是否存在
		$serv_ep = &service::factory('voa_s_cyadmin_enterprise_profile');
		if (!($enterprise = $serv_ep->fetch_by_corpid($corpid))) {
			// 转向补齐信息页面
			$post = array(
				'corpid' => $corpid,
				'appids' => $appids,
				'suiteid' => $suiteid,
				'authcode' => substr($auth_code, -16)
			);
			$post['sig'] = voa_h_func::sig_create($post);

			//发送微信企业信息，填充开通站点表单
			$post['corp_name'] = empty($authdata['auth_corp_info']['corp_name']) ? '' : $authdata['auth_corp_info']['corp_name'];
			$post['user_max'] = empty($authdata['auth_corp_info']['corp_user_max']) ? '' : $authdata['auth_corp_info']['corp_user_max'];
			$post['email'] = empty($authdata['auth_user_info']['email']) ? '' : $authdata['auth_user_info']['email'];
			$post['mobile'] = empty($authdata['auth_user_info']['mobile']) ? '' : $authdata['auth_user_info']['mobile'];


			$post['ts'] = startup_env::get('timestamp');

			// 转向修改信息的地址
			$this->response->set_redirect(config::get('voa.main_url')."member/install?".http_build_query($post));
			return true;
		}

		$scheme = config::get('voa.oa_http_scheme');
		// 调用接口, 写用户库的 suite 信息
		$post = array(
			'suiteid' => $suiteid,
			'domain' => $enterprise['ep_domain'],
			'auth_code' => substr($auth_code, -16)
		);
		$post['sig'] = voa_h_func::sig_create($post);
		$post['ts'] = startup_env::get('timestamp');
		$post['authdata'] = serialize($authdata);
		$url = $scheme.$enterprise['ep_domain'].'/api/suite/post/update';
		$data = array();
		voa_h_func::get_json_by_post($data, $url, $post);

		$this->response->set_redirect($scheme."{$enterprise['ep_domain']}/admincp/setting/application/bind/?suiteid={$suiteid}&multi=1&appids={$appids}");

		return true;
	}

}
