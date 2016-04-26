<?php

/**
 * edit.php
 * 账号信息
 * Create By lixue
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_status_edit extends voa_c_admincp_base
{
	public function execute()
	{
		//判断企业id
		$epid = $this->request->get('epid');
		$epid = rintval($epid);
		if ($epid < 1) {
			$this->_ajax_message('3000001', '企业id不正确');
		}

		//判断企业名称
		$name = $this->request->get('name');
		$data['ep_name'] = rhtmlspecialchars($name);
		if ($data['ep_name'] != $name || empty($data['ep_name'])) {
			$this->_ajax_message('3000002', '企业名称不能包含特殊字符并且不能为空');
		}

		$rpc_cyadmin = voa_h_rpc::phprpc(config::get('voa.cyadmin_url').'OaRpc/Rpc/Enterprise');
		if (!$rpc_cyadmin->update_by_ep_id($epid, $data)) {

			//更新系统设置
			$serv_set = &service::factory('voa_s_oa_common_setting');
			$serv_set->update(array('cs_value' => $data['ep_name']), array('cs_key' => 'sitename'));
			voa_h_cache::get_instance()->get('setting', 'oa', true);

			// 同步uc库中数据
			$rpc_uc = voa_h_rpc::phprpc(config::get('voa.uc_url').'OaRpc/Rpc/Enterprise');
			$rpc_uc->update_by_ep_id($epid, $data);
			$this->_ajax_message('0', '更新成功');

		} else {
			$this->_ajax_message('3000003', '更新失败,请稍后再试');
		}

	}

}
