<?php
/**
 * voa_c_admincp_tool_secret_search
 * 企业后台/应用宝/秘密/搜索
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_tool_secret_search extends voa_c_admincp_tool_secret_base {

	public function execute() {

		// 每页显示的结果数
		$perpage = 15;

		$search_default_fields = array(
				'after' => '',//发表时间范围：此时间之后
				'before' => '',//发表时间范围：此时间之前
				'stp_subject' => '',//主题关键词
				'stp_message' => '',//回复内容关键词
		);

		// 搜索表单的默认值
		$search_by = $search_default_fields;
		// 确定当前是否提交了搜索
		$is_search = $this->request->get('is_search') ? 1 : 0;
		// 符合条件的列表
		$list = array();
		// 符合条件的数量
		$total = 0;
		// 分页链接
		$multi = '';

		// 搜索条件
		$conditions = array();
		if ($is_search) {
			// 分析出搜索条件
			$uda_search = &uda::factory('voa_uda_frontend_secret_search');
			$uda_search->secret_post_conditions($search_default_fields, $search_by, $conditions);
		}

		if ($is_search && !empty($conditions)) {

			// 计算符合条件的数量
			$total = $this->_service_single('secret_post', $this->_module_plugin_id, 'count_by_conditions', $conditions);

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
				$list = $this->_service_single('secret_post', $this->_module_plugin_id, 'fetch_by_conditions', $conditions, $pager_options['start'], $pager_options['per_page']);

				$uda_format = &uda::factory('voa_uda_frontend_secret_format');
				$uda_format->secret_post_list($list);
			}

		}

		$this->view->set('search_by', $search_by);
		$this->view->set('list_url', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		$this->view->set('form_search_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->view->set('thread_delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('st_id' => '')));
		$this->view->set('reply_delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('stp_id' => '')));
		$this->view->set('view_url_base', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('st_id' => '')));
		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));

		$this->view->set('is_thread', voa_d_oa_secret_post::FIRST_YES);

		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);


		$this->output('tool/secret/secret_search');
	}

}
