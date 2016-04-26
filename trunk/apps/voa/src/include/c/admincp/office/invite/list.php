<?php
/**
 * voa_c_admincp_office_invite_list
* 企业后台/微办公管理/微社群/数据列表
* Create By HuangZhongZheng
* $Author$
* $Id$
*/
class voa_c_admincp_office_invite_list extends voa_c_admincp_office_invite_base {
	// 配置数据
	protected $_setting = null;
	public function execute() {
		$search_default = array(
			'name' => '',
			'email' => '',
			'm_uids' => '',
			'approval_state' => '-1',
			'invite_begintime' => '',
			'invite_endtime' => '',
			'm_qywxstatus' => ''
		);

		$search_conds = array();   //记住查询条件，填充到页面
		$conditions = array(); //供查询数据库用的查询条件
		$this->_parse_search_cond($search_default, $search_conds, $conditions);

		// 查询的邀请人
		if (!empty($search_conds['m_uids'])) {
			$user = voa_h_user::get($search_conds['m_uids']);
			$search_m_uid = array(
				'id' => $user['m_uid'],
				'name' => $user['m_username'],
			);
			$this->view->set('search_m_uid', $search_m_uid);
		}

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
			$uda_list = &uda::factory('voa_uda_frontend_invite_list');
			// 数据结果
			$result = array();
			// 实际查询条件
			$conditions = $issearch ? $conditions : array();
			$uda_list->list_invite($result, $conditions, $page_option);
			$resu = $result['list'];
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

		// 可邀请人列表,下拉框显示
		$primary_id_list_ex = explode(',', $this->_invite_setting['primary_id']);
		foreach ($primary_id_list_ex as $key => $val) {
			$user_name = voa_h_user::get($val);
			$primary_id_list[] = array(
				'id' => $val,
				'name' => $user_name['m_username'],
			);
		}

		// 注入模板变量
 		$this->view->set('total', $result['total']);
 		if($multi) $this->view->set('multi', $multi);
 		$this->view->set('list', $resu);
 		$this->view->set('data', $this->_setting);
 		$this->view->set('approval_state', $this->_approval_state);//是否需要审批
 		$this->view->set('issearch', $this->request->get('issearch'));
 		$this->view->set('search_conds', array_merge($search_default, $search_conds));
 		$this->view->set('view_url', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('per_id' => '')));
		$this->view->set('primary_id_list', $primary_id_list);
 		$this->view->set('list_url', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
 		$this->view->set('delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('per_id' => '')));
 		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));

		// 输出模板
		$this->output('office/invite/list');
	}

	/**
	 * 重构搜索条件
	 * @param array $searchDefault 初始条件
	 * @param array $searchBy 输入的查询条件
	 * @param array $conditons 组合的查询
	 */
	protected function _parse_search_cond($search_default, &$search_conds, &$conditons) {
		foreach ( $search_default AS $_k=>$_v ) {
			$value = $this->request->get($_k);
			if ( isset($_GET[$_k]) && $_v != $value) {
				$search_conds[$_k] = $value;

				if ($_k == 'invite_begintime') {
					$conditons['created>=?'] = rstrtotime($value);
				} elseif ($_k == 'invite_endtime') {
					$conditons['created<=?'] = rstrtotime($value);
				}elseif ($_k == 'm_uids') {
					$conditons['invite_uid =?'] = $value;
				}elseif ($_k == 'approval_state' && $value == "0" && $value !== "") {
					$conditons['approval_state < ?'] = voa_d_oa_invite_personnel::NO_CHECK;
				}elseif ($_k == 'approval_state' && $value == voa_d_oa_invite_personnel::NO_CHECK) {
					$conditons['approval_state = ?'] = voa_d_oa_invite_personnel::NO_CHECK;
				}elseif ($_k == 'email') {
					$conditons['email LIKE ?'] = ($this->request->get($_k)).'%';
				}elseif ($_k == 'name') {
					$conditons['name LIKE ?'] = ($this->request->get($_k)).'%';
				}
			}
		}
		return true;
	}

}
