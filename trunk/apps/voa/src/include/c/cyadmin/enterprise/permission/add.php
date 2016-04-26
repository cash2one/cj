<?php

/**
 * add.php
 * @author Burce
 *
 */
class voa_c_cyadmin_enterprise_permission_add extends voa_c_cyadmin_enterprise_base {
	public $job = array(
		'1' => '主管',
		'2' => '销售人员'
	);
	public function execute() {

		$post = $this->request->postx();
		// 添加操作
		if (! empty($post)) {
			try {
				$data = null;
				$uda = &uda::factory('voa_uda_cyadmin_enterprise_permission');
				
				if (! $uda->add($post, $data)) {
					if ($uda->errmsg) {
						$this->message('error', $uda->errmsg);
					} else {
						$this->message('error', '添加失败');
					}
				}
				voa_h_cache::get_instance()->get('adminer', 'cyadmin', true);
				$this->message('success', '添加成功', $this->cpurl($this->_module, $this->_operation, 'list'));
			} catch (help_exception $h) {
				$this->message('error', '添加失败');
			} catch (Exception $e) {
				logger::error($e);
				$this->message('error', '添加失败');
			}
		}
		// 管理组列表
		$serv_group = &service::factory('voa_s_cyadmin_common_adminergroup');
		$group_list = $serv_group->fetch_all();

		// 获取销售主管
		$serv_adminer = &service::factory('voa_s_cyadmin_common_newadminer');
		$adminer_leader = $serv_adminer->list_by_conds(array('ca_job' => 1));
		$leader = array();
		if (!empty($adminer_leader)) {
			foreach ($adminer_leader as $k => $v) {
				$leader[$v['ca_id']] = $v['ca_realname'];
			}
			$this->view->set('leader', $leader); // 主管
		}

		//取除系统管理组以外的
		/* $group_list = array();
		foreach ($tmp as $val) {
			if ($val['cag_enable'] == 1) {
				$group_list[] = array('cag_id' => $val['cag_id'], 'cag_title' => $val['cag_title']);
			}
		} */
		//部门列表
		$this->view->set('list_url_base', $this->cpurl($this->_module, $this->_operation, 'list'));
		$this->view->set('form_url', $this->cpurl($this->_module, $this->_operation, 'add'));
		$this->view->set('group_list', $group_list);
		$this->view->set('job_list', $this->job);
		$this->output('cyadmin/enterprise/permission/add');

		return true;
	}

}
