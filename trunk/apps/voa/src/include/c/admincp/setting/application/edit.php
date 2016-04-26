<?php
/**
 * voa_c_admincp_setting_application_edit
 * 企业后台/系统设置/应用维护/启用或关闭应用
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_setting_application_edit extends voa_c_admincp_setting_application_base {

	/** 当前应用信息 */
	protected $_plugin = array();

	public function execute() {

		// 处理类型，开启还是关闭
		$available = $this->request->get('available');
		// 操作的应用的ID
		$cp_pluginid = $this->request->get('cp_pluginid');

		$available = (int)$available;
		$cp_pluginid = (int)$cp_pluginid;
		startup_env::set('pluginid', $cp_pluginid);

		// 获取应用信息
		$uda_application = &uda::factory('voa_uda_frontend_application_base');
		$this->_plugin = $uda_application->get_plugin($cp_pluginid);
		if (!$cp_pluginid || empty($this->_plugin)) {
			$this->message('error', '指定应用信息不存在');
		}

		$update_setting = array();
		// 检查系统设置表内的token是否已定义
		if (empty($this->_setting['token']) || !preg_match('/^[a-z0-9]+$/i', $this->_setting['token'])) {
			// 系统设置表的token不存在，则重新生成一个
			$token = md5(random(32).startup_env::get('timestamp'));
			$this->_setting['token'] = $token;
			$update_setting['token'] = $token;
		}

		// 检查系统设置表内的aes_key是否已定义
		if (empty($this->_setting['aes_key']) || !preg_match('/^[a-z0-9]+$/i', $this->_setting['aes_key'])) {
			// 不存在，则重新生成一个
			$aes_key = md5(random(32).startup_env::get('timestamp')).random(11);
			$this->_setting['aes_key'] = $aes_key;
			$update_setting['aes_key'] = $aes_key;
		}

		// 存在需要更新的系统设置项
		if ($update_setting) {
			// 更新系统设置
			$serv_setting = &service::factory('voa_s_oa_common_setting');
			$serv_setting->update_setting($update_setting);

			// 强制更新系统设置缓存
			voa_h_cache::get_instance()->get('setting', 'oa', true);
		}


		// 应用的图标url
		$this->_plugin['_icon_url'] = $uda_application->application_icon_url($this->_plugin['cp_icon']);

		// 当前应用响应微信请求的url
		$scheme = config::get('voa.oa_http_scheme');
		$plugin_url = $scheme.$this->_setting['domain'].'/qywx.php?pluginid='.$this->_plugin['cp_pluginid'];

		// 应用信息
		$this->view->set('plugin', $this->_plugin);
		// 系统环境设置
		$this->view->set('setting', $this->_setting);
		// 当前应用响应微信请求的url
		$this->view->set('plugin_url', $plugin_url);
		// 提交操作的url
		$this->view->set('application_update_submit_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('available' => $available, 'cp_pluginid' => $cp_pluginid)));



		if ($available < 0) {
			// 撤销申请操作

			if ($this->_use_qywx_api) {
				// 如果启用了企业微信接口，则不需要进行撤销
				$this->message('error', '未知的应用管理操作');
			}

			// TODO 撤销功能不开发
			$this->message('error', '未知的应用管理操作');

			//
			//$uda_application = &uda::factory('voa_uda_frontend_application_cancel');

		} elseif ($available == voa_d_oa_common_plugin::AVAILABLE_OPEN) {
			// 开启应用

			if ($this->_is_post()) {

				$agent_id = $this->request->post('agent_id');
				if (empty($agent_id)) {
					$this->_msg('error', $this->_wechat_noun_list['agentid'].' 必须填写', 1001);
				}

				// 先检查是否有应用权限
				//$r = array();
				//if (!$this->_check_agent_power($agent_id, $r)) {
				//	$this->_msg($r['type'], $r['message'], $r['code'], '');
				//	return;
				//}

				$uda_application = &uda::factory('voa_uda_frontend_application_open');
				if ($uda_application->open($cp_pluginid, $agent_id)) {

					// by zhuxun, 更新缓存
					$uda_application->update_cache();
					// end

					// 开启/通知开启应用成功
					if ($this->_use_qywx_api == 'cyadmin') {
						$msg = '新应用开启申请已提交，请等待系统审核';
						$url = $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id, array('type' => 'waited'));
					} else {
						$msg = '新应用开启成功，欢迎使用';
						$url = $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id, array('type' => 'used'));
					}
					$this->_msg('success', $msg, 0, $url);
				} else {
					// 失败
					$this->_msg('error', $uda_application->error, -1);
				}
				return true;
			}

			// 是否是首次开通
			$first_open = $this->_plugin['cyea_id'] ? false : true;
			$this->view->set('first_open', $first_open);

			// 上次开启时的应用代理ID
			$this->view->set('agent_id', $this->_plugin['cp_agentid']);


			$this->output('setting/application_open');
			return true;

			// 引入开启时的界面模板
/* 			if ($first_open) {
				$this->output('setting/application_open');
			} else {
				$this->output('setting/application_reopen');
			}
			return true; */

		} elseif ($available == voa_d_oa_common_plugin::AVAILABLE_CLOSE) {
			// 关闭应用

			if ($this->_is_post()) {
				$uda_application = &uda::factory('voa_uda_frontend_application_close');
				if ($uda_application->close($cp_pluginid)) {

					// by zhuxun, 更新缓存
					$uda_application->update_cache();
					// end

					// 关闭/通知关闭应用成功
					if ($this->_use_qywx_api == 'cyadmin') {
						$msg = '应用关闭请求已提交，请等待系统审核';
						$url = $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id, array('type' => 'waited'));
					} else {
						$msg = '应用已经关闭，应用数据已为您保存';
						$url = $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id, array('type' => 'unused'));
					}
					$this->message('success', $msg, $url, false);
				} else {
					// 失败
					$this->message('error', $uda_application->error.'['.$uda_application->errcode.']');
				}
				return true;
			}

			// 引入关闭时的界面模板
			$this->output('setting/application_close');
			return true;

		} else {

			$this->message('error', '错误请求未知操作');

		}
	}

	/**
	 * 检查应用权限
	 * @param number $agentid
	 * @param array $result <strong style="color:red">(引用结果)</strong>结果
	 * @return boolean
	 */
	protected function _check_agent_power($agentid = 0, &$result) {
		$result = array();
		if (!$agentid) {
			$result = array(
				'type' => 'error',
				'message' => '应用ID 必须填写',
				'code' => -1,
			);
			return false;
		}

		// 尝试去读取菜单来判断是否具有菜单权限
		$wxqy_menu = &voa_wxqy_menu::instance();
		$r = array();
		if (!$wxqy_menu->get($agentid, $r)) {
			// 出错
			$msg = '应用权限设置错误，请仔细按照第6步的“设置应用权限”操作';
			if ($wxqy_menu->errcode == '40028') {
				$msg = '应用ID 不存在，请确保填写正确';
			}
			$result = array(
				'type' => 'error',
				'message' => $msg.$wxqy_menu->errmsg.$wxqy_menu->errcode,
				'code' => $wxqy_menu->errcode
			);
			return false;
		}

		return true;
	}

	/**
	 * 内部消息
	 * @param string $type
	 * @param string $message
	 * @param string $code
	 * @param number $url
	 */
	protected function _msg($type = '', $message = '', $code = 0, $url = '') {

		if ($this->_is_ajax) {
			$result = array(
				'url' => $url
			);
			$this->_ajax_message($code, $message, $result, false, '');
		} else {
			$this->message($type, $message, $url, false);
		}
	}

}
