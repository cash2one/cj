<?php
/**
* 题库列表
* Create By wogu
* $Author$
* $Id$
*/
class voa_c_admincp_office_exam_tikulist extends voa_c_admincp_office_exam_base {

	public function execute() {
		$search_default = array(
			'name' => '',
			'username' => '',
			'begintime' => '',
			'endtime' => '',
		);
		$search_conds = array();   //记住查询条件，填充到页面
		$conditions = array(); //供查询数据库用的查询条件
		$this->_parse_search_cond($search_default, $search_conds, $conditions);
		$issearch = $this->request->get('issearch') ? 1 : 0;

		$limit = 12;   // 每页显示数量
		$page = $this->request->get('page');   // 当前页码
		if (!is_numeric($page) || $page < 1) {
			$page = 1;
		}
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);
		$page_option = array($start, $limit);

		try {
			// 载入搜索uda类
			$uda_list = &uda::factory('voa_uda_frontend_exam_tiku');
			// 数据结果
			$result = array();
			// 实际查询条件
			$conditions = $issearch ? $conditions : array();
			$uda_list->list_tiku($result, $conditions, $page_option);
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}

		// 分页链接信息
		$multi = '';
		if ($result['total'] > 0) {
			// 输出分页信息
			$multi = pager::make_links(array(
				'total_items' => $result['total'],
				'per_page' => $limit,
				'current_page' => $page,
				'show_total_items' => true,
			));
		}

		// 注入模板变量
		$this->view->set('total', $result['total']);
		$this->view->set('list', $result['list']);
		$this->view->set('multi', $multi);
		$this->view->set('issearch', $this->request->get('issearch'));
		$this->view->set('search_conds', array_merge($search_default, $search_conds));

		$this->view->set('tiku_url', $this->cpurl($this->_module, $this->_operation, 'tikulist', $this->_module_plugin_id));
		
		$this->view->set('addtiku_url', $this->cpurl($this->_module, $this->_operation, 'addtiku', $this->_module_plugin_id, array('id' => '')));
		$this->view->set('deletetiku_url', $this->cpurl($this->_module, $this->_operation, 'deletetiku', $this->_module_plugin_id, array('id' => '')));
		$this->view->set('addtm_url', $this->cpurl($this->_module, $this->_operation, 'addtm', $this->_module_plugin_id, array('isedit' => '1', 'tiku_id' => '')));
		$this->view->set('viewtm_url', $this->cpurl($this->_module, $this->_operation, 'viewtm', $this->_module_plugin_id, array('id' => '')));
		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'deletetiku', $this->_module_plugin_id));

		$this->output('office/exam/tikulist');
	}

	/**
	 * 重构搜索条件
	 * @param array $searchDefault 初始条件
	 * @param array $searchBy 输入的查询条件
	 * @param array $conditons 组合的查询
	 */
	protected function _parse_search_cond($search_default, &$search_conds, &$conditons) {
		foreach ( $search_default AS $_k=>$_v ) {
			if ( isset($_GET[$_k]) && $_v != $this->request->get($_k) ) {
				$search_conds[$_k] = $this->request->get($_k);
				if ($_k == 'begintime') {
					$conditons['created>=?'] = rstrtotime($this->request->get($_k));
				} elseif ($_k == 'endtime') {
					$conditons['created<?'] = rstrtotime($this->request->get($_k)) + 86400;
				} elseif ($_k == 'name') {
					$conditons['name LIKE ?'] = '%'.($this->request->get($_k)).'%';
				} else {
					$conditons[$_k] = ($this->request->get($_k));
				}
			}
		}
		return true;
	}
}
