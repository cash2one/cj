<?php
/**
 * voa_c_admincp_office_train_cglist
 * 企业后台/微办公管理/培训/目录列表
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_train_cglist extends voa_c_admincp_office_train_base {

	public function execute() {
		$searchDefault = array(
			'title' => '',
			'contacts' => '',
			'deps' => '',
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
		$uda_categorylist = &uda::factory('voa_uda_frontend_train_action_categorylist');
		// 列出数据请求
		$pager = array( ($page-1)*$limit, $limit );
		// 数据结果
		$result = array();
		// 实际查询条件
		$conditions = $issearch ? $conditions : array();
		if (!$uda_categorylist->result($pager, $result, $conditions)) {
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

		//获取已选择的权限
		$contacts =  $this->request->get('contacts');
		$deps =  $this->request->get('deps');
		$this->__get_rights_info($contacts, $deps);

		// 注入模板变量
		$this->view->set('total', $result['count']);
		$this->view->set('list', $result['list']);
		$this->view->set('multi', $multi);
		$this->view->set('contacts', rjson_encode(array_values($contacts)));
		$this->view->set('deps', rjson_encode(array_values($deps)));
		$this->view->set('issearch', $this->request->get('issearch'));
		$this->view->set('searchBy', array_merge($searchDefault, $searchBy));
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'cgdelete', $this->_module_plugin_id, array('tc_id'=>'')));
		$this->view->set('editUrlBase', $this->cpurl($this->_module, $this->_operation, 'cgedit', $this->_module_plugin_id, array('tc_id'=>'')));
		$this->view->set('formDeleteUrl', $this->cpurl($this->_module, $this->_operation, 'cgdelete', $this->_module_plugin_id));
		$this->view->set('listAllUrl', $this->cpurl($this->_module, $this->_operation, 'cglist', $this->_module_plugin_id));

		// 输出模板
		$this->output('office/train/category_list');
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
				if ($_k == 'title') {
					$conditons['title LIKE ?'] = '%'.($this->request->get($_k)).'%';
				} elseif ($_k == 'contacts') {
					$conditons['m_uid'] = (array)$this->request->get($_k);
				} elseif ($_k == 'deps') {
					$conditons['cd_id'] = (array)$this->request->get($_k);
				} else {
					$conditons[$_k] = ($this->request->get($_k));
				}
			}
		}
		return true;
	}

	/**
	 * 取回有权限查看的部门及人员信息
	 * @param array $arr 信息数组
	 */
	private function __get_rights_info (&$contacts, &$deps) {

		$existed_rights = array();
		$temp = array();
		if (!empty($contacts)) { //取回有权限查看文章的人员信息
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $serv_m->fetch_all_by_ids($contacts);
			if ($users) {
				foreach ($users as $user) {
					$temp[] = array(
						'm_uid' => $user['m_uid'],
						'm_username' => $user['m_username'],
						'selected' => (bool)true
					);
				}
			}
			$contacts = $temp;
		} else {
			$contacts = array();
		}
		$temp = array();
		if (!empty($deps)) { //取回有权限查看文章的部门信息
			$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
			$depms = $serv_d->fetch_all_by_key($deps);
			if ($depms) {
				foreach ($depms as $dep) {
					$temp[] = array(
						'id' => $dep['cd_id'],
						'cd_name' => $dep['cd_name'],
						'isChecked' => (bool)true
					);
				}
			}
			$deps = $temp;
		} else {
			$deps = array();
		}

		return true;
	}


}
