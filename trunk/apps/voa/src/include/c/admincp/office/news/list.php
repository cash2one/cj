<?php
/**
 * voa_c_admincp_office_news_list
* 企业后台/微办公管理/超级报表/数据列表
* Create By YanWenzhong
* $Author$
* $Id$
*/
class voa_c_admincp_office_news_list extends voa_c_admincp_office_news_base {

	public function execute() {
		$search_default = array(
			'title' => '',
			'nca_id' => '-1',
			'is_publish' => '-1',
			'updated_begintime' => '',
			'updated_endtime' => '',
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
			$uda_list = &uda::factory('voa_uda_frontend_news_list');
			// 数据结果
			$result = array();
			// 实际查询条件
			$conditions = $issearch ? $conditions : array();
			$uda_list->list_news($result, $conditions, $page_option);
			// 获取分类
			$uda_cat = &uda::factory('voa_uda_frontend_news_category');
			$select_categories = $uda_cat->list_categories();
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
		$this->view->set('categories', $this->_categories);
		$this->view->set('select_categories', $select_categories);
		$this->view->set('status', $this->status);
		$this->view->set('issearch', $this->request->get('issearch'));//判断是否以搜索
		$this->view->set('search_conds', array_merge($search_default, $search_conds));//搜索关键字
		//链接-详情-编辑-删除-阅读人员-及提交url
		$this->view->set('view_url', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('ne_id' => '')));
		$this->view->set('edit_url', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('ne_id' => '')));
		$this->view->set('delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('ne_id' => '')));
		$this->view->set('read_url', $this->cpurl($this->_module, $this->_operation, 'read', $this->_module_plugin_id, array('ne_id' => '')));
		$this->view->set('list_url', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		$this->view->set('form_category_url', $this->cpurl($this->_module, $this->_operation, 'addcategory', $this->_module_plugin_id));

		// 输出模板
		$this->output('office/news/list');
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
				if ($_k == 'updated_begintime') {
					$conditons['updated>=?'] = rstrtotime($this->request->get($_k));
				} elseif ($_k == 'updated_endtime') {
					$conditons['updated<?'] = rstrtotime($this->request->get($_k)) + 86400;
				} elseif ($_k == 'title') {
					$conditons['title LIKE ?'] = '%'.($this->request->get($_k)).'%';
				} else {
					$conditons[$_k] = ($this->request->get($_k));
				}
			}
		}
		return true;
	}

}
