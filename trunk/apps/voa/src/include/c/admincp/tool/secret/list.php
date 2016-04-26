<?php
/**
 * voa_c_admincp_tool_secret_list
 * 企业后台/应用宝/秘密/列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_tool_secret_list extends voa_c_admincp_tool_secret_base {

	public function execute() {

		// 每页显示的主题数
		$perpage = 15;
		// 主题数
		$total = $this->_service_single('secret', $this->_module_plugin_id, 'count_all', null);
		// 分页链接
		$multi = '';
		// 主题列表
		$list = array();

		if ($total > 0) {

			// 分页链接信息
			$pager_options = array(
					'total_items' => $total,
					'per_page' => $perpage,
					'current_page' => $this->request->get('page'),
					'show_total_items' => true,
			);
			// 分页链接
			$multi = pager::make_links($pager_options);
			pager::resolve_options($pager_options);
			// 主题列表
			$list = $this->_service_single('secret', $this->_module_plugin_id, 'fetch_all', $pager_options['start'], $pager_options['per_page']);
			// 格式化主题列表
			$uda = &uda::factory('voa_uda_frontend_secret_format');
			$uda->secret_list($list);

			// 计算主题回复数
			$replies_count = array();
			$uda_base = &uda::factory('voa_uda_frontend_secret_base');
			$uda->secret_replies_count(array_keys($list), $replies_count);

			foreach ($list as &$v) {
				$v['_count'] = isset($replies_count[$v['st_id']]) ? $replies_count[$v['st_id']] : 0;
			}

		}

		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);
		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		$this->view->set('delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('st_id' => '')));
		$this->view->set('view_url_base', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('st_id' => '')));

		$this->output('tool/secret/secret_list');
	}

}
