<?php
class voa_c_admincp_office_travel_customer extends voa_c_admincp_office_travel_base {

	// 最大 limit 值
	protected $_max_limit = 100;

	public function execute() {

		// 搜索条件
		$conds = $this->request->getx();
		$ac = $this->request->get('ac');
		if (! empty($ac)) { // 根据操作类型，跳转不同页面
			$this->$ac();
			exit();
		}

		$issearch = $this->request->post('issearch');
		list($total, $multi, $list) = $this->_search_customer($conds, $issearch);

		// 获取用户名称
		$this->_get_users($list);

		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);

		// 客户详情url
		$this->view->set('viewUrlBase', $this->cpurl($this->_module, $this->_operation, 'customer', $this->_module_plugin_id, array(
				'ac' => 'style#/view/'
		)));
		// 删除客户url
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'customer', $this->_module_plugin_id, array(
				'ac' => 'customer_delete',
				'dataid' => ''
		)));
		//查询客户url
		$this->view->set('searchActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		$this->output('office/customize/customize');
	}

	/**
	 * 获取用户名称
	 * @param array $list 客户列表
	 * @return boolean
	 */
	protected function _get_users(&$list) {

		// 如果列表为空
		if (empty($list)) {
			return true;
		}

		// 取出所有用户 uid
		$uids = array();
		foreach ($list as $_v) {
			$uids[$_v['uid']] = $_v['uid'];
		}

		// 根据 uids 读取用户信息
		$serv_member = &service::factory('voa_s_oa_member');
		$users = $serv_member->fetch_all_by_ids($uids);

		// 在返回数据中加入用户名
		foreach ($list as &$_v) {
			if (empty($users[$_v['uid']])) {
				$_v['username'] = '';
				continue;
			}

			$_v['username'] = $users[$_v['uid']]['m_username'];
		}

		return true;
	}


	/**
	 * 搜索客户记录
	 * @param number $cp_pluginid
	 * @param boolean $issearch
	 * @param array $searchDefault
	 * @param number $perpage
	 */
	protected function _search_customer($conds, $issearch) {
		$uda_list = &uda::factory('voa_uda_frontend_customer_data', $this->_ptname);

		// 获取分页参数
		$page = (int)$this->request->get('page');
		$limit = 15;
		list($start, $perpage, $page) = voa_h_func::get_limit($page, min($limit, $this->_max_limit));

		$params = array();
		$params['is_admin'] = 1;

		// 条件查询
		if ($issearch) {
			$truename = $this->request->post('truename');
			if (! empty($truename)) {
				$params['query'] = $truename;
			}
		}

		// 读取话题列表 及总数
		$total = 0;
		$list = array();
		$uda_list->list_all($params, array(
				$start,
				$perpage
		), $list, $total);

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
	 * 删除产品
	 */
	private function customer_delete() {
		$delete = $this->request->post('delete');
		$tid = rintval($this->request->get('dataid'));

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

		$uda = &uda::factory('voa_uda_frontend_customer_data', $this->_ptname);
		$result = array();
		if (! $uda->delete(implode(',', $ids), $result)) {
			$this->_error_message($uda->errmsg);
			return true;
		}

		$this->_success_message('指定' . $this->_module_plugin['cp_name'] . '信息删除完毕', null, null, true, $this->cpurl($this->_module, $this->_operation, 'customer', $this->_module_plugin_id, array()));
	}

	/**
	 * js模板
	 */
	private function style() {
		$p_sets = voa_h_cache::get_instance()->get('plugin.travel.setting', 'oa');
		$this->view->set('pluginid', $this->_module_plugin_id);
		$this->view->set('style', $p_sets['goods_tpl_style']);

		$this->output('office/customize/customer');
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _init_ptname() {

		parent::_init_ptname();

		$this->_ptname = array(
				'plugin' => $this->_pluginname,
				'table' => $this->_p_sets['customer_table_name']
		);

		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.travel.customerclass', 'oa');
		$this->_ptname['columns'] = voa_h_cache::get_instance()->get('plugin.travel.customertablecol', 'oa');
		$this->_ptname['options'] = voa_h_cache::get_instance()->get('plugin.travel.customertablecolopt', 'oa');

	}

}
