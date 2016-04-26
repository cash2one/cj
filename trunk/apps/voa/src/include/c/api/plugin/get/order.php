<?php
/**
 * 获取插件排序列表
 * $Author$
 * $Id$
 */

class voa_c_api_plugin_get_order extends voa_c_api_plugin_base {

	public function execute() {

		$serv_pd = &service::factory('voa_s_oa_common_plugin_display', array('pluginid' => startup_env::get('wbs_uid')));
		$list = $serv_pd->fetch_order_list(startup_env::get('wbs_uid'));

		$pluginids = array();

		$favs = array();
		$unfavs = array();
		foreach ($list as $_pd) {
			// 测试代码, 临时, debug
			if (!in_array($this->_plugins[$_pd['cp_pluginid']]['cp_identifier'], array('notice', 'sign', 'project', 'dailyreport', 'askfor', 'askoff'))) {
				continue;
			}

			if ($_pd['isfav']) {
				$favs[] = array(
					'pluginid' => $_pd['cp_pluginid'],
					'ordernum' => $_pd['cpd_ordernum']
				);
			} else {
				$unfavs[] = array(
					'pluginid' => $_pd['cp_pluginid'],
					'ordernum' => $_pd['cpd_ordernum']
				);
			}

			$pluginids[$_pd['cp_pluginid']] = $_pd['cp_pluginid'];
		}

		foreach ($this->_plugins as $_p) {
			// 测试代码, 临时, debug
			if (!in_array($_p['cp_identifier'], array('notice', 'sign', 'project', 'dailyreport', 'askfor', 'askoff'))) {
				continue;
			}

			if (array_key_exists($_p['cp_pluginid'], $pluginids)) {
				continue;
			}

			$unfavs[] = array(
				'pluginid' => $_p['cp_pluginid'],
				'ordernum' => 0
			);
		}

		$this->_result = array(
			'favs' => $favs,
			'unfavs' => $unfavs
		);

		return true;
	}

}
