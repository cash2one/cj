<?php
/**
 * 巡店(评分)配置
 * @author zhuxun37
 *
 */

class voa_c_admincp_office_inspect_config extends voa_c_admincp_office_inspect_base {
	// 配置的最大选项数
	protected $_max_opt = 5;

	public function execute() {

		$pid = (int)$this->request->get('pid');
		$this->view->set('pid', $pid);

		if ($pid) {
			$db = &service::factory('voa_s_oa_inspect_item');
			$current = $db->get($pid);
			$this->view->set('current', $current);
		}

		$act = $this->request->get('act');
		$acts = array('edit', 'delete', 'list');
		$act = $act && in_array($act, $acts) ? $act : 'list';
		$func = '_ac_'.$act;
		$this->$func();

		$this->view->set('defaultUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('pid' => $pid)));
		$this->view->set('editUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act' => 'edit')));
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act' => 'delete')));
		$this->view->set('baseUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->view->set('max_opt', $this->_max_opt);

		if ($act == 'edit') {
			$this->output('office/inspect/edit_config');
		} elseif('step' == $act) {
			$this->output('office/inspect/step_config');
		} else {
			$this->output('office/inspect/config');
		}
	}

	protected function _ac_list() {

		$condi = array('insi_state' => voa_d_oa_inspect_item::STATE_USING);
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

		$p2c = array();
		foreach ($list as $_v) {
			if (!isset($p2c[$_v['insi_parent_id']])) {
				$p2c[$_v['insi_parent_id']] = array();
			}

			$p2c[$_v['insi_parent_id']][$_v['insi_id']] = $_v['insi_id'];
		}

		$this->view->set('p2c', $p2c);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);
		$this->view->set('total', $total);
		$this->view->set('search', $search);
	}

	protected function _ac_delete() {

		$id = (int)$this->request->get('id');
		if (!empty($id)) {
			$serv_insi = new voa_s_oa_inspect_item();
			if ($serv_insi->list_by_conds(array('insi_parent_id' => $id, 'insi_state' => voa_d_oa_inspect_item::STATE_USING))) {
				$this->_error_message('该评分项下还有子评分项, 不能删除');
				return true;
			}

			$data['insi_state'] = voa_d_oa_inspect_item::STATE_UNUSED;
			$data['insi_status'] = voa_d_oa_inspect_item::STATUS_DELETE;
			$data['insi_id'] = $id;
			$this->_save($data);
		} else {
			$ids = $this->request->post('delete');
			if (!empty($ids)) {
				foreach ($ids as $val) {
					$data['insi_state'] = voa_d_oa_inspect_item::STATE_UNUSED;
					$data['insi_status'] = voa_d_oa_inspect_item::STATUS_DELETE;
					$data['insi_id'] = $val;
					$this->_save($data);
				}
			}
		}

		/** 更新地区和门店的缓存 */
		voa_h_cache::get_instance()->get('plugin.inspect.item', 'oa', true);
		voa_h_cache::get_instance()->get('plugin.inspect.option', 'oa', true);
		header('location: '.$this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array()));
		exit;
	}

	protected function _ac_edit() {

		$items = voa_h_cache::get_instance()->get('plugin.inspect.item', 'oa');
		$insi_parent_id = (int)$this->request->get('pid');
		$insi_id = (int)$this->request->get('id');
		$data = array();

		if ($this->request->post('submit')) {
			$data = $this->request->post('form');
			if ($insi_id) {
				$data['insi_id'] = $insi_id;
				if (!isset($items[$insi_id])) {
					echo json_encode(array('errcode' => '100', 'errmsg' => '配置信息错误, 请刷新重试'));
					exit;
				}

				$insi_parent_id = $items[$insi_id]['insi_parent_id'];
			}

			if ($insi_parent_id) {
				$data['insi_parent_id'] = $insi_parent_id;
				$score = (int)$data['insi_score'];
				$score = empty($score) ? 5 : $score;
				$avg = (int)($score / 5);
				if ($data['insi_score'] != $avg * 5) {
					echo json_encode(array('errcode' => '100', 'errmsg' => '分数配置错误, 值只能为 5 的倍数'));
					exit;
				}

				$data['insi_score'] = $score;
			}

			$data['insi_ordernum'] = (int)$data['insi_ordernum'];
			$data['insi_score'] = (int)$data['insi_score'];
			$this->_save($data);
			$insi_id = empty($insi_id) ? $data['insi_id'] : $insi_id;

			// 更新选项
			$ins_set = voa_h_cache::get_instance()->get('plugin.inspect.setting', 'oa');
			if (0 == $ins_set['score_rule_diy']) {
				$max_opt = $this->_max_opt;
				$options = (array)$this->request->post('options');
				$uda_opt_up = new voa_uda_frontend_inspect_option_update();
				foreach ($options as $_id => $_v) {
					if (0 >= $max_opt --) {
						break;
					}

					$opt = array();
					$_v = trim($_v);
					if (empty($_v)) {
						continue;
					}

					$uda_opt_up->execute(array('insi_id' => $insi_id, 'inso_id' => $_id, 'inso_optvalue' => $_v), $opt);
				}

				// 新选择
				$newopts = (array)$this->request->post('newopts');
				foreach ($newopts as $_v) {
					if (0 >= $max_opt --) {
						break;
					}

					$opt = array();
					$_v = trim($_v);
					if (empty($_v)) {
						continue;
					}

					$uda_opt_up->execute(array('insi_id' => $insi_id, 'inso_optvalue' => $_v), $opt);
				}

				// 其他选项作为未使用
				$serv_opt = new voa_s_oa_inspect_option();
				$serv_opt->update_by_conds(array('insi_id' => $insi_id, 'inso_updated<?' => startup_env::get('timestamp')), array('inso_state' => voa_d_oa_inspect_option::STATE_UNUSED));
				voa_h_cache::get_instance()->get('plugin.inspect.option', 'oa', true);
			}

			voa_h_cache::get_instance()->get('plugin.inspect.item', 'oa', true);
			echo json_encode(array('errcode' => '0', 'result' => array('insi_id' => $insi_id)));
			exit;
		} elseif ($insi_id) {
			$db = &service::factory('voa_s_oa_inspect_item');
			$data = $db->get($insi_id);
		}

		// 读取选项配置
		$uda_option = new voa_uda_frontend_inspect_option_list();
		$options = array();
		$uda_option->execute(array('insi_id' => $insi_id, 'inso_state' => voa_d_oa_inspect_option::STATE_USING), $options);

		if (empty($data) && !empty($insi_parent_id)) {
			$data = array(
				'insi_rules_title' => '评分标准',
				'insi_score_title' => '评分',
				'insi_score' => 5,
				'insi_hasselect' => 0,
				'insi_select_title' => '选项',
				'insi_hasatt' => 1,
				'insi_att_title' => '图片',
				'insi_hasfeedback' => 1,
				'insi_feedback_title' => '问题反馈'
			);
		}

		$this->view->set('insi_parent_id', $insi_parent_id);
		$this->view->set('options', $options);
		$this->view->set('form', $data);
	}

	protected function _save(&$data) {

		$db = &service::factory('voa_d_oa_inspect_item');
		if (!empty($data['insi_id'])) {
			$id = $data['insi_id'];
			unset($data['insi_id']);
			$db->update($id, $data);
		} else {
			!isset($data['insi_rules']) && $data['insi_rules'] = '';
			$data = $db->insert($data);
		}
	}

	protected function _list($condi) {

		// 每页显示数
		$condi['perpage'] = 300;
		$condi['page'] = $this->request->get('page');

		// 总数
		$uda = new voa_uda_frontend_inspect_item_list();
		$list = array();
		$uda->execute($condi, $list);
		// 分页显示
		$multi = '';
		$total = $uda->get_total();

		if (!$total) {
			// 如果无数据
			return array($total, $multi, $list);
		}

		// 分页配置
		$pager_options = array(
			'total_items' => $total,
			'per_page' => $uda->get_perpage(),
			'current_page' => $uda->get_page(),
			'show_total_items' => true,
		);
		$multi = pager::make_links($pager_options);
		// 引用结果，分页配置
		pager::resolve_options($pager_options);

		return array($total, $multi, $list);
	}

}
