<?php
/**
 * voa_c_admincp_office_redpack_list
 * 红包-列表
 * Date: 15/3/9
 * Time: 上午10:42
 */


class voa_c_admincp_office_redpack_list extends voa_c_admincp_office_redpack_base {

	public function execute() {

		// 读取所有红包列表
		$uda_rp = &service::factory('voa_uda_frontend_redpack_list');
		$list = array();
		$params = $this->request->getx();
		if (!$uda_rp->doit($list, $params)) {
			return $this->_error_message($uda_rp->errmsg, '', '', false, $this->_self_url);
		}

		// 读取总数
		$serv_rp = &service::factory('voa_s_oa_redpack');
		$total = $serv_rp->count();
		// 分页
		$multi = '';
		if ($total > 0) {
			$page = (int)$this->request->get('page', 1);
			list($start, $limit, $page) = voa_h_func::get_limit($page);
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $limit,
				'current_page' => $page,
				'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
		}

		$this->view->set('list', $list);
		$this->view->set('multi', $multi);
		$this->view->set('form_ac_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->view->set('del_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('id'=>'')));
		$this->view->set('view_url_base', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('id'=>'')));
		$this->view->set('form_del_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));

		$this->output('office/redpack/list');
	}

}
