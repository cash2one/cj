<?php
/**
 * search.php
 * 云工作后台/移动派单/搜索
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_workorder_search extends voa_c_admincp_office_workorder_base {

	public function execute() {

		// 初始化变量
		// 格式化后用户提交的查询参数
		$conditions = array();
		// 搜索结果集合
		$result = array('count' => 0, 'list' => array());
		// 分页链接信息
		$multi = '';

		// 是否提交了搜索
		$submit_search = false;

		// 提交搜索
		if ($this->request->get('submit_search')) {
			list($conditions, $result, $multi) = $this->_search_respond();
			$submit_search = true;
		}

		// 初始化搜索表单
		$this->_init_search_form($conditions);

		// 注入模板变量
		$this->view->set('count', $result['count']);
		$this->view->set('list', $result['list']);
		$this->view->set('multi', $multi);
		$this->view->set('view_url_base', $this->cpurl($this->_module, $this->_operation, 'view'
				, $this->_module_plugin_id, array('woid' => '')));
		$this->view->set('submit_search', $submit_search);

		// 输出模板
		$this->output('office/workorder/search');
	}

	/**
	 * 提交搜索的响应结果
	 */
	protected function _search_respond() {

		// 每页显示数量
		$limit = 12;

		// 当前页码
		$page = $this->request->get('page');
		if (!is_numeric($page) || $page < 1) {
			$page = 1;
		}

		// 载入搜索uda类
		$uda_search = &uda::factory('voa_uda_frontend_workorder_action_search');
		// 列出数据请求
		$request = array(
			'page' => $page,
			'limit' => $limit,
			'uid' => 0,
			'admin' => true,
		);

		// 默认的搜索条件
		$search_conditions = array(
			'sender' => '',
			'woid' => '',
			'wostate' => -1,
			'operator' => '',
			'ordertime_start' => '',
			'ordertime_end' => '',
		);
		// 遍历搜索条件以提供查询使用
		foreach ($search_conditions as $_key => $_value) {
			$value = $this->request->get($_key);
			if (!is_scalar($value) || $value == $_value) {
				continue;
			}
			$request[$_key] = $value;
		}
		unset($_key, $_value);

		// 数据结果
		$result = array();
		// 实际查询条件（在数据列表这里并无实际用途）
		$conditions = array();
		if (!$uda_search->result($request, $result, $conditions)) {
			$this->message('error', $uda_search->errmsg.'[Err:'.$uda_search->errcode.']');
			return;
		}

		// 分页链接信息
		$multi = '';
		// 结果不为空
		if ($result['count'] > 0) {
			// 输出分页信息
			$multi = pager::make_links(array(
				'total_items' => $result['count'],
				'per_page' => $limit,
				'current_page' => $page,
				'show_total_items' => true,
			));

			// 格式化工单列表
			$result['list'] = $this->_format_workorder_list($result['list']);
		}
		// 搜索请求参数
		$conditions = $conditions ? $conditions : $request;

		return array($conditions, $result, $multi);
	}

}
