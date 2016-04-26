<?php
/**
 * voa_c_admincp_office_namecard_list
 * 企业后台/微办公管理/微名片/名片列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_productive_plan extends voa_c_admincp_office_productive_base {

	protected $_member_list = array();
	protected $_job_list = array();
	protected $_company_list = array();
	protected $_folder_list = array();
	protected $erron = '';
	protected $error = '';

	public function execute() {
		$dbtasks = &service::factory('voa_s_oa_productive_tasks');
		$cache_config = voa_h_cache::get_instance()->get('plugin.productive.setting', 'oa');
		$this->view->set('cache_config', $cache_config);
		$act = $this->request->get('act');
		if ($act == 'edit') {
			$ptt_id = (int)$this->request->get('ptt_id');
			$data = array();

			if ($this->request->post('submit')) {
				$data = $this->request->post('item');
				if ($ptt_id) {
					$data['ptt_id'] = $ptt_id;
				}
				$this->_save_task($data);
				echo json_encode(array('result'=>array('status'=>'100')));
				exit;
			} elseif ($ptt_id) {
				$data = $dbtasks->fetch_by_id($ptt_id);

				$this->_task_format($data);
			}
			//echo "<pre>";

			$this->view->set('region', $this->_get_region_list());
			$this->view->set('data', $data);
		} elseif ($act == 'view') {
			$ptt_id = (int)$this->request->get('ptt_id');
			$data = $dbtasks->fetch_by_id($ptt_id);
			$this->_task_format($data);
			$items = array();
			$dbproductive = &service::factory('voa_s_oa_productive');
			foreach ($data['ptt_csp_id_list'] as $val) {
				$item = $dbproductive->fetch_by_conditions(array('csp_id'=>$val['csp_id'], 'ptt_id'=>$ptt_id));
				if (!empty($item)) {
					$item = $item[array_rand($item)];
					if (voa_d_oa_productive::STATUS_WAITING == $item['pt_status']) {
						$val['pt_status_text'] = "<label class='label label-info'>".voa_d_oa_productive::STATUS_WAITING_TEXT."</label>";
					} elseif (voa_d_oa_productive::STATUS_DONE == $item['pt_status']) {
						$val['pt_status_text'] = "<label class='label label-success'>".voa_d_oa_productive::STATUS_DONE_TEXT."</label>";
					} elseif (voa_d_oa_productive::STATUS_DOING == $item['pt_status']) {
						$val['pt_status_text'] = "<label class='label label-primary'>".voa_d_oa_productive::STATUS_DOING_TEXT."</label>";
					} elseif (voa_d_oa_productive::STATUS_REMOVE == $item['pt_status'] ) {
						$val['pt_status_text'] = "<label class='label label-danger'>".voa_d_oa_productive::STATUS_REMOVE_TEXT."</label>";
					}
					$items[] = $val;
				}
			}
			$data['ptt_csp_id_list'] = $items;
			$this->view->set('data', $data);

		} elseif ($act == 'getusers') {
			$kw = $this->request->post('kw');
			$list = $this->_get_users($kw);
			echo json_encode($list);
			exit;

		} elseif ($act == 'getregionlist') {

			$parent = $this->request->post('parent');
			echo json_encode($this->_get_region_list($parent));

			exit;
		} elseif ($act == 'getshoplist') {
			$districts = $this->request->post('districts');
			echo json_encode($this->_get_shop_list($districts));

			exit;
		} elseif ($act == 'execution') {
			$id = ( int ) $this->request->get ( 'ptt_id' );
			if (! empty ( $id )) {
				$this->_execution($id);
			}
			header ( 'location: ' . $this->cpurl ( $this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array () ) );
		} elseif ($act == 'rollback') {
			$id = ( int ) $this->request->get ( 'ptt_id' );
			if (! empty ( $id )) {
				$this->_rollback( $id );
			}
			header ( 'location: ' . $this->cpurl ( $this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array () ) );

		} elseif ($act == 'delete') {
			$id = (int)$this->request->get('ptt_id');
			if (!empty($id)) {
				$data['ptt_status'] = voa_d_oa_productive_tasks::STATUS_REMOVE;
				$data['ptt_id'] = $id;
				$data['ptt_deleted'] = time();
				$this->_save_task($data);
			} else {
				$ids = $this->request->post('delete');
				if (!empty($ids)) {
					foreach ($ids as $val) {
						$data['ptt_status'] = voa_d_oa_productive_tasks::STATUS_REMOVE;
						$data['ptt_id'] = $val;
						$data['ptt_deleted'] = time();
						$this->_save_task($data);
					}

				}
			}
			header('location: '.$this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array()));

		} else {
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
				if ($search['ptt_assign_uid']) {
					$condi['ptt_assign_uid'] = array($search['ptt_assign_uid'], 'in');

					$assign_users = $dbmember->fetch_all_by_ids( explode(',', $search['ptt_assign_uid']));
					$search['ptt_assign_users'] = array();
					foreach ($assign_users as $item) {
						$search['ptt_assign_users'][] = $item['m_username'];
					}
					if (!empty($search['ptt_assign_users'])) {
						$search['ptt_assign_users'] = implode(',', $search['ptt_assign_users']);
					}
				}
				if ($search['ptt_submit_uid']) {
					$condi['ptt_submit_uid'] = array($search['ptt_submit_uid'], 'in');

					$submit_users = $dbmember->fetch_all_by_ids(explode(',', $search['ptt_submit_uid']));
					$search['ptt_submit_users'] = array();
					foreach ($submit_users as $item) {
						$search['ptt_submit_users'][] = $item['m_username'];
					}
					if (!empty($search['ptt_submit_users'])) {
						$search['ptt_submit_users'] = implode(',', $search['ptt_submit_users']);
					}
				}
				if ($search['ptt_start_date']) {
					$condi['ptt_start_date'] = array(rstrtotime($search['ptt_start_date'])-1, '>');
				}
				if ($search['ptt_end_date']) {
					$condi['ptt_end_date'] = array(rstrtotime($search['ptt_end_date'])+1, '<');
				}
				$this->view->set('search', $search);

			}

			list($total, $multi, $list) = $this->_list($condi);


		}

		$this->view->set('addUrlBase', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'edit')));
		$this->view->set('getUsersUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'getusers')));
		$this->view->set('getRegionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'getregionlist')));
		$this->view->set('getShopUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'getshoplist')));
		$this->view->set('listUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array()));
		$this->view->set('viewUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'view', 'ptt_id'=>'')));
		$this->view->set('executionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'execution', 'ptt_id'=>'')));
		$this->view->set('rollbackUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'rollback', 'ptt_id'=>'')));

		$this->view->set('editUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'edit', 'ptt_id'=>'')));
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'delete', 'ptt_id'=>'')));


		if ($act == 'edit') {
			$this->output('office/productive/edit_task');
		} elseif ($act == 'view') {
			$this->output('office/productive/view_task');
		} else {
			$this->view->set('multi', $multi);
			$this->view->set('list', $list);
			$this->view->set('total', $total);
			$this->output('office/productive/plan');
		}
	}
	protected function _rollback($id) {
		$db = &service::factory('voa_s_oa_productive_tasks');
		$data = $db->fetch_by_id($id);
		if ($data['ptt_csp_id_list'] && $data['ptt_assign_uid'] && $data ['ptt_execution_status'] == voa_d_oa_productive_tasks::EXE_STATUS_DOING) {
			$item = array();
			$condi = array();
			$condi['ptt_id'] = $data['ptt_id'];
			$item['pt_status'] = voa_d_oa_productive::STATUS_REMOVE;
			$dbproductive = &service::factory('voa_s_oa_productive');
			$dbproductivetasks = &service::factory('voa_s_oa_productive_tasks');
			$dbproductive->update($item, $condi);
			$dbproductivetasks->update(
					array('ptt_execution_status'=>voa_d_oa_productive_tasks::EXE_STATUS_ROLLBACK),
					$condi);

		}
	}

	protected function _execution($id) {

		$db = &service::factory('voa_s_oa_productive_tasks');
		$data = $db->fetch_by_id($id);
		if ($data['ptt_csp_id_list'] && $data['ptt_assign_uid'] && $data ['ptt_execution_status'] != voa_d_oa_productive_tasks::EXE_STATUS_DOING) {

			/** edit by zhuxun37 */
			$ins_insert = &uda::factory('voa_uda_frontend_productive_insert');
			if (!$ins_insert->run_task($data)) {
				return false;
			}

			$update = array();
			$update ['ptt_execution_status'] = voa_d_oa_productive_tasks::EXE_STATUS_DOING;
			$update['ptt_last_execution_time'] = startup_env::get('timestamp');
			$condi ['ptt_id'] = $id;
			$db->update($update, $condi);

			return true;
			/**$csp_id_list = explode(',', $data['ptt_csp_id_list']);
			if (!empty($csp_id_list)) {
				$update = array();
				$update ['ptt_execution_status'] = voa_d_oa_productive_tasks::EXE_STATUS_DOING;
				$condi ['ptt_id'] = $id;
				$this->_service_single('productive_tasks', $this->_module_plugin_id, 'update', $update, $condi);
				$item = array();
				$member = $this->_service_single('member', $this->_module_plugin_id, 'fetch', $data['ptt_assign_uid']);
				$item['m_username'] = $member['m_username'];
				$item['ptt_id'] = $data['ptt_id'];
				$item['m_uid'] = $data['ptt_assign_uid'];
				$item['pt_status'] = voa_d_oa_productive::STATUS_WAITING;

				foreach ($csp_id_list as $id) {
					$item['csp_id'] = $id;
					$data = $this->_service_single('productive', $this->_module_plugin_id, 'insert', $item);
				}
			}*/
		}

	}
	protected function _task_format(&$data) {
		if (!empty($data['ptt_id'])) {
			$condi = array('ptt_id'=>$data['ptt_id'], 'pt_status'=>3);
			if ($data['ptt_start_date']) {
				$data['ptt_start_date'] = rgmdate($data['ptt_start_date'], 'Y-m-d');
			} else {
				$data['ptt_start_date'] = '';
			}
			if ($data['ptt_end_date']) {
				$data['ptt_end_date'] = rgmdate($data['ptt_end_date'], 'Y-m-d');
			} else {
				$data['ptt_end_date'] = '';
			}
			if (!empty($data['ptt_repeat_frequency'])) {
				if ($data['ptt_repeat_frequency'] != 'no') {
					$freq = explode('_', $data['ptt_repeat_frequency']);
					if (count($freq) == 2) {
						$data['ptt_repeat_frequency'] = array();
						$data['ptt_repeat_frequency'][$freq[0]] = $freq[1];
					}
				} else {
					$data['ptt_repeat_frequency'] = array();
					$data['ptt_repeat_frequency']['no'] = 1;
				}
			}
			if ($data['ptt_execution_status'] == voa_d_oa_productive_tasks::EXE_STATUS_DRAFT) {
				$data['ptt_execution_status_text'] = "<label class='label label-info'>".'未开始'."</label>";
			} elseif ($data['ptt_execution_status'] == voa_d_oa_productive_tasks::EXE_STATUS_DOING) {
				$data['ptt_execution_status_text'] = "<label class='label label-primary'>".'进行中'."</label>";
			} elseif ($data['ptt_execution_status'] == voa_d_oa_productive_tasks::EXE_STATUS_ROLLBACK) {
				$data['ptt_execution_status_text'] = "<label class='label label-danger'>".'已撤消'."</label>";
			}

			if (!empty($data['ptt_submit_uid'])) {

				$db = &service::factory('voa_s_oa_member');
				$member = $db->fetch($data['ptt_submit_uid']);
				if (!empty($member)) {
					$data['ptt_submit_username'] = $member['m_username'];
				}
			}
			if (!empty($data['ptt_csp_id_list'])) {
				$shopid = explode(',', $data['ptt_csp_id_list']);
				$data['ptt_csp_id_list'] = array();
				$db = &service::factory('voa_s_oa_common_shop');
				foreach ($shopid as $id) {
					$shop = $db->fetch_by_id($id);

					if (!empty($shop)) {
						$data['ptt_csp_id_list'][] = $shop;
					}
				}
				$data['ptt_csp_id_list_total'] = count($data['ptt_csp_id_list']);

			}
			if ($data['ptt_assign_uid']) {
				$db = &service::factory('voa_s_oa_member');
				$assign_users = $db->fetch_all_by_ids(explode(',', $data['ptt_assign_uid']));
				$data['ptt_assign_users'] = array();
				foreach ($assign_users as $item) {
					$data['ptt_assign_users'][] = $item['m_username'];
				}
				if (!empty($data['ptt_assign_users'])) {
					$data['ptt_assign_users'] = implode(',', $data['ptt_assign_users']);
				}
			}

		}
	}

	protected function _list($condi){
		// 每页显示数
		$perpage = 20;

		// 总数
		$db = &service::factory('voa_s_oa_productive_tasks');
		$total = $db->count_by_conditions($condi);
		// 分页显示
		$multi = '';
		// 管理员列表
		$list = array();

		if (!$total) {
			// 如果无数据
			return array($total, $multi, $list);
		}

		// 分页配置
		$pager_options = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true,
		);
		$multi = pager::make_links($pager_options);
		// 引用结果，分页配置
		pager::resolve_options($pager_options);

		// 管理员列表
		$list = $db->fetch_by_conditions($condi, $pager_options['start'], $pager_options['per_page']);


		// 格式化列表输出

		foreach ($list as &$_ca) {
			$this->_task_format($_ca);
		}

		return array($total, $multi, $list);
	}

	protected function _save_task($data) {

		if ($data['ptt_repeat_frequency'] == 'mon') {
			$data['ptt_repeat_frequency'] = 'mon_'.$data['ptt_repeat_date_mon'];
		} elseif ($data['ptt_repeat_frequency'] == 'week') {
			$data['ptt_repeat_frequency'] = 'week_'.$data['ptt_repeat_date_week'];
		} elseif ($data['ptt_repeat_frequency'] == 'day') {
			$data['ptt_repeat_frequency'] = 'day_'.$data['ptt_repeat_date_day'];
		} else {
			$data['ptt_repeat_frequency'] = 'no';
		}
		unset($data['ptt_repeat_date_day']);
		unset($data['ptt_repeat_date_week']);
		unset($data['ptt_repeat_date_mon']);
		if (!empty($data['ptt_start_date'])) {
			$data['ptt_start_date'] = rstrtotime($data['ptt_start_date']);
		}
		if (!empty($data['ptt_end_date'])) {
			$data['ptt_end_date'] = rstrtotime($data['ptt_end_date']);
		}
		if (!empty($data['ptt_csp_id_list'])) {
			$data['ptt_csp_id_list'] = implode(',', $data['ptt_csp_id_list']);
		} else {
			$data['ptt_csp_id_list'] = '';
		}
		if (!empty($this->_user['m_uid'])) {
			$data['ptt_submit_uid'] = $this->_user['m_uid'];
		}
		$db = &service::factory('voa_s_oa_productive_tasks');
		if (!empty($data['ptt_id'])) {

			$org = $db->fetch_by_id($data['ptt_id']);
			if (!empty($data)) {
				$id = $data['ptt_id'];
				unset($data['ptt_id']);
				if ($org['ptt_execution_status'] != $data['ptt_execution_status']) {
					if ($data['ptt_execution_status'] == voa_d_oa_productive_tasks::EXE_STATUS_DOING) {
						$this->_execution($id);
					} elseif ($data['ptt_execution_status'] == voa_d_oa_productive_tasks::EXE_STATUS_ROLLBACK &&
							$org['ptt_execution_status'] != voa_d_oa_productive_tasks::EXE_STATUS_DRAFT) {
						$this->_rollback($id);
					}
				}
				$db->update($data, array('ptt_id'=>$id));
			}

		} else {

			$id = $db->insert($data, true);
			if ($data['ptt_execution_status'] == voa_d_oa_productive_tasks::EXE_STATUS_DOING) {
				$this->_execution($id);
			}
		}


	}

	protected function _get_shop_list($cr_id = null) {
		$newConditions = array();
		if (empty($cr_id)) {
			return false;
		}
		$newConditions['cr_id'] = array(explode(',', $cr_id), 'in');

		$db = &service::factory('voa_s_oa_common_shop');
		$tmp	=	$db->fetch_by_conditions($newConditions);

		return $tmp;
	}

	/**
	 * (admincp/base) 根据用户名读取用户信息
	 * array(common_adminer,common_adminergroup)
	 * @param string $username 用户名
	 */
	protected function _get_users($username) {
		$newConditions['m_username'] = array("%$username%", 'like');
		$db = &service::factory('voa_s_oa_member');
		$tmp	=	$db->fetch_all_by_conditions($newConditions);

		$list	=	$this->_member_list_format($tmp);
		$newtmp = array();
		foreach ($list as $key => $item) {
			$newtmp[] = $item;
		}
		return $newtmp;
	}

}
