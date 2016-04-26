<?php
/**
 * 授权更迭
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_wxqysuite_post_auth extends voa_c_api_wxqysuite_abstract {

	public function execute() {

		$suiteid = $this->_get('suite_id');

    	$serv = &service::factory('voa_wxqysuite_service');
    	$serv->get_auth_info($suiteid);

		// 初始化 suite
		$serv_suite = &service::factory('voa_s_oa_suite');
		// 如果未读到
		if (!$oa_suite = $serv_suite->fetch_by_suiteid($suiteid)) {
			logger::error($suiteid.var_export($this->_params, true));
			$this->_set_errcode(voa_errcode_api_wxqysuite::SUITE_ID_ERROR);
			return true;
		}

		// 读取套件信息
		$uda_application = &uda::factory('voa_uda_frontend_application_delete');
		$authinfo = unserialize($oa_suite['authinfo']);
		$agentids = array();
		if ($authinfo['auth_info'] && $authinfo['auth_info']['agent']) {
			foreach ((array)$authinfo['auth_info']['agent'] as $_k => $_v) {
				$agentids[] = $_v['agentid'];
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

		return true;
	}

}
