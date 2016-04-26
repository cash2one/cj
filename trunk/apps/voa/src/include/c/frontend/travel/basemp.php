<?php
/**
 * 商品基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_basemp extends voa_c_frontend_travel_base {

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->session->set('logintype', 'mp');

		// 输出 saleuid
		$saleuid = (int)$this->request->get('saleuid');
		if (empty($saleuid)) {
			$saleuid = (int)$this->session->get('saleuid');
		} else {
			$this->session->set('saleuid', $saleuid);
		}

		// 如果 $saleuid 为空
		if (empty($saleuid)) {
			$saleuid = (int)$this->_user['saleid'];
			$this->session->set('saleuid', $saleuid);
		}

		// 根据销售 uid, 读取用户
		/**if (0 < $saleuid) {
			$serv_m = &service::factory('voa_s_oa_member');
			if (!$mem = $serv_m->fetch_by_uid($saleuid)) {
				$this->session->remove('saleuid');
				$saleuid = 0;
			}
		}*/

		startup_env::set('saleuid', $saleuid);
		$this->view->set('saleuid', $saleuid);

		return true;
	}

	protected function _init_user() {

		// 如果是授权回调地址
		if (!empty($_GET['code']) && isset($_GET['state'])) {
			return false;
		}

		$uda = new voa_uda_frontend_mpuser_login();
		$uda->set_session($this->session);
		$uda->set_type(voa_uda_frontend_mpuser_login::TYPE_COOKIE);
		$result = array();
		if (!$uda->execute(array(), $result)) {
			return false;
		}

		// 登录信息相关
		$this->_set_user_env_mp($result['member']);

		return true;
	}

	protected function _set_user_env_mp($user) {

		$this->_user = $this->_filter_user_secret_field($user, array('password', 'salt'));
		$this->_openid = $this->_user['openid'];

		// 登陆成功后, 清除 openid
		$this->session->remove('openid');
		startup_env::set('wbs_uid', $user['mpuid']);
		startup_env::set('wbs_username', $user['username']);
		startup_env::set('web_access_token', isset($user['web_access_token']) ? $user['web_access_token'] : '');
		startup_env::set('web_token_expires', isset($user['web_token_expires']) ? $user['web_token_expires'] : 0);

		// 推入用户信息数组
		voa_h_user::push($user);
	}

	// 自动登陆
	protected function _auto_login() {

		// 判断是否已经登录
		if (!empty($this->_user)) {
			return true;
		}

		// 代码
		$code = $this->request->get('code');

		// 如果 code 不为空
		if (!empty($code) || !preg_match("/vchangyi\.(net|com)$/i", $_SERVER['HTTP_HOST'])) {
			return $this->_auto_login_mp();
		}

		//$serv = voa_weixinopen_service::instance();
		$serv = voa_weixin_service::instance();
		header('Location: '.$serv->oauth_url_base(voa_h_func::get_auth_back_url($logintype), startup_env::get('open_appid')));
		$this->response->stop(); exit;

		return false;
	}

	// 自动登录服务号
	protected function _auto_login_mp() {

		if (!empty($this->_user['openid']) && startup_env::get('timestamp') < $this->_user['web_token_expires']) {
			return true;
		}

		// 获取 openid
		//$this->_openid = 'deepseath';
		$wechatid = $this->_get_mp_openid();
		if (empty($wechatid)) {
			logger::error('mp openid is empty.');
			return false;
		}

		// 登录
		$uda_login = new voa_uda_frontend_mpuser_login();
		$uda_login->set_session($this->session);
		$result = array();
		$member = array();
		$mp_serv = voa_weixin_service::instance();
		// 如果登录不成功
		if (!$uda_login->execute(array('openid' => $wechatid), $result)) {
			// mp openid 入库, 新建用户
			try {
				$uda_mem_add = new voa_uda_frontend_mpuser_add();
				list($password, $salt) = voa_h_func::generate_password(md5(random(16)), random(6));
				$uda_mem_add->execute(array(
					'openid' => $wechatid,
					'salt' => $salt,
					'password' => $password,
					'web_access_token' => $mp_serv->web_token->access_token,
					'web_token_expires' => $mp_serv->web_token->expires_in + startup_env::get('timestamp')
				), $member);
				// 登录
				$uda_login->execute(array('openid' => $wechatid), $result);
			} catch (Exception $e) {
				logger::error($wechatid.' is exists.');
				return false;
			}
		} else {
			// 更新 access_token 和 expires_in
			$uda_mem_up = new voa_uda_frontend_mpuser_update();
			$upres = array();
			$uda_mem_up->execute(array(
				'mpuid' => $result['member']['mpuid'],
				'web_access_token' => $mp_serv->web_token->access_token,
				'web_token_expires' => $mp_serv->web_token->expires_in + startup_env::get('timestamp')
			), $upres);
			$result['member']['web_access_token'] = $mp_serv->web_token->access_token;
			$result['member']['web_token_expires'] = $mp_serv->web_token->expires_in + startup_env::get('timestamp');
		}

		$member = $result['member'];
		// 设置用户环境相关
		$this->_set_user_env_mp($member);

		return true;
	}

	// 获取当前用户的 openid
	protected function _get_mp_openid() {

		if (false !== $this->_openid) {
			return $this->_openid;
		}

		// 从网页接口获取 openid
		$mp_serv = voa_weixin_service::instance();
		$mp_serv->get_web_openid($this->_openid);

		// 如果还是为空, 则
		if (empty($this->_openid)) {
			$this->_openid = '';
		}

		return $this->_openid;
	}

}
