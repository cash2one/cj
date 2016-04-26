<?php
/**
 * voa_c_admincp_setting_application_list
 * 企业后台/系统设置/应用维护/修改
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_setting_application_list extends voa_c_admincp_setting_application_base {

	/** 启用和未启用名称 */
	protected $_list_types = array(
			'used' => '已开启应用',
			'unused' => '未开启应用',
			'waited' => '待审核应用',
	);

	/** 默认显示哪类应用 ： used\unused\waited */
	protected $_default_type = 'used';

	public function execute() {

		// 返回请求的授权信息
		$auth_code = (string)$this->request->get('auth_code');
		if (!empty($auth_code)) {
			return $this->_suite_install_by_authcode($auth_code);
		}

		// 新企业安装请求
		$corpid = (string)$this->request->get('corpid');
		if (!empty($corpid)) {
			return $this->_suite_install_by_preauth($corpid);
		}

		// 是否没有安装过应用
		$uda = &uda::factory('voa_uda_frontend_common_plugin_installed');
		$install_count = array();
		$uda->doit(array(), $install_count);
		$this->view->set('installed', $install_count['installed']);

		// 判断是否是请求安装应用
		$this->view->set('install_app', $this->request->get('install_app') ? 1 : 0);

		// 默认都使用授权模式启用应用
		$this->_setting['ep_wxqy'] = voa_d_oa_common_setting::WXQY_AUTH;
		// 判断当前使用的模式，确定具体的操作方法
		switch ($this->_setting['ep_wxqy']) {
			case voa_d_oa_common_setting::WXQY_CLOSE:
			case voa_d_oa_common_setting::WXQY_MANUAL:
				$this->_list_for_manual();
				break;
			default:
				$this->_list_for_auth();
				break;
		}

		return false;
	}

	/**
	 * 新企业安装请求
	 * @param string $corpid
	 */
	protected function _suite_install_by_preauth($corpid) {

		// 读取授权信息
		$s_preauth = &service::factory('voa_s_uc_preauth');
		$preauth = $s_preauth->get_by_conds(array('corpid' => $corpid));
		$authdata = unserialize($preauth['authdata']);
		$oa_suite = array(
			'suiteid' => $preauth['suiteid'],
			'auth_corpid' => $authdata['auth_corp_info']['corpid'],
			'permanent_code' => $authdata['permanent_code'],
			'access_token' => $authdata['access_token'],
			'expires' => startup_env::get('timestamp') + ($authdata['expires_in'] * 0.8),
			'authinfo' => serialize($authdata)
		);

		// 写入本地suite表
		$s_suite = &service::factory('voa_s_oa_suite');
		// 判断是否是首次安装
		if ($s_suite->count_by_conditions(array()) > 0) {
			$is_first = 0;
		} else {
			$is_first = 1;
		}
		$suite = $s_suite->fetch_by_conditions(array('suiteid' => $authdata['suiteid']));
		if (empty($suite)) {
			$s_suite->insert($oa_suite);
		} else {
			$s_suite->update($oa_suite, array('suiteid' => $authdata['suiteid']));
		}

		return $this->_suite_application_install($authdata, $preauth['suiteid'], $is_first);
	}

	/**
	 * 批量安装应用
	 * @param string $auth_code
	 * @return boolean
	 */
	protected function _suite_install_by_authcode($auth_code) {

		$serv = voa_wxqysuite_service::instance();
		$suiteid = (string)$this->request->get('suiteid');
		$data = array();
		if (!$serv = $serv->get_permanent_code($data, $auth_code, $suiteid)) {
			$this->_error_message('授权失败, 请重新授权');
			return true;
		}

		return $this->_suite_application_install($data, $suiteid);
	}

	/**
	 * 构造实际安装应用页面url并跳转
	 * @param array $data
	 * @return boolean
	 */
	protected function _suite_application_install($data, $suiteid, $is_first) {

		// 更新 corpid
		$request = controller_request::get_instance();
		$host = $request->server('HTTP_HOST');
		/**$serv_ep = &service::factory('voa_s_cyadmin_enterprise_profile');
		// 先清除之前的授权
		$wxcorpid_bak = $data['auth_corp_info']['corpid'] . '_bak';
		$serv_ep->update_by_conditions(array('ep_wxcorpid' => $wxcorpid_bak), "ep_wxcorpid='{$data['auth_corp_info']['corpid']}' AND ep_domain!='{$host}'");
		// 然后更新授权
		$serv_ep->update_by_conditions(array('ep_wxcorpid' => $data['auth_corp_info']['corpid']), 'ep_domain="'.$host.'"');*/
		$rpc = voa_h_rpc::phprpc(config::get('voa.cyadmin_url').'OaRpc/Rpc/Enterprise');
		if (!$rpc->update_by_domain($host, array('ep_wxcorpid' => $data['auth_corp_info']['corpid']))) {
			logger::error('uc=>host:'.$host.'; ep_wxcorpid:'.$data['auth_corp_info']['corpid']);
		}

		// 保持到配置
		$serv_set = &service::factory('voa_s_oa_common_setting');
		//$serv_set->update(array('cs_value' => $data['auth_corp_info']['corpid']), "`cs_key`='corp_id'");
		//$serv_set->update(array('cs_value' => $data['auth_corp_info']['corp_wxqrcode']), "`cs_key`='qrcode'");
		$serv_set->update_setting(array(
			'round_logo_url' => $data['auth_corp_info']['corp_round_logo_url'],
			'square_logo_url' => $data['auth_corp_info']['corp_square_logo_url'],
			'corp_id' => $data['auth_corp_info']['corpid'],
			'qrcode' => $data['auth_corp_info']['corp_wxqrcode']
		));
		voa_h_cache::get_instance()->get('setting', 'oa', true);

		// 读取套件信息
		$uda_application = &uda::factory('voa_uda_frontend_application_delete');
		$agentids = array();
		$install_list = array();
		if ($data['auth_info'] && $data['auth_info']['agent']) {
			foreach ((array)$data['auth_info']['agent'] as $_k => $_v) {
				$agentids[] = $_v['agentid'];
				$install_list[$_k] = $_v;
			}
		}

		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		foreach ($plugins as $_p) {
			if (empty($_p['cp_agentid']) || in_array($_p['cp_agentid'], $agentids)
					|| (!empty($_p['cp_suiteid']) && $suiteid != $_p['cp_suiteid'])) {
						continue;
					}

					$uda_application->delete($_p['cp_pluginid']);
		}

		// 直接跳转到安装页面
		$url = '/admincp/setting/application/bind/suiteid/'.$suiteid;
		if (isset($_GET['appids'])) {
			$url .= '/appids/'.$this->request->get('appids');
		}
		if ($is_first) {
			$url .= '/is_first/1';
		}
		$this->message('success', '', $url, true);

		return true;
	}

	protected function _check_move_agent(&$list) {

		if (empty($list['tj59546543529912af'])) {
			return true;
		}

		/**if (!in_array(3, $list['tj59546543529912af']['_agent_id_list'])) {
			return true;
		}*/

		foreach ($list['tj59546543529912af']['_agent_id_list'] as $_agentid => $_appid) {
			if (3 == $_appid) {
				if (empty($list['tj407a156836450616'])) {
					$list['tj407a156836450616'] = array(
						'_agent_id_list' => array($_agentid => $_appid)
					);
				} else {
					$list['tj407a156836450616']['_agent_id_list'][$_agentid] = $_appid;
				}
			}
		}

		return true;
	}

	/**
	 * 使用微信企业号授权模式（应用套件）方式的应用列表
	 * 基于 setting[ep_wxqy]进行判断
	 */
	protected function _list_for_auth() {

		// 已授权套件列表
		$suite_list = array();
		$s_suite = new voa_s_oa_suite();
		foreach ($s_suite->fetch_all() as $_suite) {
			// 解析授权信息
			$_authinfo = @unserialize($_suite['authinfo']);
			// 该套件已授权的应用列表
			$_suite['_agent_list'] = !empty($_authinfo['auth_info']['agent']) ? $_authinfo['auth_info']['agent'] : array();
			// 该套件已授权的应用数
			$_suite['_agent_count'] = count($_suite['_agent_list']);
			// 该套件已授权的应用agentid列表
			$_suite['_agent_id_list'] = array();
			foreach ($_suite['_agent_list'] as $_agent) {
				$_suite['_agent_id_list'][$_agent['agentid']] = $_agent['appid'];
			}
			$suite_list[$_suite['suiteid']] = $_suite;
		}

		// 特殊处理被移动过的应用, begin
		$this->_check_move_agent($suite_list);
		// end

		// 授权回调地址
		$auth_callback_url = config::get(startup_env::get('app_name').'.oa_http_scheme');
		$auth_callback_url .= $this->_setting['domain'];
		$auth_callback_url .= $this->cpurl($this->_module, $this->_operation, 'suite', $this->_module_plugin_id);

		$plgin2appid = config::get(startup_env::get('app_name').'.suite.plgin2appid');

		// by zhuxun, 没有应用的套件
		$noagent = config::get(startup_env::get('app_name').'.suite.noagent');

		$serv = voa_wxqysuite_service::instance();
		$list = $this->_uda_get->suite_agent_list();
		$scheme = config::get('voa.oa_http_scheme');
		foreach ($list as &$_cpg) {
			// 所在的套件ID
			$_suiteid = $_cpg['group']['cpg_suiteid'];
			// 所在的套件信息
			$_cpg['_suite'] = isset($suite_list[$_suiteid]) ? $suite_list[$_suiteid] : array();
			$_base_auth_url = $scheme.'uc.vchangyi.com/uc/home/suite/?ac=toqy&domain='.$this->_setting['domain'].'&suiteid='.$_suiteid;
			// 套件授权地址
			$_cpg['group']['_authurl'] = '';
			// 套件是否已授权
			$_cpg['group']['_is_auth_suite'] = !empty($_cpg['_suite']) ? true : false;
			foreach ($_cpg['list'] as &$_p) {

				// 应用绑定状态
				$_p['_is_bind'] = false;
				if (isset($plgin2appid[$_suiteid][$_p['cp_identifier']])) {
					$_p['_bindurl'] = $_base_auth_url.'&appids='.$plgin2appid[$_suiteid][$_p['cp_identifier']];
					$_p['_is_bind'] = $plgin2appid[$_suiteid][$_p['cp_identifier']] == $_p['cp_agentid'];
					$_cpg['group']['_authurl'] = $_base_auth_url.'&appids='.implode(',', $plgin2appid[$_suiteid]);
				} else {
					$_p['_bindurl'] = '';
					$_cpg['group']['_authurl'] = '';
				}

				// 套件已授权，进一步确定应用是否绑定
				if ($_cpg['group']['_is_auth_suite'] && $_p['cp_agentid'] && !empty($_cpg['_suite']['_agent_id_list'][$_p['cp_agentid']])) {
					$_p['_is_bind'] = true;
				}

				if (!empty($noagent)) {
					if (in_array($_suiteid, $noagent) && 0 < $_p['cp_agentid']) {
						$_p['_is_bind'] = true;
					}
				}

				if ($_p['_is_bind'] && $_p['cp_available'] != voa_d_oa_common_plugin::AVAILABLE_OPEN) {
					$_p['_is_bind'] = false;
				}

				// 应用是否已开启
				$_p['_allow_delete'] = ($_p['cp_available'] == voa_d_oa_common_plugin::AVAILABLE_OPEN) || ($_p['cp_available'] == voa_d_oa_common_plugin::AVAILABLE_CLOSE);
			}
		}

		// 删除应用的基本链接
		$delete_url_base = $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('cp_pluginid' => ''));
		// 关闭应用的基本链接
		$close_url_base = $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('available' => voa_d_oa_common_plugin::AVAILABLE_CLOSE,'cp_pluginid' => ''));
		// 开启应用的基本链接
		$open_url_base = $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('available' => voa_d_oa_common_plugin::AVAILABLE_OPEN, 'cp_pluginid' => ''));
		// 取消应用审核基本链接
		$cancel_url_base = '';

		$this->view->set('plugin_list', $list);
		$this->view->set('list_types', $this->_list_types);
		$this->view->set('delete_url_base', $delete_url_base);
		$this->view->set('close_url_base', $close_url_base);
		$this->view->set('open_url_base', $open_url_base);
		$this->view->set('cancel_url_base', $cancel_url_base);
		$this->view->set('availables', $this->_availables);
		$this->view->set('list', $list);
		$this->view->set('list_url_base', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('type' => '')));
		$this->view->set('setting', $this->_setting);

		$this->view->set('is_suite_auth_site', $this->_is_suite_auth_site);
		$this->output('setting/application_suite');
	}

	/**
	 * 使用手动开启应用方式的应用列表
	 * 基于 setting[ep_wxqy]进行判断
	 */
	protected function _list_for_manual() {

		$type = $this->request->get('type');
		$type = (string)$type;

		if (!$this->_open_wxqy) {
			// 当前企业没有企业微信号，则应用不需要审核
			unset($this->_list_types['waited']);

		} else {
			// 有微信企业号
			if ($this->_use_qywx_api != 'cyadmin') {
				// 不使用畅移后台开启则不需要待审核状态
				unset($this->_list_types['waited']);
			}
		}

		if (!isset($this->_list_types[$type])) {
			// 未指定的显示类型默认显示“已开启应用”
			$type = 'used';
		}

		if ($type == 'waited') {
			// 待审核应用

			// 应用列表
			$list = $this->_uda_get->site_plugin_list(array(
				voa_d_oa_common_plugin::AVAILABLE_WAIT_CLOSE,
				voa_d_oa_common_plugin::AVAILABLE_WAIT_OPEN,
				voa_d_oa_common_plugin::AVAILABLE_WAIT_DELETE
			));

			// 无应用时提示信息
			$data_none_msg = '暂无待审核的应用';

		} elseif ($type == 'unused') {
			// 未开启应用

			// 应用列表
			$list = $this->_uda_get->site_plugin_list(array(
				voa_d_oa_common_plugin::AVAILABLE_NEW,
				voa_d_oa_common_plugin::AVAILABLE_CLOSE,
				voa_d_oa_common_plugin::AVAILABLE_DELETE,
			));

			// 无应用时提示信息
			$data_none_msg = '您已开启了所有的应用';

		} else {
			// 已开启应用

			// 应用列表
			$list = $this->_uda_get->site_plugin_list(array(
				voa_d_oa_common_plugin::AVAILABLE_OPEN
			));

			// 无应用时提示信息
			$data_none_msg = '暂无已开启的应用';

		}

		// 删除应用的基本链接
		$delete_url_base = $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('cp_pluginid' => ''));
		// 关闭应用的基本链接
		$close_url_base = $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('available' => voa_d_oa_common_plugin::AVAILABLE_CLOSE,'cp_pluginid' => ''));
		// 开启应用的基本链接
		$open_url_base = $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('available' => voa_d_oa_common_plugin::AVAILABLE_OPEN, 'cp_pluginid' => ''));
		// 取消应用审核基本链接
		$cancel_url_base = '';

		//$this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('available' => -1, 'cp_pluginid' => ''));

		// 回调URL根
		$scheme = config::get('voa.oa_http_scheme');
		$plugin_callback_url_base = $scheme.$this->_setting['domain'].'/qywx.php?pluginid=';

		// 应用套件授权状态
		$auth_status = false;
		$s_suite = new voa_s_oa_suite();
		$suiteid = '';
		$suite = $s_suite->fetch_by_suiteid($suiteid);
		if (!empty($suite)) {
			$auth_status = true;
		}
		$this->view->set('auth_status', $auth_status);

		// 应用套件授权地址
		$serv = voa_wxqysuite_service::instance();
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme.'uc.vchangyi.com/uc/home/suite/?ac=toqy&cname=zhougong';
		$this->view->set('authurl', $url);
		//$this->view->set('authurl', $serv->get_oauth_url($suiteid, urlencode($url), 'suite'));
		// 绑定应用
		$this->view->set('bind_url_base', $this->cpurl($this->_module, $this->_operation, 'bind', $this->_module_plugin_id, array(
			'suiteid' => $suiteid,
			'cp_pluginid' => ''
		)));

		$this->view->set('type', $type);
		$this->view->set('data_none_msg', $data_none_msg);
		$this->view->set('list_types', $this->_list_types);
		$this->view->set('delete_url_base', $delete_url_base);
		$this->view->set('close_url_base', $close_url_base);
		$this->view->set('open_url_base', $open_url_base);
		$this->view->set('cancel_url_base', $cancel_url_base);
		$this->view->set('availables', $this->_availables);
		$this->view->set('list', $list);
		$this->view->set('list_url_base', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('type' => '')));
		$this->view->set('plugin_callback_url_base', $plugin_callback_url_base);
		$this->view->set('setting', $this->_setting);

		$this->output('setting/application_list');
	}

}
