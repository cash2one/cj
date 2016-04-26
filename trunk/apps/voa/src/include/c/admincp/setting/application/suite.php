<?php
/**
 * voa_c_admincp_setting_application_suite
 * 企业后台/系统设置/应用维护/获取永久授权码
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_c_admincp_setting_application_suite extends voa_c_admincp_setting_application_base {

	public function execute() {

		// 旧的用户，暂时禁止使用套件方式启用应用
		if (!$this->_is_suite_auth_site) {
			$this->message('error', '抱歉，应用套件服务目前正在内部测试，暂不支持应用绑定及授权，请稍候再试。');
		}

		$auth_code = (string)$this->request->get('auth_code');
		$suiteid = (string)$this->request->get('suiteid');

		if (empty($auth_code)) {
			$this->message('error', '授权码信息错误，请返回重试');
			return true;
		}

		if (empty($suiteid)) {
			$this->message('error', '应用套件 ID 错误，请返回重试');
		}

		$serv = voa_wxqysuite_service::instance();
		$data = array();
		if (!$serv = $serv->get_permanent_code($data, $auth_code, $suiteid)) {
			$this->message('error', '微信企业号应用套件授权失败, 请返回重新授权');
			return true;
		}

		$this->message('success', '微信企业号应用套件使用授权成功，即将返回到应用列表继续操作'
				, $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		return true;
	}

}
