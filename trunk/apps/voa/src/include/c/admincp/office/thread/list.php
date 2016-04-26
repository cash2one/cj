<?php

/**
 * voa_c_admincp_office_sign_list
 * 企业后台/同事社区/社区话题列表
 * Create By gaosong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_thread_list extends voa_c_admincp_office_thread_base
{

	public function execute()
	{
		$p_sets = voa_h_cache::get_instance()->get('plugin.thread.setting', 'oa'); // 读同事社区配置缓存

		$ac = $this->request->get('ac');
		//删除操作
		if ($ac == 'delete') {
			$delete = $this->request->post('delete');
			$tid = rintval($this->request->get('tid'));
			$this->_delete($delete, $tid);
			return true;
		}

		// 搜索条件
		$conds = $this->request->getx();
		$issearch = $this->request->get('issearch');
		list ($total, $multi, $list) = $this->_search_thread($conds, $issearch);

		//将发布时间转换为年月日格式
		foreach ($list as &$releaseDate) {
			$releaseDate['__created'] = rgmdate($releaseDate['created']);
		}

		foreach ($list as &$_p) {

			if ($p_sets['hot_key'] == 'likes') {
				if (rintval($_p['likes']) >= rintval($p_sets['hot_value'])) {
					$_p['good'] = 1; // 热门话题
				}
			} else {
				if (rintval($_p['replies']) >= rintval($p_sets['hot_value'])) {
					$_p['good'] = 1; // 热门话题
				}
			}

			if ($p_sets['choice_key'] == 'likes') {
				if (rintval($_p['likes']) >= rintval($p_sets['choice_value'])) {
					$_p['choice'] = 1; // 精选话题
				}
			} else {
				if (rintval($_p['replies']) >= rintval($p_sets['choice_value'])) {
					$_p['choice'] = 1; // 精选话题
				}
			}
		}

		unset($_p);
		$this->view->set('total', $total);
		$this->view->set('list', $list);
		$this->view->set('multi', $multi);
		$this->view->set('searchBy', $conds);
		// 话题详情url
		$this->view->set('viewUrlBase', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array(
			'tid' => ''
		)));
		// 删除话题url
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id, array(
			'ac' => 'delete',
			'tid' => ''
		)));
		// 查询话题url
		$this->view->set('searchActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->output('office/thread/thread_list');
	}

	/**
	 * 搜索话题记录
	 *
	 * @param number $cp_pluginid
	 * @param boolean $issearch
	 * @param array $searchDefault
	 * @param number $perpage
	 */
	protected function _search_thread($conds, $issearch)
	{
		$uda_list = &uda::factory('voa_uda_frontend_thread_alllist');

		/**
		 * 读取话题列表 及总数
		 */
		$list = array();

		$conds['page'] = $this->request->get('page');
		$conds['perpage'] = 15;
		$conds['sort_type'] = empty($conds['sort_type']) ? 1 : $conds['sort_type'];

		if ($issearch) {
			if ($conds['starttime']) {
				$conds['starttime'] = rstrtotime($conds['starttime']);
			}

			if ($conds['endtime']) {
				$conds['endtime'] = rstrtotime($conds['endtime']) + 86400;
			}
		}

		$uda_list->execute($conds, $list);

		$total = $uda_list->get_total();
		$multi = '';
		if (! $total) {
			// 如果无数据
			return array(
				$total,
				$multi,
				array(),
				$list
			);
		}

		$perpage = $uda_list->get_perpage();
		$page = $uda_list->get_page();

		// 分页配置
		$pager_options = array(
			'total_items' => $total,
			'per_page' => $perpage,
			'current_page' => $page,
			'show_total_items' => true
		);

		$multi = pager::make_links($pager_options);
		// 引用结果，分页配置
		pager::resolve_options($pager_options);
		return array(
			$total,
			$multi,
			$list
		);
	}

	/**
	 * 删除话题
	 *
	 * @param array $delete
	 * @param int $tid
	 * @return boolean
	 */
	protected function _delete($delete, $tid)
	{
		$ids = 0;
		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($tid) {
			$ids = rintval($tid, false);
			if (! empty($ids)) {
				$ids = array(
					$ids
				);
			}
		}
		if (empty($ids)) {
			$this->message('error', '请指定要删除的' . $this->_module_plugin['cp_name']);
		}
		try {
			// 事务开始
			voa_uda_frontend_transaction_abstract::s_begin();
			$uda = &uda::factory('voa_uda_frontend_thread_delete');
			$result = array();
			if (! $uda->execute(array(
						'tid' => $ids
					), $result)) {
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
		$this->_success_message('指定' . $this->_module_plugin['cp_name'] . '信息删除完毕', null, null, true, $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id, array(
								'ac' => 'list',
								'tid' => ''
							)));
	}
}
