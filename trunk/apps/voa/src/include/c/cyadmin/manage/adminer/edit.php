<?php

/**
 * voa_c_cyadmin_manage_adminer_edit
 * 主站后台/后台管理/管理员管理/编辑
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_manage_adminer_edit extends voa_c_cyadmin_manage_adminer_base {

	public function execute() {

		$ca_id = $this->request->get('ca_id');
		$ca_id = rintval($ca_id, false);
		if ($ca_id < 1) {
			$this->message('error', '请指定要编辑的管理员');
		}
		
		// 管理员详情
		$adminer = $this->_adminer_get($ca_id, false);
		if (! $adminer) {
			$this->message('error', '对不起，指定的管理员不存在 或 已被删除');
		}
		
		// 提交修改动作
		if ($this->_is_post()) {
			
			// 返回的消息
			$result_msg = '';
			$submit['ca_job'] = 0;
			// 提交的数据
			$submit = array();
			$submit['upid'] = $this->request->post('upid');
			$submit['ca_username'] = $this->request->post('ca_username');
			$submit['ca_password'] = $this->request->post('ca_password');
			$submit['ca_locked'] = $this->request->post('ca_locked');
			$submit['cag_id'] = $this->request->post('cag_id');
			$submit['ca_realname'] = $this->request->post('ca_realname');
			$submit['ca_mobilephone'] = $this->request->post('ca_mobilephone');
			$submit['ca_job'] = $this->request->post('ca_job');
			$submit['ca_email'] = $this->request->post('ca_email');
			if ($this->_adminer_update($adminer, $submit, $result_msg)) {
				// 更新成功
				voa_h_cache::get_instance()->get('adminer', 'cyadmin', true);
				$this->message('success', '编辑管理员操作成功', $this->cpurl($this->_module, $this->_operation, 'list'));
				
			} else {
				// 更新失败
				$this->message('error', $result_msg);
			}
		}
		
		// 当前编辑的管理员ca_id
		$this->view->set('ca_id', $ca_id);
		// 当前编辑的管理员信息
		$this->view->set('adminer', $this->_adminer_format($adminer));
		// 表单提交链接
		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, array('ca_id' => $ca_id)));
		// 管理员状态数组推送到模板内
		$this->view->set('adminer_locked_map', $this->_adminer_locked_map);
		// 系统管理员的状态标记
		$this->view->set('system_adminer', voa_d_cyadmin_common_adminer::LOCKED_SYS);
		// 管理组列表
		$this->view->set('adminergroup_list', $this->_adminergroup_list());
		// 添加管理组的链接
		$add_adminergroup_url = $this->cpurl($this->_module, 'adminergroup', 'add');
		$this->view->set('add_adminergroup_url', $this->show_link($add_adminergroup_url, '', '添加新管理组', 'fa-plus'));
		
		// 获取销售主管
		$serv_rec = &service::factory('voa_s_cyadmin_common_subordinates');
		$adminer_leader = $serv_rec->list_by_conds(array('un_id' => $ca_id));
		$leader = array();
		$this->_adminer_data = voa_h_cache::get_instance()->get('adminer', 'cyadmin');
		
		//当前绑定的上级主管
		if (!empty($adminer_leader)) {
			foreach ($adminer_leader as $k => $v) {
				$leader_id = $v['ca_id'];
			}
			$this->view->set('leader_id', $leader_id); // 主管
		}
		
		// 有无主管都加载主管列表
		$adminer_leader = &service::factory('voa_s_cyadmin_common_newadminer');
		$leader_li = $adminer_leader->list_by_conds(array('ca_job' => 1));
		$leader_list = array();
		//遍历取所有主管信息
		if (!empty($leader_li)) {
			foreach ($leader_li as $k => $v) {
				$leader_list[$v['ca_id']] = $v['ca_realname'];
			}
			$this->view->set('leader_list', $leader_list); // 主管
		}
		$this->output('cyadmin/manage/adminer/edit_form');
	}

}

