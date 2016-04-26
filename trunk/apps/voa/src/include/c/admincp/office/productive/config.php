<?php

class voa_c_admincp_office_productive_config extends voa_c_admincp_office_productive_base {

	public function execute() {
		$pid = (int)$this->request->get('pid');
		$this->view->set('pid', $pid);
		$act = $this->request->get('act');
		if ($pid) {
			$db = &service::factory('voa_s_oa_productive_item');
			$current = $db->fetch_by_id($pid);
			$this->view->set('current', $current);
		}
		if ($act == 'edit') {
			$id = (int)$this->request->get('id');
			$data = array();

			if ($this->request->post('submit')) {
				$data = $this->request->post('form');
				if ($pid) {
					$data['pti_parent_id'] = $pid;
				}
				if ($id) {
					$data['pti_id'] = $id;
				}

				$this->_save($data);
				voa_h_cache::get_instance()->get('plugin.productive.item', 'oa', true);
				echo json_encode(array('result'=>array('status'=>'100')));
				exit;
			} elseif ($id) {

				$db = &service::factory('voa_s_oa_productive_item');
				$data = $db->fetch_by_id($id);
			}
			/** 更新地区和门店的缓存 */
			$this->view->set('form', $data);

		} elseif ($act == 'delete') {
			$id = (int)$this->request->get('id');
			if (!empty($id)) {
				$data['pti_status'] = voa_d_oa_productive_item::STATUS_REMOVE;
				$data['pti_id'] = $id;
				$data['pti_deleted'] = time();
				$this->_save($data);
			} else {
				$ids = $this->request->post('delete');
				if (!empty($ids)) {
					foreach ($ids as $val) {
						$data['pti_status'] = voa_d_oa_productive_item::STATUS_REMOVE;
						$data['pti_id'] = $val;
						$data['pti_deleted'] = time();
						$this->_save($data);
					}

				}
			}
			/** 更新地区和门店的缓存 */
			voa_h_cache::get_instance()->get('plugin.productive.item', 'oa', true);
			header('location: '.$this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array()));

		} else {
			$condi = array('pti_parent_id'=>$pid);
			$search = array();
			$post = array();
			if ($this->request->post('submit')) {
				$post = $this->request->postx();
			} elseif ($this->request->get('submit')) {
				$post = $this->request->getx();
			}
			if ($post) {
				$search = $post['search'];
			}
			list($total, $multi, $list) = $this->_list($condi);
			$this->view->set('multi', $multi);
			$this->view->set('list', $list);
			$this->view->set('total', $total);

			$this->view->set('search', $search);
		}
		$this->view->set('subListUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('pid'=>'')));

		$this->view->set('defaultUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('pid'=>$pid)));
		$this->view->set('editUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'edit', 'pid'=>$pid, 'id'=>'')));
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'delete', 'id'=>'')));


		if ($act == 'edit') {
			$this->output('office/productive/edit_config');
		} else {
			$this->output('office/productive/config');

		}
	}
	protected function _save($data) {
		$db = &service::factory('voa_d_oa_productive_item');

		if (!empty($data['pti_id'])) {
			$id = $data['pti_id'];
			unset($data['pti_id']);
			$db->update($data, array('pti_id'=>$id));

		} else {
			$data['pti_status'] = 1;
			$data['pti_created'] = time();

			$db->insert($data);
		}
	}
	protected function _list($condi){
		// 每页显示数
		$perpage = 20;

		// 总数
		$db = &service::factory('voa_s_oa_productive_item');
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


		/**
		 * 根据条件计算总数
		 * @param  array  $conditions
		 *  $conditions = array(
		 *      'field1' => '查询条件', // 运算符为 =
		 *      'field2' => array('查询条件', '查询运算符'),
		 *      'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
		 *      ...
		 *  );
		 * @return number
		*/

		// 管理员列表
		$list = $db->fetch_by_conditions($condi, $pager_options['start'], $pager_options['per_page']);


		// 格式化列表输出

		foreach ($list as &$_ca) {
			$_ca['pti_created'] = rgmdate($_ca['pti_created'], 'Y-m-d H:i');
			$_ca['pti_updated'] = rgmdate($_ca['pti_updated'], 'Y-m-d H:i');
		}

		return array($total, $multi, $list);
	}



}
