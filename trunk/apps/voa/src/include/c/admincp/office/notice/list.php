<?php
/**
 * voa_c_admincp_office_notice_list
 * 企业后台/微办公/通知公告/列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_notice_list extends voa_c_admincp_office_notice_base {

	public function execute() {

		$perpage = 15;

		list($total, $multi, $search_by, $list) = $this->_notice_search($perpage);

		$this->view->set('search_by', $search_by);
		$this->view->set('list_url', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		$this->view->set('form_search_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->view->set('view_url_base', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('nt_id' => '')));
		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		$this->view->set('delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('nt_id' => '')));

		//$this->view->set('notice_type_list', $this->_sets['types']);
		$this->view->set('issearch', $this->request->get('issearch'));

		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);

		$this->output('office/notice/notice_list');
	}

	protected function _notice_search($perpage) {
		//搜索条件
		$conditions = array();
		//搜索字段
		$search_by = array();

		$uda_notice_search = &uda::factory('voa_uda_frontend_notice_search');
		$uda_notice_search->notice_conditions($search_by, $conditions, array('shard_key' => $this->_module_plugin_id));

		$list = array();
		$total = $this->_service_single('notice', $this->_module_plugin_id, 'count_by_conditions', $conditions);
		$multi = '';
		if ($total > 0) {
			$pagerOptions = array(
					'total_items' => $total,
					'per_page' => $perpage,
					'current_page' => $this->request->get('page'),
					'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);
			$list = $this->_service_single('notice', $this->_module_plugin_id, 'fetch_by_conditions', $conditions, $pagerOptions['start'], $pagerOptions['per_page']);

			$uda_notice_format = &uda::factory('voa_uda_frontend_notice_format');
			$uda_notice_format->format_list($list);
		}

		return array($total, $multi, $search_by, $list);
	}

}
