<?php
/**
 * voa_c_admincp_office_namecard_list
 * 企业后台/微办公管理/微名片/名片列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_c_admincp_office_inspect_plan extends voa_c_admincp_office_inspect_base {


	public function execute() {

		$cache_config = voa_h_cache::get_instance()->get('plugin.inspect.setting', 'oa');
		$this->view->set('cache_config', $cache_config);

		// 取店铺配置
		$this->_shops = voa_h_cache::get_instance()->get('shop', 'oa');

		$act = $this->request->get('act');
		$acts = array(
			'getusers', 'getshoplist', 'getregionlist', 'execution', 'rollback', 'delete', 'edit', 'view'
		);
		$act = empty($act) || !in_array($act, $acts) ? 'list' : $act;
		$func = '_ac_'.$act;
		if (method_exists($this, $func)) {
			$this->$func();
		}

		$this->view->set('addUrlBase', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'edit')));
		$this->view->set('getUsersUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'getusers')));
		$this->view->set('getRegionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'getregionlist')));
		$this->view->set('getShopUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'getshoplist')));
		$this->view->set('listUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array()));
		$this->view->set('viewUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'view', 'it_id'=>'')));
		$this->view->set('executionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'execution', 'it_id'=>'')));
		$this->view->set('rollbackUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'rollback', 'it_id'=>'')));

		$this->view->set('editUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'edit', 'it_id'=>'')));
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'delete', 'it_id'=>'')));

		$this->view->set('shops', $this->_shops);

		if ($act == 'edit') {
			$this->output('office/inspect/edit_task');
		} elseif ($act == 'view') {
			$this->output('office/inspect/view_task');
		} else {
			$this->output('office/inspect/plan');
		}
	}

	private function __assign_uid(&$condi, &$search) {

		if (empty($search['it_assign_uid'])) {
			return false;
		}

		$dbmember = &service::factory('voa_s_oa_member');
		$condi['it_assign_uid'] = explode(',', $search['it_assign_uid']);
		$assign_users = $dbmember->fetch_all_by_ids($condi['it_assign_uid']);
		$search['it_assign_users'] = array();
		foreach ($assign_users as $item) {
			$search['it_assign_users'][] = $item['m_username'];
		}

		if (!empty($search['it_assign_users'])) {
			$search['it_assign_users'] = implode(',', $search['it_assign_users']);
		}

		return true;
	}

	private function __submit_uid(&$condi, &$search) {

		if (empty($search['it_submit_uid'])) {
			return false;
		}

		$dbmember = &service::factory('voa_s_oa_member');
		$condi['it_submit_uid'] = explode(',', $search['it_submit_uid']);
		$submit_users = $dbmember->fetch_all_by_ids($condi['it_submit_uid']);
		$search['it_submit_users'] = array();
		foreach ($submit_users as $item) {
			$search['it_submit_users'][] = $item['m_username'];
		}

		if (!empty($search['it_submit_users'])) {
			$search['it_submit_users'] = implode(',', $search['it_submit_users']);
		}

		return true;
	}

	/**
	 * 获取列表
	 * @return boolean
	 */
	protected function _ac_list() {

		$condi = array();
		$post = array();
		if ($this->request->post('submit')) {
			$post = $this->request->postx();
		} elseif ($this->request->get('submit')) {
			$post = $this->request->getx();
		}

		if ($post) {
			$dbmember = &service::factory('voa_s_oa_member');
			$search = $post['search'];
			$this->__assign_uid($condi, $search);
			$this->__submit_uid($condi, $search);

			if ($search['it_start_date']) {
				$condi['start_date'] = array(rstrtotime($search['it_start_date']) - 1, '>');
			}

			if ($search['it_end_date']) {
				$condi['end_date'] = array(rstrtotime($search['it_end_date']) + 1, '<');
			}

			$this->view->set('search', $search);
		}

		list($total, $multi, $list) = $this->_list($condi);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);
		$this->view->set('total', $total);
		return true;
	}

	/**
	 * 查看任务详情
	 * @return boolean
	 */
	protected function _ac_view() {

		$it_id = (int)$this->request->get('it_id');
		$uda_task = new voa_uda_frontend_inspect_tasks_get();
		$data = array();
		$uda_task->execute(array('it_id' => $it_id), $data);

		$this->_task_format($data);
		$items = array();
		$uda_ins_list = new voa_uda_frontend_inspect_list();
		$uda_ins_list->execute(array('it_id' => $it_id), $items);
		foreach ($items as &$_v) {
			if (voa_d_oa_inspect::TYPE_WAITING == $_v['ins_type']) {
				$_v['ins_type_text'] = "<label class='label label-info'>".voa_d_oa_inspect::TYPE_WAITING_TEXT."</label>";
			} elseif (voa_d_oa_inspect::TYPE_DONE == $_v['ins_type']) {
				$_v['ins_type_text'] = "<label class='label label-success'>".voa_d_oa_inspect::TYPE_DONE_TEXT."</label>";
			} elseif (voa_d_oa_inspect::TYPE_DOING == $_v['ins_type']) {
				$_v['ins_type_text'] = "<label class='label label-primary'>".voa_d_oa_inspect::TYPE_DOING_TEXT."</label>";
			}
		}

		$data['it_csp_id_list'] = $items;
		$this->view->set('data', $data);
		return true;
	}

	/**
	 * 编辑
	 * @return boolean
	 */
	protected function _ac_edit() {

		$it_id = (int)$this->request->get('it_id');
		$data = array();

		if ($this->request->post('submit')) {
			$data = $this->request->post('item');
			if ($it_id) {
				$data['it_id'] = $it_id;
			}

			$this->_save_task($data);
			echo json_encode(array('result' => array('status' => '100')));
			exit;
		} elseif ($it_id) {
			$uda_task = new voa_uda_frontend_inspect_tasks_get();
			$data = array();
			$uda_task->execute(array('it_id' => $it_id), $data);
			$this->_task_format($data);
		}

		$this->view->set('region', $this->_get_region_list());
		$this->view->set('data', $data);
		return true;
	}

	/**
	 * 删除任务
	 */
	protected function _ac_delete() {

		$id = (int)$this->request->get('it_id');
		if (!empty($id)) {
			$data['it_status'] = voa_d_oa_inspect_tasks::STATUS_DELETE;
			$data['it_id'] = $id;
			$data['it_deleted'] = time();
			$this->_save_task($data);
		} else {
			$ids = $this->request->post('delete');
			if (!empty($ids)) {
				foreach ($ids as $val) {
					$data['it_status'] = voa_d_oa_inspect_tasks::STATUS_DELETE;
					$data['it_id'] = $val;
					$data['it_deleted'] = time();
					$this->_save_task($data);
				}
			}
		}

		header('location: '.$this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array()));
		exit;
	}

	/**
	 * 回退
	 */
	protected function _ac_rollback() {

		$id = (int)$this->request->get('it_id');
		if (!empty($id)) {
			$this->_rollback($id);
		}

		header('location: '.$this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array()));
		exit;
	}

	/**
	 * 执行
	 */
	protected function _ac_execution() {

		$id = (int)$this->request->get('it_id');
		if (!empty($id)) {
			$this->_execution($id);
		}

		header('location: '.$this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array()));
		exit;
	}

	/**
	 * 获取地区列表
	 */
	protected function _ac_getregionlist() {

		$parent = $this->request->post('parent');
		echo json_encode($this->_get_region_list($parent));
		exit;
	}

	protected function _rollback($id) {

		$uda_task = new voa_uda_frontend_inspect_tasks_get();
		$data = array();
		$uda_task->execute(array('it_id' => $id), $data);
		if ($data['it_csp_id_list'] && $data['it_assign_uid'] && $data['it_execution_status'] == voa_d_oa_inspect_tasks::EXE_STATUS_DOING) {
			$uda_ins = new voa_s_oa_inspect();
			$uda_ins->delete_by_conds(array('it_id' => $data['it_id']));

			$uda_task_up = new voa_uda_frontend_inspect_tasks_update();
			$task = array();
			$uda_task_up->execute(array(
				'it_id' => $data['it_id'],
				'it_execution_status'=>voa_d_oa_inspect_tasks::EXE_STATUS_ROLLBACK
			), $task);

		}
	}

	protected function _execution($id) {

		$uda_task = new voa_uda_frontend_inspect_tasks_get();
		$data = array();
		$uda_task->execute(array('it_id' => $id), $data);
		if ($data['it_csp_id_list'] && $data['it_assign_uid'] && $data ['it_execution_status'] != voa_d_oa_inspect_tasks::EXE_STATUS_DOING) {

			/** edit by zhuxun37 */
			$ins_insert = &uda::factory('voa_uda_frontend_inspect_tasks_run');
			$result = array();
			if (!$ins_insert->execute(array('task' => $data), $result)) {
				return false;
			}

			$update = array();
			$update['it_execution_status'] = voa_d_oa_inspect_tasks::EXE_STATUS_DOING;
			$update['it_last_execution_time'] = startup_env::get('timestamp');
			$update['it_id'] = $id;
			$uda_task_up = new voa_uda_frontend_inspect_tasks_update();
			$task = array();
			$uda_task_up->execute($update, $task);

			return true;
		}

	}

	protected function _task_format(&$data) {

		if (!empty($data['it_id'])) {
			$condi = array('it_id'=>$data['it_id'], 'ins_status'=>3);
			if ($data['it_start_date']) {
				$data['it_start_date'] = date('m/d/Y', $data['it_start_date']);
			} else {
				$data['it_start_date'] = '';
			}

			if ($data['it_end_date']) {
				$data['it_end_date'] = date('m/d/Y', $data['it_end_date']);
			} else {
				$data['it_end_date'] = '';
			}

			if (!empty($data['it_repeat_frequency'])) {
				if ($data['it_repeat_frequency'] != 'no') {
					$freq = explode('_', $data['it_repeat_frequency']);
					if (count($freq) == 2) {
						$data['it_repeat_frequency'] = array();
						$data['it_repeat_frequency'][$freq[0]] = $freq[1];
					}
				} else {
					$data['it_repeat_frequency'] = array();
					$data['it_repeat_frequency']['no'] = 1;
				}
			}

			if ($data['it_execution_status'] == voa_d_oa_inspect_tasks::EXE_STATUS_DRAFT) {
				$data['it_execution_status_text'] = "<label class='label label-info'>".'未开始'."</label>";
			} elseif ($data['it_execution_status'] == voa_d_oa_inspect_tasks::EXE_STATUS_DOING) {
				$data['it_execution_status_text'] = "<label class='label label-primary'>".'进行中'."</label>";
			} elseif ($data['it_execution_status'] == voa_d_oa_inspect_tasks::EXE_STATUS_ROLLBACK) {
				$data['it_execution_status_text'] = "<label class='label label-danger'>".'已撤消'."</label>";
			}

			$dbmember = &service::factory('voa_s_oa_member');
			if (!empty($data['it_submit_uid'])) {
				$member = $dbmember->fetch($data['it_submit_uid']);
				if (!empty($member)) {
					$data['it_submit_username'] = $member['m_username'];
				}
			}

			if (!empty($data['it_csp_id_list'])) {
				$shopids = explode(',', $data['it_csp_id_list']);
				$data['it_csp_id_list'] = array();
				$uda_shop_list = new voa_uda_frontend_common_shop_list();
				$uda_shop_list->execute(array('csp_id' => $shopids), $data['it_csp_id_list']);
				$data['it_csp_id_list_total'] = count($data['it_csp_id_list']);
			}

			if ($data['it_assign_uid']) {
				$assign_users = $dbmember->fetch_all_by_ids(explode(',', $data['it_assign_uid']));
				$data['it_assign_users'] = array();
				foreach ($assign_users as $item) {
					$data['it_assign_users'][] = $item['m_username'];
				}

				if (!empty($data['it_assign_users'])) {
					$data['it_assign_users'] = implode(',', $data['it_assign_users']);
				}
			}

		}
	}

	protected function _list($condi) {

		// 每页显示数
		$condi['perpage'] = 20;
		$condi['page'] = $this->request->get('page');
		// 总数
		$uda_task = new voa_uda_frontend_inspect_tasks_list();
		$list = array();
		$uda_task->execute($condi, $list);

		// 分页显示
		$multi = '';
		$total = $uda_task->get_total();
		if (!$total) {
			// 如果无数据
			return array($total, $multi, $list);
		}

		// 分页配置
		$pager_options = array(
			'total_items' => $total,
			'per_page' => $uda_task->get_perpage(),
			'current_page' => $uda_task->get_page(),
			'show_total_items' => true
		);
		$multi = pager::make_links($pager_options);
		// 引用结果，分页配置
		pager::resolve_options($pager_options);

		// 格式化列表输出
		foreach ($list as &$_ca) {
			$this->_task_format($_ca);
		}

		return array($total, $multi, $list);
	}

	/**
	 * 编辑巡店任务
	 * @param array $data 巡店任务信息
	 */
	protected function _save_task($data) {

		if (empty($data['it_repeat_frequency'])) {
			$data['it_repeat_frequency'] = 'no';
		} elseif ($data['it_repeat_frequency'] == 'mon') {
			$data['it_repeat_frequency'] = 'mon_'.$data['it_repeat_date_mon'];
		} elseif ($data['it_repeat_frequency'] == 'week') {
			$data['it_repeat_frequency'] = 'week_'.$data['it_repeat_date_week'];
		} elseif ($data['it_repeat_frequency'] == 'day') {
			$data['it_repeat_frequency'] = 'day_'.$data['it_repeat_date_day'];
		} else {
			$data['it_repeat_frequency'] = 'no';
		}

		unset($data['it_repeat_date_day']);
		unset($data['it_repeat_date_week']);
		unset($data['it_repeat_date_mon']);
		if (!empty($data['it_start_date'])) {
			$data['it_start_date'] = strtotime($data['it_start_date']);
		}

		if (!empty($data['it_end_date'])) {
			$data['it_end_date'] = strtotime($data['it_end_date']);
		}

		if (!empty($data['it_csp_id_list'])) {
			$data['it_csp_id_list'] = implode(',', $data['it_csp_id_list']);
		} else {
			$data['it_csp_id_list'] = '';
		}

		if (!empty($this->_user['m_uid'])) {
			$data['it_submit_uid'] = $this->_user['m_uid'];
		}

		$serv_task = new voa_s_oa_inspect_tasks();
		if (!empty($data['it_id'])) {
			$uda_task_get = new voa_uda_frontend_inspect_tasks_get();
			$org = array();
			$uda_task_get->execute(array('it_id' => $data['it_id']), $org);
			if (!empty($data)) {
				$id = $data['it_id'];
				unset($data['it_id']);
				if (isset($data['it_execution_status']) && $org['it_execution_status'] != $data['it_execution_status']) {
					if ($data['it_execution_status'] == voa_d_oa_inspect_tasks::EXE_STATUS_DOING) {
						$this->_execution($id);
					} elseif ($data['it_execution_status'] == voa_d_oa_inspect_tasks::EXE_STATUS_ROLLBACK
							&& $org['it_execution_status'] != voa_d_oa_inspect_tasks::EXE_STATUS_DRAFT) {
						$this->_rollback($id);
					}
				}

				$serv_task->update($id, $data);
			}
		} else {
			$data['it_description'] = empty($data['it_description']) ? '' : $data['it_description'];
			$exe_status = $data['it_execution_status'];
			if ($exe_status == voa_d_oa_inspect_tasks::EXE_STATUS_DOING) {
				$data['it_execution_status'] = voa_d_oa_inspect_tasks::EXE_STATUS_DRAFT;
			}

			$data = $serv_task->insert($data);
			if ($exe_status == voa_d_oa_inspect_tasks::EXE_STATUS_DOING) {
				$this->_execution($data['it_id']);
			}
		}
	}

	/**
	 * 获取门店列表
	 * @return boolean
	 */
	protected function _ac_getshoplist() {

		$cr_id = $this->request->post('districts');
		$condi = array();
		if (empty($cr_id)) {
			return false;
		}

		$condi['cr_id'] = explode(',', $cr_id);
		$list = array();
		$uda = new voa_uda_frontend_common_shop_list();
		$uda->execute($condi, $list);
		echo json_encode($list);
		exit;
	}

	/**
	 * (admincp/base) 根据用户名读取用户信息
	 * array(common_adminer,common_adminergroup)
	 * @param string $username 用户名
	 */
	protected function _ac_getusers() {

		$username = $this->request->post('kw');
		$newConditions['m_username'] = array("%$username%", 'like');
		$db = &service::factory('voa_s_oa_member');
		$tmp = $db->fetch_all_by_conditions($newConditions);
		$list = $this->_member_list_format($tmp);
		$newtmp = array();
		foreach ($list as $key => $item) {
			$newtmp[] = $item;
		}

		echo json_encode($newtmp);
		exit;
	}

}
