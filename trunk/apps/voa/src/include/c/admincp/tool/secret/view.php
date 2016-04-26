<?php
/**
 * voa_c_admincp_tool_secret_view
 * 企业后台/应用宝/秘密/浏览
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_tool_secret_view extends voa_c_admincp_tool_secret_base {

	public function execute() {

		$st_id = $this->request->get('st_id');
		$st_id = rintval($st_id, false);

		// 每页显示内容数
		$perpage = 15;

		// 主题基本信息
		$secret = array();

		if ($st_id <= 0 || !($secret = $this->_service_single('secret', $this->_module_plugin_id, 'fetch_by_id', $st_id))) {
			$this->message('error', '指定主题不存在 或 已删除');
		}

		// 主题内容信息
		$thread = $this->_service_single('secret_post', $this->_module_plugin_id, 'fetch_by_st_id', $st_id);
		if (empty($thread)) {
			$this->message('error', '指定主题内容不存在');
		}
		$uda_post = &uda::factory('voa_uda_frontend_secret_format');
		$uda_post->secret_post($thread);

		// 主题回复数
		$post_count = $this->_service_single('secret_post', $this->_module_plugin_id, 'count_all_by_st_id', $st_id);
		// 分页链接
		$multi = '';
		// 回复列表
		$post_list = array();

		if ($post_count > 0) {
			// 分页链接信息
			$pager_options = array(
					'total_items' => $post_count,
					'per_page' => $perpage,
					'current_page' => $this->request->get('page'),
					'show_total_items' => true,
			);
			// 分页链接
			$multi = pager::make_links($pager_options);
			pager::resolve_options($pager_options);
			$post_list = $this->_service_single('secret_post', $this->_module_plugin_id, 'fetch_all_by_st_id', $st_id, $pager_options['start'], $pager_options['per_page']);
			// 格式化内容列表
			$uda_post->secret_post_list($post_list);
		}

		$this->view->set('st_id', $st_id);
		$this->view->set('post_first_tag', voa_d_oa_secret_post::FIRST_YES);
		$this->view->set('secret', $secret);
		$this->view->set('post_count', $post_count);
		$this->view->set('multi', $multi);
		$this->view->set('post_list', $post_list);
		$this->view->set('thread', $thread);

		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		$this->view->set('delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('st_id' => '')));

		$this->output('tool/secret/secret_view');
	}

}
