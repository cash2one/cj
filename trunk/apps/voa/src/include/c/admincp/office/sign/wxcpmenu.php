<?php

/**
 * voa_c_admincp_office_sign_wxcpmenu
 * 企业后台/微办公管理/考勤签到/考勤微信菜单修改
 * Create By lixue
 * $Id$
 */
class voa_c_admincp_office_sign_wxcpmenu extends voa_c_admincp_office_sign_base {
	public function execute() {

		//处理接收的数据
//		$post = $this->request->postx();
//		$serv_set = &service::factory('voa_s_oa_sign_setting');
//
//		if (!empty($post)) {
//
//			foreach ($post as $key => $val) {
//				$menu_key = substr($key, 0, 1);
//				for ($i = 0; $i <= 2; $i ++) {
//					if ($menu_key == $i) {
//						if (!isset($tmp[$i]['name'])) {
//							$tmp[$i]['name'] = $post[$i . '-name'];
//						}
//						if (!isset($tmp[$i]['url'])) {
//							$tmp[$i]['url'] = $post[$i . '-url'];
//						}
//						if (!isset($tmp[$i]['type'])) {
//							$tmp[$i]['type'] = $post[$i . '-type'];
//						}
//					}
//				}
//			}
//			//修改操作
//			if (!empty($post)) {
//				//判断有无菜单数据
//				$record = $serv_set->list_all();
//
//				//获取agentid,pluginid
//				foreach($record as $_agent_plugin){
//					if($_agent_plugin['ss_key'] == 'agentid'){
//						$agentid = $_agent_plugin['ss_value'];
//					}
//					if($_agent_plugin['ss_key'] == 'pluginid'){
//						$pluginid = $_agent_plugin['ss_value'];
//					}
//				}
//
//				// 加载企业微信应用型代理菜单接口类
//				$qywx_menu = new voa_wxqy_menu();
//				if (!$qywx_menu->create($agentid, $tmp, $pluginid)) {
//					$error = empty($qywx_menu->menu_error) ? '更新应用菜单发生未知错误' : $qywx_menu->menu_error;
//					$this->message('error', $error);
//					return false;
//				}
//
//				foreach($record as $_set){
//					if($_set['ss_key'] == 'wxcpmenu'){
//						$exists = 1;
//					}
//				}
//				//如果有就更新，没有就增加
//				if (isset($exists)) {
//					$conds_wx['ss_key'] = 'wxcpmenu';
//					$data['ss_value'] = serialize($tmp);
//					$result = $serv_set->update_by_conds($conds_wx, $data);
//				} else {
//					$data_add['ss_key'] = 'wxcpmenu';
//					$data_add['ss_value'] = serialize($tmp);
//					$data_add['ss_type'] = 1;
//					$data_add['ss_comment'] = '微信菜单';
//					$result = $serv_set->insert($data_add);
//				}
//				//同步cpmenu表
//				$serv_cpmenu = &service::factory('voa_s_oa_common_cpmenu');
//				$conds_cp1['ccm_operation'] = 'sign';
//				$conds_cp1['ccm_subop'] = 'list';
//				$data1['ccm_name'] = $post['0-name'];
//				$serv_cpmenu->update_by_conditions($data1, $conds_cp1);
//
//				$conds_cp2['ccm_operation'] = 'sign';
//				$conds_cp2['ccm_subop'] = 'upposition';
//				$data2['ccm_name'] = $post['1-name'];
//				$serv_cpmenu->update_by_conditions($data2, $conds_cp2);
//				//更新缓存
//				$uda_base = &uda::factory('voa_uda_frontend_base');
//				$uda_base->update_cache();
//
//				if ($result) {
//					$this->message('success', '修改成功', $this->cpurl($this->_module, $this->_operation, 'wxcpmenu', $this->_module_plugin_id), false);
//				} else {
//					$this->message('error', '修改失败');
//				}
//			}
//
//		}
//		//数据初始化
//		$conds_wx['ss_key'] = 'wxcpmenu';
//		$record = $serv_set->get_by_conds($conds_wx);
//		//如果有记录则显示
//		if ($record) {
//			$list = unserialize($record['ss_value']);
//		} else {
//			$list = config::get(startup_env::get('app_name') . '.application.' . 'sign' . '.menu.qywx');
//		}
//
//		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
//		$this->view->set('list', $list);
        $this->view->set('pluginId', $this->_module_plugin_id);
		$this->output('office/sign/wxcpmenu');
	}

}
