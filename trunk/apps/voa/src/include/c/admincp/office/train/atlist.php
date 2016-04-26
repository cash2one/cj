<?php
/**
 * voa_c_admincp_office_train_atlist
 * 企业后台/微办公管理/培训/文章列表
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_train_atlist extends voa_c_admincp_office_train_base {

	public function execute() {
		$searchDefault = array(
				'author' => '',
				'title' => '',
				'tc_id' => '-1',
				'created_begintime' => '',
				'updated_begintime' => '',
				'created_endtime' => '',
				'updated_endtime' => '',
		);
		$searchBy = array();
		$conditions = array();
		$this->_parse_search_cond($searchDefault, $searchBy, $conditions);
		$issearch = $this->request->get('issearch') ? 1 : 0;

		$limit = 12;   // 每页显示数量
		$page = $this->request->get('page');   // 当前页码
		if (!is_numeric($page) || $page < 1) {
			$page = 1;
		}

		// 载入搜索uda类
		$uda_articlelist = &uda::factory('voa_uda_frontend_train_action_articlelist');
		// 列出数据请求
		$pager = array( ($page-1)*$limit, $limit );
		// 数据结果
		$result = array();
		// 实际查询条件
		$conditions = $issearch ? $conditions : array();
		if (!$uda_articlelist->result($pager, $result, $conditions)) {
			$this->message('error', $uda_search->errmsg.'[Err:'.$uda_search->errcode.']');
			return;
		}
		// 分页链接信息
		$multi = '';
		if ($result['count'] > 0) {
			// 输出分页信息
			$multi = pager::make_links(array(
				'total_items' => $result['count'],
				'per_page' => $limit,
				'current_page' => $page,
				'show_total_items' => true,
			));
		}

		// 取得目录列表
		$uda_categorylist = &uda::factory('voa_uda_frontend_train_action_categorylist');
		$categories = $uda_categorylist->get_all_categories();

		// 注入模板变量
		$this->view->set('categories', $categories);
		$this->view->set('total', $result['count']);
		$this->view->set('list', $result['list']);
		$this->view->set('multi', $multi);
		$this->view->set('issearch', $this->request->get('issearch'));
		$this->view->set('searchBy', array_merge($searchDefault, $searchBy));
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'atdelete', $this->_module_plugin_id, array('ta_id'=>'')));
		$this->view->set('editUrlBase', $this->cpurl($this->_module, $this->_operation, 'atedit', $this->_module_plugin_id, array('ta_id'=>'')));
		$this->view->set('formDeleteUrl', $this->cpurl($this->_module, $this->_operation, 'atdelete', $this->_module_plugin_id));
		$this->view->set('listAllUrl', $this->cpurl($this->_module, $this->_operation, 'atlist', $this->_module_plugin_id));

		// 输出模板
		$this->output('office/train/article_list');
	}

	/**
	 * 重构搜索条件
	 * @param array $searchDefault 初始条件
	 * @param array $searchBy 输入的查询条件
	 * @param array $conditons 组合的查询
	 */
	protected function _parse_search_cond($searchDefault, &$searchBy, &$conditons) {
		foreach ( $searchDefault AS $_k=>$_v ) {
			if ( isset($_GET[$_k]) && $_v != $this->request->get($_k) ) {
				$searchBy[$_k] = $this->request->get($_k);
				if ($_k == 'created_begintime') {
					$conditons['created>?'] = rstrtotime($this->request->get($_k));
				} elseif ($_k == 'created_endtime') {
					$conditons['created<?'] = rstrtotime($this->request->get($_k)) + 86400;
				} elseif ($_k == 'updated_begintime') {
					$conditons['updated>?'] = rstrtotime($this->request->get($_k));
				} elseif ($_k == 'updated_endtime') {
					$conditons['updated<?'] = rstrtotime($this->request->get($_k)) + 86400;
				} elseif ($_k == 'title') {
					$conditons['title LIKE ?'] = '%'.($this->request->get($_k)).'%';
				} elseif ($_k == 'author') {
					$conditons['author LIKE ?'] = '%'.($this->request->get($_k)).'%';
				} else {
					$conditons[$_k] = ($this->request->get($_k));
				}
			}
		}
		return true;
	}

}
