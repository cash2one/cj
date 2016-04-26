<?php

/**
 * voa_c_admincp_office_express_list
 * 企业后台/快递助手/快递列表
 * Create By gaosong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_express_list extends voa_c_admincp_office_express_base {

	public function execute() {
		// 读快递助手配置缓存
		$p_sets = voa_h_cache::get_instance()->get('plugin.express.setting', 'oa');

		$ac = $this->request->get('ac');
		// 删除操作
		if ($ac == 'delete') {
			$delete = $this->request->post('delete');
			$eid = rintval($this->request->get('eid'));
			$this->_delete($delete, $eid);
			return true;
		}

		// 搜索条件
		$conds = $this->request->getx();
		$issearch = $this->request->get('issearch');
		list($total, $multi, $list) = $this->_search_express($conds, $issearch);

		$this->view->set('total', $total);
		$this->view->set('list', $list);
		$this->view->set('multi', $multi);
		$this->view->set('searchBy', $conds);
		// 话题详情url
		$this->view->set('viewUrlBase', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('eid' => '')));
		// 删除话题url
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id, array('ac' => 'delete', 'eid' => '')));
		// 查询话题url
		$this->view->set('searchActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->output('office/express/list');
	}

	/**
	 * 搜索话题记录
	 *
	 * @param number $cp_pluginid
	 * @param boolean $issearch
	 * @param array $searchDefault
	 * @param number $perpage
	 */
	protected function _search_express($conds, $issearch) {

		$uda_list = &uda::factory('voa_uda_frontend_express_list');
		// 读取快递列表及总数
		$list = array();

		// 分页参数
		$conds['page'] = $this->request->get('page');
		$conds['perpage'] = 15;

		$uda_list->execute($conds, $list);
		$total = $uda_list->get_total();
		$multi = '';
		if (! $total) {
			// 如果无数据
			return array($total, $multi, array(), $list);
		}

		// 快递列表id
		$eids = array();
		foreach ($list as $k => $v) {
			$eids[] = $v['eid'];
		}

		// 快递关联列表(收件人、代领人)
		$list_mem = array();
		$uda_list_mem = &uda::factory('voa_uda_frontend_express_mem_list');
		$uda_list_mem->execute(array('eid' => $eids), $list_mem);

		// 整理数据
		foreach ($list_mem as $k => $v) {
			if ($v['flag'] == voa_d_oa_express_mem::COLLECTION) { // 设置代领人姓名
				$list[$v['eid']]['c_username'] = $v['username'];
				continue;
			}
		}

		$perpage = $uda_list->get_perpage();
		$page = $uda_list->get_page();

		// 分页配置
		$pager_options = array('total_items' => $total, 'per_page' => $perpage, 'current_page' => $page, 'show_total_items' => true);

		$multi = pager::make_links($pager_options);
		// 引用结果，分页配置
		pager::resolve_options($pager_options);
		return array($total, $multi, $list);
	}

	/**
	 * 删除话题
	 *
	 * @param array $delete
	 * @param int $tid
	 * @return boolean
	 */
	protected function _delete($delete, $eid) {
		// 删除快递的eid
		$ids = 0;
		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($eid) {
			$ids = rintval($eid, false);
			if (! empty($ids)) {
				$ids = array($ids);
			}
		}
		if (empty($ids)) {
			$this->message('error', '请指定要删除的' . $this->_module_plugin['cp_name']);
		}
		try {
			// 事务开始
			voa_uda_frontend_transaction_abstract::s_begin();
			$uda = &uda::factory('voa_uda_frontend_express_delete');
			$result = array();
			if (! $uda->execute(array('eid' => $ids), $result)) {
				$this->_error_message($uda->errmsg);
				return true;
			}

			// 提交事务
			voa_uda_frontend_transaction_abstract::s_commit();
		} catch (help_exception $e) {
			// 事务回滚
			voa_uda_frontend_transaction_abstract::s_rollback();
			$this->_error_message($e->getMessage());
			return true;
		}
		$this->_success_message('指定' . $this->_module_plugin['cp_name'] . '信息删除完毕', null, null, true, $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id, array('ac' => 'list', 'eid' => '')));
	}

}
