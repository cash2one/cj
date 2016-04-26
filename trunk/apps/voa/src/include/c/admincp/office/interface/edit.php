<?php

/**
 * voa_c_admincp_office_interface_edit
 * 企业后台/测试应用/接口详情
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_interface_edit extends voa_c_admincp_office_interface_base {

	public function execute() {

		// post请求执行编辑
		if ($this->request->get_method() === 'POST') {
			$this->__edit();
		} else {
			// 获取接口id
			$n_id = $this->request->get('n_id');
			if (empty($n_id)) {
				$this->message('error', voa_errcode_oa_goods::INTERFACE_DATA_IS_NOT_EXIST);
			}

			// 获取详情数据
			$interface = array();
			$uda = &uda::factory('voa_uda_frontend_interface_view');
			$uda->get_info($n_id,$interface);
			if (empty($interface)) {
				$this->message('error', voa_errcode_oa_goods::INTERFACE_DATA_IS_NOT_EXIST);
			}

			// 获取接口请求参数
			$list = $this->__paramter($n_id);

			// 获取插件信息
			$plugins = array();
			$serv_plugin = &service::factory('voa_s_oa_common_plugin');
			$plugins = $serv_plugin->fetch_all();

			// 提交添加时的url
			$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

			// 设置详情
			$this->view->set('view', $interface);
			// 设置插件信息
			$this->view->set('plugins', $plugins);
			// 设置接口请求参数
			$this->view->set('list', $list);

			$this->output('office/interface/edit');
		}
	}

	private function __edit() {

		$data = $this->request->postx();
		$interface = array(
			'name' => '', // 接口名称
		    'n_id' => 0,
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

		// 编辑接口
		$uda = &uda::factory('voa_uda_frontend_interface_edit');
		$uda->edit($interface);

		// 请求参数修改
		if (!empty($data['key'])) {

			$p_uda = &uda::factory('voa_uda_frontend_interface_pedit');
			$p_add = &uda::factory('voa_uda_frontend_interface_padd');
			$parameter = array(); // 数据数组
			$parameter['n_id'] = $interface['n_id'];
			foreach ($data['key'] as $_k => $_val) {

				$parameter['p_id'] = $data['p_id'][$_k];
				$parameter['name'] = $data['key'][$_k]; // 参数名
				$parameter['val'] = $data['val'][$_k]; // 参数值
				$parameter['type'] = $data['p_type'][$_k]; // 参数类型

				if (empty($parameter['p_id'])) {
					$p_add->add($parameter);
				} else {
					$p_uda->edit($parameter);
				}
			}
		} else {
			// 编辑时删除所有参数
			$p_delete = &uda::factory('voa_uda_frontend_interface_pdelete');
			$list_paramter = $this->__paramter($interface['n_id']);

			foreach ($list_paramter as $_k => $_val) {
				$p_delete->delete($_val['p_id']);
			}
		}

		$this->_admincp_success_message('编辑成功', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
	}

	private function __paramter($n_id) {
		// 获取接口请求参数
		$uda_list = &uda::factory('voa_uda_frontend_interface_plist');
		// 读取列表及总数
		$list = array();
		$conds = array();
		$conds['n_id'] = $n_id;
		$uda_list->list_by_conds($conds,$list);

		return $list;
	}
}
