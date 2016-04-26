<?php
/**
 * 企业后台/微办公管理/活动
 * Create By linshiling
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_campaign_list extends voa_c_admincp_office_campaign_base {

	public function execute() {

		$searchDefault = array('subject' => '', 'typeid' => '-1', 'overtime_begin' => '', 'overtime_end' => '', 'is_push' => '-1');
		$searchBy = array();
		$conditions = array();
		$this->_parse_search_cond($searchDefault, $searchBy, $conditions);
		$issearch = $this->request->get('issearch') ? 1 : 0;

		$limit = 12; // 每页显示数量
		$page = $this->request->get('page'); // 当前页码
		if (! is_numeric($page) || $page < 1) {
			$page = 1;
		}

		// 载入搜索uda类
		$uda = &uda::factory('voa_uda_frontend_campaign_campaign');

		// 列出数据请求
		$pager = array(($page - 1) * $limit, $limit);
		// 数据结果
		$result = array();
		// 实际查询条件
		$conditions = $issearch ? $conditions : array();
		if (! $uda->ls($pager, $result, $conditions)) {
			$this->message('error', '获取列表出错');
			return;
		}

		// 分页链接信息
		$multi = '';
		if ($result['count'] > 0) {
			// 输出分页信息
			$multi = pager::make_links(array('total_items' => $result['count'], 'per_page' => $limit, 'current_page' => $page, 'show_total_items' => true));
		}

		// 取得分类列表
		$cats = voa_d_oa_campaign_type::get_type();

		// 注入模板变量
		$this->view->set('cats', $cats);
		$this->view->set('total', $result['count']);
		$this->view->set('list', $result['list']);
		$this->view->set('multi', $multi);
		$this->view->set('issearch', $this->request->get('issearch'));
		$this->view->set('searchBy', array_merge($searchDefault, $searchBy));

		// 设置模板使用的链接
		$this->seturl();

		// 输出模板
		$this->output('office/campaign/list');
	}

	/**
	 * 重构搜索条件
	 *
	 * @param array $searchDefault 初始条件
	 * @param array $searchBy 输入的查询条件
	 * @param array $conditons 组合的查询
	 */
	protected function _parse_search_cond($searchDefault, &$searchBy, &$conditons) {

		foreach ($searchDefault as $_k => $_v) {
			if (isset($_GET[$_k]) && $_v != $this->request->get($_k)) {
				$searchBy[$_k] = $this->request->get($_k);
				if ($_k == 'overtime_begin') {
					$conditons['overtime > ?'] = rstrtotime($this->request->get($_k));
				} elseif ($_k == 'overtime_end') {
					$conditons['overtime < ?'] = rstrtotime($this->request->get($_k)) + 86400;
				} elseif ($_k == 'subject') {
					$conditons['subject LIKE ?'] = '%' . ($this->request->get($_k)) . '%';
				} else {
					$conditons[$_k] = ($this->request->get($_k));
				}
			}
		}

		return true;
	}
}
