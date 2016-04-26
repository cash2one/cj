<?php
/**
* 试卷列表
* Create By wogu
* $Author$
* $Id$
*/

class voa_c_admincp_office_exam_paperlist extends voa_c_admincp_office_exam_base {

	public function execute() {
		$search_default = array(
			'name' => '',
			'status' => '-1',
			'begin_time' => '',
			'end_time' => '',
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
			$uda_list = &uda::factory('voa_uda_frontend_exam_paper');
			// 数据结果
			$result = array();
			// 实际查询条件
			$conditions = $issearch ? $conditions : array();
			$uda_list->list_paper($result, $conditions, $page_option);
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

		$this->view->set('total', $result['total']);
		$this->view->set('list', $result['list']);
		$this->view->set('multi', $multi);
		$this->view->set('issearch', $this->request->get('issearch'));
		$this->view->set('search_conds', array_merge($search_default, $search_conds));

		$this->view->set('paperlist_url', $this->cpurl($this->_module, $this->_operation, 'paperlist', $this->_module_plugin_id));

		$this->view->set('addpaper_url', $this->cpurl($this->_module, $this->_operation, 'addpaper', $this->_module_plugin_id, array('id' => '')));
		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'deletepaper', $this->_module_plugin_id));
		$this->view->set('deletepaper_url', $this->cpurl($this->_module, $this->_operation, 'deletepaper', $this->_module_plugin_id, array('id' => '')));
		$this->view->set('stoppaper_url', $this->cpurl($this->_module, $this->_operation, 'stoppaper', $this->_module_plugin_id));
		$this->view->set('tjdetail_url', $this->cpurl($this->_module, $this->_operation, 'tjdetail', $this->_module_plugin_id, array('id' => '')));
		$this->view->set('viewpaper_url', $this->cpurl($this->_module, $this->_operation, 'viewpaper', $this->_module_plugin_id, array('id' => '')));

		$this->output('office/exam/paperlist');
	}

	/**
	 * 重构搜索条件
	 * @param array $searchDefault 初始条件
	 * @param array $searchBy 输入的查询条件
	 * @param array $conditions 组合的查询
	 */
	protected function _parse_search_cond($search_default, &$search_conds, &$conditions) {
		foreach ( $search_default AS $_k=>$_v ) {
			if ( isset($_GET[$_k]) && $_v != $this->request->get($_k) ) {
				$hastatus = isset($_GET['status']) && intval($this->request->get('status')) >= 0;
				if ($_k == 'begin_time') {
					$search_conds[$_k] = $this->request->get($_k);
					$conditions['begin_time>=?'] = rstrtotime($this->request->get($_k));
				} elseif ($_k == 'end_time') {
					$search_conds[$_k] = $this->request->get($_k);
					$conditions['end_time<?'] = rstrtotime($this->request->get($_k));
				} elseif ($_k == 'name') {
					$search_conds[$_k] = $this->request->get($_k);
					$conditions['name LIKE ?'] = '%'.($this->request->get($_k)).'%';
				} elseif ($_k == 'status') {
					$search_conds[$_k] = $this->request->get($_k);
					$status = intval($this->request->get($_k));
					if($status == 0) {
						$conditions['status'] = 0;
					} elseif($status == 4) {
						$conditions['status'] = 2;
					} else {
						// 正常状态 重新设计时间
						//unset($conditions['begin_time>=?'], $conditions['end_time<?']);
						$conditions['status'] = 1;
						$currtime = time();
						if($status == 1) {
							// 未开始
							unset($conditions['begin_time>=?']);
							$conditions['begin_time>?'] = $currtime;
						} elseif($status == 2) {
							// 已开始
							unset($conditions['begin_time>=?']);
							$conditions['begin_time<=?'] = $currtime;
							$conditions['end_time>=?'] = $currtime;
						} else {
							// 已结束
							unset($conditions['end_time<?']);
							$conditions['end_time<?'] = $currtime;
						}


					}
				}
			}
		}

		return true;
	}
}
