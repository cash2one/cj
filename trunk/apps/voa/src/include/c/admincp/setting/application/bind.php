<?php
/**
 * bind.php
 * 绑定微信企业号应用
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_setting_application_bind extends voa_c_admincp_setting_application_base {

	/** 本地应用信息 */
	protected $_plugin = array();

	public function execute() {

		// 旧的用户，暂时禁止使用套件方式启用应用
		if (!$this->_is_suite_auth_site) {
			$this->message('error', '抱歉，应用套件服务目前正在内部测试，暂不支持应用绑定及授权，请稍候再试。');
		}

		// 套件ID
		$suiteid = (string)$this->request->get('suiteid');
		// 待安装的应用ID
		$appids = (string)$this->request->get('appids');

		// 当前请求安装的应用ID(由自身发出)
		$appid = (string)$this->request->get('appid');

		// 获取套件应用列表
		$suite_plugin_list = config::get(startup_env::get('app_name').'.suite.plgin2appid');
		if (!isset($suite_plugin_list[$suiteid])) {
			$this->message('error', '套件ID错误');
		}

		// 载入应用套件数据类
		$s_suite = new voa_s_oa_suite();
		// 读取套件ID
		$suite = $s_suite->fetch_by_suiteid($suiteid);
		$auth_app_list = array();
		if (empty($suite) || $this->_from_www() || !($auth_app_list = @unserialize($suite['authinfo']))) {
			$corpid = (string)$this->request->get('corpid');
			if (!$this->_init_suite($auth_app_list, $corpid, $suiteid)) {
				$this->message('error', '套件授权信息读取错误');
			}
		}

		// by zhuxun, 如果是消息服务, 则构造一个 agent 信息
		$noagent = config::get(startup_env::get('app_name').'.suite.noagent');
		if (in_array($suiteid, $noagent)) {
			$auth_app_list['auth_info']['agent'] = array(
				array('agentid' => 9999, 'appid' => 1)
			);
		}
		// end.

		// 套件应用列表
		$suite_application_list = $suite_plugin_list[$suiteid];
		// 已授权应用列表
		$auth_application_list = array();
		// agentid与appid对应关系
		$agentid_list = array();
		foreach ($auth_app_list['auth_info']['agent'] as $_app) {
			$auth_application_list[$_app['appid']] = $_app['agentid'];
			$agentid_list[$_app['agentid']] = $_app['appid'];
		}

		if (!$appid && !$appids) {
			// 列出所有本地应用以过滤需要安装的应用
			$s_plugin = new voa_s_oa_common_plugin();
			$plugins = $s_plugin->fetch_all();

			$_appids = array();
			$_clear_agentids = array();
			foreach ($plugins as $_p) {
				if (!isset($suite_application_list[$_p['cp_identifier']])) {
					// 忽略非当前套件的本地应用
					continue;
				}
				if ($_p['cp_agentid']) {
					// 已安装的
					if (!isset($agentid_list[$_p['cp_agentid']])) {
						// 不存在于已授权的列表内，则表明可能通知出错，本地标记移除
						$_clear_agentids[] = $_p['cp_agentid'];
					}
				} else {
					// 未安装的
					$__appid = $suite_application_list[$_p['cp_identifier']];
					if (!isset($auth_application_list[$__appid])) {
						// 未授权则忽略
						continue;
					}
					if (!isset($agentid_list[$_p['cp_agentid']])) {
						// 微信端也未安装
						$_appids[] = $__appid;
					}
				}
			}
			if (empty($_clear_agentids)) {
				// 需要移除应用安装标记的
				$s_plugin->clear_agentid(0, $_clear_agentids);
			}
			if (empty($_appids)) {
				$this->message('error', '没有待安装的应用');
			}
			$appids = implode(',', $_appids);
		}

		// 整理出待安装的有效的应用ID列表
		$install_appids = array();
		if ($appids) {
			// 指定了待安装的应用
			foreach (explode(',', $appids) as $_appid) {
				if (isset($auth_application_list[$_appid])) {
					$install_appids[$_appid] = $_appid;
				}
			}
			unset($_appid);
		} else {
			// 未指定，则安装套件内所有
			foreach ($auth_application_list as $_appid => $_agentid) {
				$install_appids[$_appid] = $_appid;
			}
			unset($_appid);
		}
		$appids = implode(',', $install_appids);

		if (empty($install_appids)) {
			$this->message('error', '没有需要安装的应用');
		}

		// 当前安装队列未指定需要安装的应用，则默认安装列表内的第一个应用
		if (empty($appid)) {
			$_tmp = array_values($install_appids);
			$appid = $_tmp[0];
			unset($_tmp);
		}

		if (!isset($install_appids[$appid])) {
			$this->message('error', '待安装的应用不存在');
		}

		// 总计需要安装的应用数
		$total_num = count($install_appids);
		// 应用ID=>唯一标识符对应关系
		$appid2plugin = array_flip($suite_application_list);
		// 当前待安装的应用的唯一标识符
		$identifier = $appid2plugin[$appid];
		// 获取当前待安装应用的本地信息
		$s_plugin = new voa_s_oa_common_plugin();
		$plugin = $s_plugin->fetch_by_identifier($identifier);
		if (empty($plugin)) {
			$this->message('error', '待安装的本地应用不存在');
		}

		// 有顺序关系的待安装列表
		$appnum_list = array_values($install_appids);
		// 当前的安装顺序（以0开始）
		$ordernum = array_search($appid, $appnum_list);

		// 获取当前安装的应用agentid
		$agentid = $auth_application_list[$appid];
		if (!$agentid) {
			$this->message('error', '无法获取到已授权应用agentid');
		}

		// 更新 ucenter 的 corpid
		$client = voa_h_rpc::phprpc(config::get('voa.uc_url') . 'OaRpc/Rpc/Enterprise');
		if (!$client->update_enterprise_corpid($auth_app_list['auth_corp_info']['corpid'], $this->_setting['ep_id'])) {
			logger::error("corpid: {$auth_app_list['auth_corp_info']['corpid']}, ep_id: {$this->_setting['ep_id']}");
			$this->message('error', '更新站点信息错误');
			return false;
		}

		list($errcode, $errmsg) = $this->_bind_agent($agentid, $plugin['cp_pluginid'], $suiteid, $appid);
		if ($errcode != 0 && $errcode != 1104) {
			// 发生错误
			$this->message('error', '应用《'.$plugin['cp_name'].'》安装失败。'.$errmsg.'[Error:'.$errcode.']');
		}

		// 下个应用的顺序
		$next_ordernum = $ordernum + 1;
		// 全部应用安装完毕
		if (!isset($appnum_list[$next_ordernum])) {
			if ($this->request->get('is_first')) {
				// 第一次安装完应用，则跳转到同步通讯录页面
				$url = $this->cpurl('manage', 'member', 'impqywx', 0, array('is_first' => 1));
				$this->message('success', '', $url, true);
				return true;
			}
			$message = '<p style="line-height: 250%;margin: 0;padding: 0;">';
			$message .= '指定应用安装完毕，请登录微信企业号平台（<a href="https://qy.weixin.qq.com/cgi-bin/home?#app/list" target="_blank">qy.weixin.qq.com</a>）<br />';
			$message .= '检查该应用的可见范围（只有指定的部门能够使用该应用）设置是否正确<br />';
			$message .= '<a href="http://www.vchangyi.com/faq/221.html" target="_blank" style="color:blue;text-decoration:underline"><strong>详情可见帮助中心</strong></a>';
			$message .= '</p>';
			$message .= '<br />';
			$message .= '<a href="'.$this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id).'" class="btn btn-primary">回到应用中心</a>';

			// by zhuxun, 更新缓存
			$uda = &uda::factory('voa_uda_frontend_base');
			$uda->update_cache();
			// end

			$this->message('success', $message, false, false);
		}

		// 下个应用ID
		$next_appid = $appnum_list[$next_ordernum];

		$message = '正在安装第 '.($ordernum + 1).'/'.$total_num.'个应用（'.$plugin['cp_name'].'），即将自动安装下一个应用。';

		// 继续下个应用安装
		$this->message('success', $message, $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array(
			'suiteid' => $suiteid,
			'appid' => $next_appid,
			'appids' => $appids,
			'is_first' => $this->request->get('is_first')
		)), false);
	}

	// 判断是否来自主站
	protected function _from_www() {

		$data = array(
			'corpid' => (string)$this->request->get('corpid'),
			'suiteid' => (string)$this->request->get('suiteid'),
			'appids' => (string)$this->request->get('appids'),
			'ts' => (string)$this->request->get('ts'),
			'sig' => (string)$this->request->get('sig')
		);

		// 验证签名
		return voa_h_func::sig_check($data);
	}

	/**
	 * 初始化套件信息
	 * @param array $authinfo 授权信息
	 * @param string $corpid 企业号corpid
	 * @param string $suiteid 套件ID
	 */
	protected function _init_suite(&$authinfo, $corpid, $suiteid) {

		// 读取预授权信息
		$serv_pa = &service::factory('voa_s_uc_preauth');
		if (!$auth = $serv_pa->get_by_corpid_suiteid($corpid, $suiteid)) {
			return false;
		}

		// 授权信息
		$authinfo = unserialize($auth['authdata']);
		$oa_suite = array(
			'auth_corpid' => $authinfo['auth_corp_info']['corpid'],
			'permanent_code' => $authinfo['permanent_code'],
			'access_token' => $authinfo['access_token'],
			'expires' => startup_env::get('timestamp') + ($authinfo['expires_in'] * 0.8),
			'authinfo' => serialize($authinfo)
		);
		// 取套件id
		$serv_suite = &service::factory('voa_s_oa_suite');
		if (!$suite = $serv_suite->fetch_by_suiteid($suiteid)) {
			$oa_suite['suiteid'] = $suiteid;
			$serv_suite->insert($oa_suite);
		} else {
			$serv_suite->update($oa_suite, "`suiteid`='{$suiteid}'");
		}

		return true;
	}

}
