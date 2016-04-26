<?php

/**
 * account.php
 * 账号信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_status_account extends voa_c_admincp_system_status_base {

	const STANDARD = 1; // 普通版 付费类型

	public function execute() {

		// 获取企业信息
		$settings = voa_h_cache::get_instance()->get('setting', 'oa');
		$rpc = voa_h_rpc::phprpc(config::get('voa.cyadmin_url').'OaRpc/Rpc/Enterprise');
		$com_info = $rpc->get_by_ep_id($settings['ep_id']);

		// 获取企业套件使用情况
		$serv_plugin_group = &service::factory('voa_s_oa_common_plugin_group');
		$group = $serv_plugin_group->fetch_all();
		foreach ($group as $k => &$v) {
			// 有使用时间
			if (!empty($v['date_start']) && !empty($v['date_end'])) {
				$v['date_start'] = rgmdate($v['date_start'], 'Y-m-d H:i:s');
				$v['date_end'] = rgmdate($v['date_end'], 'Y-m-d H:i:s');
			}
		}

		if ($com_info) {
			$list = array();
			$this->_formdata($com_info, $list);
			$list['pay_status'] = $group;
			// 判断企业使用人数是否在免费人数下
			if ($this->_member_count <= config::get('voa.cyadmin_domain.free_use_number')) {
				$list['free_pay_status'] = config::get('voa.cyadmin_domain.free_message');
			}

			$this->view->set('list', $list);
		}

		$this->output('system/status/account');
	}

	/**
	 * 整理数据
	 * @param $in
	 * @param $out
	 */
	public function _formdata($in, &$out) {

		$in['_ep_start'] = !empty($in['ep_start']) ? date('Y-m-d H:i:s', $in['ep_start']) : 0;
		$in['_ep_end'] = !empty($in['ep_end']) ? date('Y-m-d H:i:s', $in['ep_end']) : 0;
		$in['_ep_created'] = date('Y-m-d H:i:s', $in['ep_created']);
		$out = $in;
	}

}
