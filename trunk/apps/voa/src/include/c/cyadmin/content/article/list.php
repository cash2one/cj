<?php
/**
 * voa_c_cyadmin_content_article_list
 * 后台全部文章，包括新闻和活动
 */
class voa_c_cyadmin_content_article_list extends voa_c_cyadmin_content_article_base {

	public function execute() {
		/**
		 * 搜索默认值
		 */
		$search_default = array(
			'keyword' => '',
			'acid' => '',
			'date_start' => '',
			'date_end' => '',
			'is_publish' => '' 
		);
		$search_conds = array(); // 记住查询条件，填充到页面
		$conditions = array(); // 供查询数据库用的查询条件
		$this->_parse_search_cond($search_default, $search_conds, $conditions);
		
		$issearch = $this->request->get('isserach') ? 1 : 0;
		$total = null;
		$multi = null;
		$list = null;
		list($total, $multi, $list) = $this->_list_by_conds($conditions);
		// print_r($list);
		$this->view->set('conds', $search_conds);
		$this->view->set('issearch', $issearch);
		$this->view->set('article_list', $this->_listformat($list));
		$this->view->set('multi', $multi);
		$this->view->set('total', $total);
		$this->view->set('form_delete_url', $this->cpurl($this->_module, 'article', 'delete'));
		$this->view->set('edit_url', $this->cpurl($this->_module, 'article', 'edit', array(
			'aid' => '' 
		)));
		$this->view->set('delete_url', $this->cpurl($this->_module, 'article', 'delete', array(
			'aid' => '' 
		)));
		$this->output('cyadmin/content/article/list');
	}

	/**
	 * 重构搜索条件
	 * 
	 * @param array $searchDefault
	 *        	初始条件
	 * @param array $searchBy
	 *        	输入的查询条件
	 * @param array $conditons
	 *        	组合的查询
	 */
	protected function _parse_search_cond($search_default, &$search_conds, &$conditons) {
		foreach ($search_default as $_k => $_v) {
			if (isset($_GET[$_k]) && $_v != $this->request->get($_k)) {
				$search_conds[$_k] = $this->request->get($_k);
				if ($_k == 'date_start') {
					$conditons['updated>=?'] = rstrtotime($this->request->get($_k));
				} elseif ($_k == 'date_end') {
					$conditons['updated<?'] = rstrtotime($this->request->get($_k)) + 86400;
				} elseif ($_k == 'keyword') {
					$conditons['title LIKE ?'] = '%' . ($this->request->get($_k)) . '%';
				} elseif ($_k == 'acid') {
					$conditons['acid=?'] = $this->request->get($_k);
				} elseif ($_k == 'is_publish') {
					$conditons['is_publish=?'] = $this->request->get($_k);
				} else {
					$conditons[$_k] = ($this->request->get($_k));
				}
			}
		}
		return true;
	}
}
