<?php

/**
 * voa_c_admincp_office_interface_aflow
 * 企业后台/测试应用/添加流程
 * Create By gaosong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_interface_aflow extends voa_c_admincp_office_interface_base {

	public function execute() {

		// post请求执行添加
		if ($this->request->get_method() === 'POST') {
			$this->__add();
		} else {
			// 获取插件信息
			$plugins = array();
			$serv_plugin = &service::factory('voa_s_oa_common_plugin');
			$plugins = $serv_plugin->fetch_all();

			// 设置插件信息
			$this->view->set('plugins', $plugins);
			// 提交添加时的url
			$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
			// 输出模板
			$this->output('office/interface/aflow');
		}
	}

	/**
	 * 测试接口添加
	 */
	private function __add() {

		$data = $this->request->postx();
		$interface = array(
			'f_name' => '', // 流程名称
			'f_desc' => '', // 流程描述
			'cp_pluginid' => 0 // 应用id
		);

		// 过滤参数
		$this->_init_params($data, $interface);

		// 获取插件信息
		if (! empty($interface['cp_pluginid']) && $interface['cp_pluginid'] > 0) {

			$serv_plugin = &service::factory('voa_s_oa_common_plugin');
			// 获取插件信息
			$plugins = $serv_plugin->fetch_by_cp_pluginid($interface['cp_pluginid']);
			if (! empty($plugins)) {
				// 应用唯一标示
				$interface['cp_identifier'] = $plugins['cp_identifier'];
				// 应用名称
				$interface['cp_name'] = $plugins['cp_name'];
			}
		}

		$result = array();
		$uda = &uda::factory('voa_uda_frontend_interface_fadd');
		if (!$uda->add($interface,$result)) {
			$this->message('error', $uda->error);
		}

		// 请求参数入库
		if (!empty($data['interface'])) {

			$p_uda = &uda::factory('voa_uda_frontend_interface_sadd');
			$step = array();
			$step['f_id'] = $result['f_id'];

			foreach ($data['n_id'] as $_k => $_val) {

				// 选中的步骤入库
				if (!empty($data['interface'][$_k])) {
					$step['login_uid'] = (int)$data['login_uid'][$_k]; // 登录uid
					$step['n_id'] = (int)$data['n_id'][$_k]; // 接口id
					$step['s_order'] = (int)$data['order'][$_k]; // 排序

					$p_uda->add($step);
				}
			}
		}


		$this->_admincp_success_message('添加成功', $this->cpurl($this->_module, $this->_operation, 'flowlist', $this->_module_plugin_id));
	}

}
