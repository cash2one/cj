<?php

/**
 * voa_c_admincp_office_interface_fedit
 * 企业后台/测试应用/流程修改
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_interface_fedit extends voa_c_admincp_office_interface_base {

	public function execute() {

		// post请求执行编辑
		if ($this->request->get_method() === 'POST') {
			$this->__edit();
		} else {
			// 获取流程id
			$f_id = (int)$this->request->get('f_id');
			if (empty($f_id)) {
				$this->message('error', voa_errcode_oa_interface::INTERFACE_DATA_IS_NOT_EXIST);
			}

			// 获取流程详情
			$flow = array();
			$uda = &uda::factory('voa_uda_frontend_interface_fview');
			$uda->get_info($f_id,$flow);
			if (empty($flow)) {
				$this->message('error', voa_errcode_oa_interface::INTERFACE_DATA_IS_NOT_EXIST);
			}

			// 获取流程步骤
			$list = $this->__step($f_id);

			// 获取应用信息
			$plugins = array();
			$serv_plugin = &service::factory('voa_s_oa_common_plugin');
			$plugins = $serv_plugin->fetch_all();

			// 提交添加时的url
			$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

			// 设置详情
			$this->view->set('view', $flow);
			// 设置插件信息
			$this->view->set('plugins', $plugins);
			// 设置流程步骤
			$this->view->set('list', $list);

			$this->output('office/interface/fedit');
		}
	}

	private function __edit() {

		$data = $this->request->postx();
		$flow = array(
		    'f_id' => 0,
			'f_name' => '', // 流程名称
			'f_desc' => '', // 流程描述
			'cp_pluginid' => 0 // 应用id
		);

		// 过滤参数
		$this->_init_params($data, $flow);

		// 获取插件信息
		if (! empty($flow['cp_pluginid']) && $flow['cp_pluginid'] > 0) {

			$serv_plugin = &service::factory('voa_s_oa_common_plugin');
			// 获取插件信息
			$plugins = $serv_plugin->fetch_by_cp_pluginid($flow['cp_pluginid']);
			if (! empty($plugins)) {
				// 应用唯一标示
				$flow['cp_identifier'] = $plugins['cp_identifier'];
				// 应用名称
				$flow['cp_name'] = $plugins['cp_name'];
			}
		}

		// 编辑流程
		$uda = &uda::factory('voa_uda_frontend_interface_fedit');
		$uda->edit($flow);

		// 请求参数修改
		if (!empty($data['interface'])) {

			$p_uda = &uda::factory('voa_uda_frontend_interface_sedit');
			$p_add = &uda::factory('voa_uda_frontend_interface_sadd');
			$step = array(); // 数据数组
			$step['f_id'] = (int)$flow['f_id'];

			foreach ($data['n_id'] as $_k => $_val) {

				// 选中的步骤入库
				if (!empty($data['interface'][$_k])) {
					if (!empty($data['s_id'][$_k])) {
						$step['s_id'] = (int)$data['s_id'][$_k];// 步骤id
					}
					$step['n_id'] = (int)$data['n_id'][$_k];// 接口id
					$step['s_order'] = (int)$data['order'][$_k]; // 排序
					$step['login_uid'] = (int)$data['login_uid'][$_k]; // 登录uid

					// 接口步骤不在流程里则添加
					if (empty($data['s_id'][$_k])) {
						unset($step['s_id']);
						$p_add->add($step);
					} else {
						$p_uda->edit($step);
					}
				} else {
					if (!empty($data['s_id'][$_k])) {
						$p_delete = &uda::factory('voa_uda_frontend_interface_sdelete');
						$p_delete->delete($data['s_id'][$_k]);
					}
				}
			}
		} else {
			// 编辑时取消步骤
			$p_delete = &uda::factory('voa_uda_frontend_interface_sdelete');
			$list_step = $this->__step($flow['f_id']);

			foreach ($list_step as $_k => $_val) {
				$p_delete->delete($_val['s_id']);
			}
		}

		$this->_admincp_success_message('编辑成功', $this->cpurl($this->_module, $this->_operation, 'flowlist', $this->_module_plugin_id));
	}

	private function __step($f_id) {

		$uda_list = &uda::factory('voa_uda_frontend_interface_steplist');
		// 读取流程步骤列表
		$list = array();
		$conds = array();
		$conds['f_id'] = $f_id;
		$uda_list->list_by_conds($conds,$list);

		return $list;
	}
}
