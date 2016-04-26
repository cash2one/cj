<?php
class voa_c_cyadmin_content_link_list extends voa_c_cyadmin_content_link_base {

	public function execute() {
		
		/**
		 * 搜索默认值
		 */
		$search_default = array(
			'keyword' => '',
			'linktype' => '',
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
		$this->view->set('link_list', $this->_listformat($list));
		$this->view->set('multi', $multi);
		$this->view->set('total', $total);
		$this->view->set('form_delete_url', $this->cpurl($this->_module, 'link', 'delete'));
		$this->view->set('edit_url', $this->cpurl($this->_module, 'link', 'edit', array(
			'lid' => '' 
		)));
		$this->view->set('delete_url', $this->cpurl($this->_module, 'link', 'delete', array(
			'lid' => '' 
		)));
		$this->output('cyadmin/content/link/list');
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
					$conditons['linkname LIKE ?'] = '%' . ($this->request->get($_k)) . '%';
				} elseif ($_k == 'linktype') {
					$conditons['linktype=?'] = $this->request->get($_k);
				} elseif ($_k == 'is_publish') {
					$conditons['is_publish=?'] = $this->request->get($_k);
				} else {
					$conditons[$_k] = ($this->request->get($_k));
				}
			}
		}
		return true;
	}

	protected function _list_by_conds($conds = array()) {
		$service = &service::factory('voa_s_cyadmin_content_link_list');
		// 统计数量
		
		// 显示数量
		$perpage = 10;
		// $conds['publishtime <= ?'] = time();
		$total = null;
		$list = array();
		$multi = null;
		$total = $service->count_by_conds($conds);
		// print_r($total);
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true 
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);
			$page_option[0] = $pagerOptions['start'];
			$page_option[1] = $perpage;
			$orderby['lsort'] = 'DESC';
			$orderby['lid'] = 'DESC';
			$list = $service->list_by_conds($conds, $page_option, $orderby);
			// print_r($list);
		}
		
		return array(
			$total,
			$multi,
			$list 
		);
	}

	/**
	 * 处理数据
	 * 
	 * @param array() $data        	
	 */
	protected function _listformat($data) {
		if (empty($data)) {
			return $data;
		}
		foreach ($data as $val) {
			
			$val['time'] = rgmdate($val['updated'], 'Y年m月d日 H:i');
			if ($val['linktype'] == 1) {
				$val['type'] = '文字链接';
			} elseif ($val['linktype'] == 2) {
				$val['type'] = '图片链接';
			} else {
				$val['type'] = '未分类';
			}
			if ($val['is_publish'] == 1) {
				
				$val['status'] = '已发布';
			} else {
				$val['status'] = '草稿';
			}
			$_data[] = $val;
		}
		return $_data;
	}
}
