<?php
/**
 * voa_c_admincp_setting_application_delete
 * 企业后台/系统设置/应用维护/删除应用
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_setting_application_delete extends voa_c_admincp_setting_application_base {

	public function execute() {

		// 旧的用户，暂时禁止使用套件方式启用应用
		if (!$this->_is_suite_auth_site) {
			$this->message('error', '抱歉，应用套件服务目前正在内部测试，暂不支持应用绑定及授权，请稍候再试。');
		}

		$cp_pluginid = $this->request->get('cp_pluginid');
		$cp_pluginid = rintval($cp_pluginid, false);

		// 获取应用信息
		$uda_application = &uda::factory('voa_uda_frontend_application_base');
		$this->_plugin = $uda_application->get_plugin($cp_pluginid);
		if (!$cp_pluginid || empty($this->_plugin)) {
			$this->message('error', '指定应用信息不存在');
		}

		// 当前应用响应微信请求的url
		$scheme = config::get('voa.oa_http_scheme');
		$plugin_url = $scheme.$this->_setting['domain'].'/qywx.php?pluginid='.$this->_plugin['cp_pluginid'];

		if ($this->_is_post()) {

			$uda_application = &uda::factory('voa_uda_frontend_application_delete');
			if ($uda_application->delete($cp_pluginid)) {

				// by zhuxun, 更新缓存
				$uda_application->update_cache();
				// end

				// 删除/通知删除 应用成功
				if ($this->_use_qywx_api == 'cyadmin') {
					$msg = '删除应用申请已提交，请等待系统审核';
					$url = $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id, array('type' => 'waited'));
				} else {
					$msg = '应用删除操作完毕';
					$url = $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id, array('type' => 'used'));
				}
				$this->message('success', $msg, $url, false);
			} else {
				// 失败
				$this->message('error', $uda_application->error);
			}

			return true;
		}

		// 应用信息
		$this->view->set('plugin', $this->_plugin);
		// 系统环境设置
		$this->view->set('setting', $this->_setting);
		// 当前应用响应微信请求的url
		$this->view->set('plugin_url', $plugin_url);
		// 提交操作的url
		$this->view->set('application_update_submit_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('cp_pluginid' => $cp_pluginid)));

		$this->output('setting/application_delete');
		return true;
	}

}
