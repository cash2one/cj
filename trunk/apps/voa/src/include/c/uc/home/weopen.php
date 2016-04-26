<?php
/**
 * voa_c_uc_home_weopen
 * uc/weopen

 * $Author$
 * $Id$
 */

class voa_c_uc_home_weopen extends voa_c_uc_home_base {

	public function execute() {

		$ac = (string)$this->request->get('ac');
		$acs = array('toopen', 'fromopen');
		$ac = empty($ac) || !in_array($ac, $acs) ? 'toopen' : 'fromopen';

		$func = '_'.$ac;
		$this->$func();

		return true;
	}

	protected function _toopen() {

		$domain = (string)$this->request->get('domain');
		$appid = (string)$this->request->get('appid');
		$path = (string)$this->request->get('path');
		$path = urldecode($path);

		$serv_weopen = &service::factory('voa_s_uc_weopen');
		$weo = $serv_weopen->get_by_conds(array('appid' => $appid));
		// 如果第三方应用或者企业信息缺失
		if (empty($weo)) {
			exit('auth fail.');
			return false;
		}

		$serv_ew = &service::factory('voa_s_uc_enterprise_weopen');
		// 先读取记录
		$weopen = $serv_ew->get_by_conds(array('domain' => $domain, 'appid' => $appid));
		if (empty($weopen)) {
			$serv_ep = &service::factory('voa_s_cyadmin_enterprise_profile');
			$ep = $serv_ep->fetch_by_domain($domain);

			if (empty($ep)) {
				exit('auth fail.');
				return false;
			}

			$weopen = $serv_ew->insert(array(
				'ep_id' => $ep['ep_id'],
				'domain' => $domain,
				'appid' => $appid
			));
		}

		// sig
		$data = array(
			'domain' => $domain,
			'appid' => $appid,
			'path' => $path
		);
		$data['sig'] = voa_h_func::sig_create($data);
		$data['ts'] = startup_env::get('timestmap');

		// 应用套件授权地址
		$serv = voa_weixinopen_service::instance();
		$url = 'https://uc.vchangyi.com/uc/home/weopen?ac=fromopen&'.http_build_query($data);
		$url = $serv->get_oauth_url($weo['appid'], urlencode($url));

		$this->view->set('url', $url);
		$this->output('uc/redirect');

		return true;
	}

	protected function _fromopen() {

		$path = (string)$this->request->get('path');
		$path = urldecode($path);
		$params = array(
			'domain' => (string)$this->request->get('domain'),
			'appid' => $this->request->get('appid'),
			'path' => $path,
			'sig' => (string)$this->request->get('sig'),
			'ts' => $this->request->get('ts')
		);
		if (!voa_h_func::sig_check($params)) {
			exit('auth fail.');
		}

		$params['auth_code'] = (string)$this->request->get('auth_code');
		$params['expires_in'] = (int)$this->request->get('expires_in');
		$params['appid'] = $this->request->get('appid');
		unset($params['sig'], $params['ts'], $params['domain']);
		$params['sig'] = voa_h_func::sig_create($params);
		$params['ts'] = startup_env::get('timestamp');

		// 读取授权记录
		$domain = (int)$this->request->get('domain');
		$path = str_replace(':', '/', $path);

		$this->response->set_redirect("https://{$domain}/{$path}?".http_build_query($params));

		return true;
	}

}
