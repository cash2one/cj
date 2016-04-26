<?php

/**
 * voa_c_admincp_office_interface_flowlist
 * 企业后台/测试应用/流程列表
 * Create By gaosong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_interface_flowlist extends voa_c_admincp_office_interface_base {

	public function execute() {

		// 搜索条件
		$conds = $this->request->getx();
		$issearch = $this->request->get('issearch');

		list($total, $multi, $list) = $this->_search_interface($conds, $issearch);

		// 获取插件信息
		$plugins = array();
		$serv_plugin = &service::factory('voa_s_oa_common_plugin');
		$plugins = $serv_plugin->fetch_all();

		$this->view->set('total', $total);
		$this->view->set('list', $list);
		$this->view->set('multi', $multi);
		$this->view->set('search_conds', $conds);
		// 设置插件信息
		$this->view->set('plugins', $plugins);

		// 重置执行url
		$this->view->set('reset_url', $this->cpurl($this->_module,
				$this->_operation, 'reset',
				$this->_module_plugin_id,
				array('f_id' => '')));

		// 接口编辑url
		$this->view->set('edit_url', $this->cpurl($this->_module,
				$this->_operation, 'fedit',
				$this->_module_plugin_id,
				array('f_id' => '')));

		// 删除接口url
		$this->view->set('delete_url', $this->cpurl($this->_module, $this->_operation, 'fdelete', $this->_module_plugin_id, array('f_id' => '')));

		// 输出模板
		$this->output('office/interface/flowlist');
	}

	/**
	 * 搜索
	 *
	 * @param number $cp_pluginid
	 * @param boolean $issearch
	 * @param array $searchDefault
	 * @param number $perpage
	 */
	protected function _search_interface($conds, $issearch) {

		$uda_list = &uda::factory('voa_uda_frontend_interface_flowlist');
		// 读取列表及总数
		$list = array();

		// 分页参数
		$page = $this->request->get('page');
		$conds['page'] = empty($page) ? 1 : $page;
		$conds['perpage'] = 15;

		$uda_list->execute($conds, $list);
		$total = $uda_list->get_total();
		$multi = '';
		if (! $total) {
			// 如果无数据
			return array($total, $multi, array(), $list);
		}

		// 格式化数据
		foreach ($list as $_k => $_v) {
			$list[$_k]['_f_exec'] = $uda_list->status($_v['f_exec']);
			$list[$_k]['_created'] =  rgmdate($_v['created'], 'Y-m-d H:i');
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

}
