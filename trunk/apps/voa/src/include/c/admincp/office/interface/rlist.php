<?php

/**
 * voa_c_admincp_office_interface_rlist
 * 企业后台/测试应用/日志
 * Create By gaosong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_interface_rlist extends voa_c_admincp_office_interface_base {

	public function execute() {

		// 搜索条件
		$conds = $this->request->getx();
		$issearch = $this->request->get('issearch');

		list($total, $multi, $list) = $this->_search_log($conds, $issearch);

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

		// 详情url
		$this->view->set('view_url', $this->cpurl($this->_module, $this->_operation, 'rinfo', $this->_module_plugin_id, array('n_id' => '')));

		// 输出模板
		$this->output('office/interface/rlist');
	}

	/**
	 * 搜索
	 *
	 * @param number $cp_pluginid
	 * @param boolean $issearch
	 * @param array $searchDefault
	 * @param number $perpage
	 */
	protected function _search_log($conds, $issearch) {

		$uda_list = &uda::factory('voa_uda_frontend_interface_rlist');
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
			$list[$_k]['_created'] =  rgmdate($_v['created'], 'Y-m-d H:i');
			$list[$_k]['_msg'] = unserialize($_v['msg']);
			$list[$_k]['_params'] = rjson_encode(unserialize($_v['params']));//请求参数
			$list[$_k]['_code'] = $list[$_k]['code'] . ':' . $this->_fromat_code($list[$_k]['code'], $list[$_k]['_msg']);//返回状态码
		}
		//获取分页参数
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
	 * 格式化状态返回码
	 * @param $code 状态码
	 * @param $msg 错误返回值
	 * @return string
	 */
	protected function _fromat_code($code, $msg) {
		if ($code == 0) {
			return $message = '成功返回';
		}
		return $message = $msg['errmsg'];
	}

}
