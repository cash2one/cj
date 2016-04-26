<?php

/**
 * 内容详情
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/23
 * Time: 15:15
 */
class voa_c_admincp_api_banner_list extends voa_c_admincp_api_banner_base {

	public function execute() {

		$total = 0;
		$list = array();
		$searchBy = array();
		$multi = array();
		$post = $this->request->post('category');

		switch($post) {
			case 1://查询活动
				list($total, $multi, $list, $searchBy) = $this->_event_list();
				break;
			case 2://查询话题
				list($total, $multi, $list, $searchBy) = $this->_community_list();
				break;
			case 3://查询投票
				list($total, $multi, $list, $searchBy) = $this->_nvote_list();
				break;
		}


		$result = array(
			'total' => $total,
			'list' => $list,
			'multi' => $multi,
			'searchBy' => $searchBy,
		);

		return $this->_output_result($result);
	}


	/**
	 * 话题
	 * @return array
	 */
	protected function _community_list() {

		$search_default = array(
			'title' => '',
			'status' => '2',//话题发布
		);
		$search_conds = array();   //记住查询条件，填充到页面
		$conditions = array(); //供查询数据库用的查询条件
		$this->_parse_search_cond($search_default, $search_conds, $conditions);
		$issearch = $this->request->post('issearch') ? 1 : 0;

		//todo 二期项目修改
		$limit = 999;   // 每页显示数量
		$page = $this->request->get('page');   // 当前页码
		if (!is_numeric($page) || $page < 1) {
			$page = 1;
		}
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);
		$page_option = array($start, $limit);

		$list = array();
		$multi = null;
		$total = 0;
		try {
			// 载入搜索uda类
			$serv = &service::factory('voa_s_oa_community');

			// 实际查询条件
			$conditions = $issearch ? $conditions : array();
			$result = array();
			$orderby['created'] = 'DESC';
			$result = $serv->get_list_by_draft($conditions, $page_option, $orderby);
			$total = $serv->count_by_conds($conditions);
			if($total > 0){
				$pagerOptions = array(
					'total_items' => $total,
					'per_page' => $limit,
					'current_page' => $this->request->get('page'),
					'show_total_items' => true,
				);
				//$multi = pager::make_links($pagerOptions);
				//pager::resolve_options($pagerOptions);
				$multi = '';
			}
			$this->_community_formt($result, $list);
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}
		return array($total, $multi, $list, $conditions);

	}

	/**
	 * 活动列表
	 * @return array
	 */
	protected function _event_list() {

		/** 搜索默认值 */
		$searchDefaults = array(
			'title' => '',
			'ac_type' => '999',//全部
			'sort_num' => 0
		);
		$issearch = $this->request->get('issearch') ? 1 : 0;

		$searchBy = array();
		$conds = array();

		//查询条件
		foreach ($searchDefaults AS $_k => $_v) {
			if (isset($_GET[$_k]) && $this->request->get($_k) != $_v) {
				if ($this->request->get($_k) != null) {
					$searchBy[$_k] = $this->request->get($_k);
				} else {
					$searchBy[$_k] = $_v;
				}
			}
		}
		$searchBy = array_merge($searchDefaults, $searchBy);

		//组合搜索条件
		if (!empty($searchBy)) {

			$this->_add_condi($conds, $searchBy);

		}
		$list = array();
		$multi = null;
		$perpage = 999;
		//获取数据
		$serv = &service::factory('voa_s_oa_activity');
		$total = $serv->count_by_conds($conds);
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);

			$page_option[0] = $pagerOptions['start'];
			$page_option[1] = $perpage;
			$orderby['created'] = 'DESC';

			$result = $serv->list_by_conds($conds, $page_option, $orderby);
			$this->_event_formt($result, $list);
		}

		return array($total, $multi, $list, $searchBy);

	}

	/**
	 * 投票列表
	 * @return array
	 */
	protected function _nvote_list() {

		/** 搜索默认值 */
		$searchDefaults = array(
			'title' => '',
			'ac_type' => '999',//全部
		);
		$issearch = $this->request->get('issearch') ? 1 : 0;

		$searchBy = array();
		$conds = array();

		//查询条件
		foreach ($searchDefaults AS $_k => $_v) {
			if (isset($_GET[$_k]) && $this->request->get($_k) != $_v) {
				if ($this->request->get($_k) != null) {
					$searchBy[$_k] = $this->request->get($_k);
				} else {
					$searchBy[$_k] = $_v;
				}
			}
		}
		$searchBy = array_merge($searchDefaults, $searchBy);

		//组合搜索条件
		if (!empty($searchBy)) {

			$this->_add_condi($conds, $searchBy);

		}
		$list = array();
		$multi = null;
		$perpage = 999;
		//获取数据
		$serv = &service::factory('voa_s_oa_nvote');
		$total = $serv->count_by_conds($conds);
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);

			$page_option[0] = $pagerOptions['start'];
			$page_option[1] = $perpage;
			$orderby['created'] = 'DESC';

			$result = $serv->list_by_conds($conds, $page_option, $orderby);
			$this->_nvote_formt($result, $list);
		}

		return array($total, $multi, $list, $searchBy);

	}

	/**
	 *状态判断
	 * @param int conds
	 * @param array searchBy
	 */
	protected function _add_condi(&$conds, $searchBy) {

		if (!empty($searchBy['title'])) {//发起标题
			$conds["title like ?"] = "%" . $searchBy['title'] . "%";
		}

		//时间判断
		$time = time();//当前时间
		//未结束
		$conds['end_time >= ?'] = $time;

		$conds['is_stop'] = 0;

	}

	/**
	 * 格式化活动报名
	 * @param $request
	 * @param $result
	 * @return bool
	 */
	protected function _event_formt($request, &$result) {

		if (empty($request)) {
			$result = array();
			return true;
		}
		foreach ($request as $k => $v) {
			$result[$k]['eid'] = $v['acid'];
			$result[$k]['title'] = $v['title'];
			$result[$k]['thumb'] = $v['at_ids'];
			$result[$k]['converurl'] = voa_h_attach::attachment_url($v['at_ids']);
			$result[$k]['created'] = $v['created'];
		}

		return true;
	}

	/**
	 * 格式化话题
	 * @param $request
	 * @param $result
	 * @return bool
	 */
	protected function _community_formt($request, &$result) {

		if (empty($request)) {
			$result = array();
			return true;
		}
		foreach ($request as $k => $v) {
			$result[$k]['eid'] = $v['cid'];
			$result[$k]['title'] = $v['subject'];
			$result[$k]['thumb'] = $v['attach_id'];
			$result[$k]['converurl'] = voa_h_attach::attachment_url($v['attach_id']);
			$result[$k]['created'] = $v['created'];
		}

		return true;
	}

	/**
	 * 格式化投票
	 * @param $request
	 * @param $result
	 * @return bool
	 */
	protected function _nvote_formt($request, &$result) {

		if (empty($request)) {
			$result = array();
			return true;
		}
		foreach ($request as $k => $v) {
			$result[$k]['eid'] = $v['id'];
			$result[$k]['title'] = $v['subject'];
			$result[$k]['thumb'] = $v['thumb'];
			$result[$k]['converurl'] = voa_h_attach::attachment_url($v['thumb']);
			$result[$k]['created'] = $v['created'];
		}

		return true;
	}


	/**
	 * 重构搜索新闻条件
	 * @param array $searchDefault 初始条件
	 * @param array $searchBy 输入的查询条件
	 * @param array $conditons 组合的查询
	 */
	protected function _parse_search_cond($search_default, &$search_conds, &$conditons) {

		foreach ($search_default AS $_k => $_v) {
			if (isset($_GET[$_k]) && $_v != $this->request->get($_k)) {
				$search_conds[$_k] = $this->request->get($_k);
				if ($_k == 'title') {
					$conditons['title LIKE ?'] = '%' . ($this->request->get($_k)) . '%';
				}
			}
		}

		return true;
	}

}
