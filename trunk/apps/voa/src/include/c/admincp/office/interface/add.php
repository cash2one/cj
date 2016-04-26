<?php

/**
 * voa_c_admincp_office_interface_add
 * 企业后台/测试应用/接口列表
 * Create By gaosong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_interface_add extends voa_c_admincp_office_interface_base {

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
			$this->output('office/interface/add');
		}
	}

	/**
	 * 测试接口添加
	 */
	private function __add() {

		$data = $this->request->postx();

		$interface = array('name' => '', // 接口名称
			'desc' => '', // 接口描述
			'url' => '', // 接口地址
			'cp_pluginid' => 0, // 应用id
			'method' => 'GET'); // 请求方式（GET POST PUT DELETE）

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
		$uda = &uda::factory('voa_uda_frontend_interface_add');
		if (! $uda->add($interface,$result)) {
			$this->message('error', $uda->error);
		}

		// 请求参数入库
		if (!empty($data['key'])) {

			$p_uda = &uda::factory('voa_uda_frontend_interface_padd');
			$parameter = array();
			$parameter['n_id'] = $result['n_id'];

			foreach ($data['key'] as $_k => $_val) {
				$parameter['name'] = $data['key'][$_k]; // 参数名
				$parameter['val'] = $data['val'][$_k]; // 参数值
				$parameter['type'] = $data['p_type'][$_k]; // 参数类型
				$p_uda->add($parameter);
			}
		}


		$this->_admincp_success_message('添加成功', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
	}

}
