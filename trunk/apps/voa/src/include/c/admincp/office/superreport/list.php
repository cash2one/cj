<?php
/**
 * voa_c_admincp_office_superreport_list
* 企业后台/微办公管理/超级报表/数据列表
* Create By YanWenzhong
* $Author$
* $Id$
*/
class voa_c_admincp_office_superreport_list extends voa_c_admincp_office_superreport_base {

	public function execute() {
		$search_default = array(
			'contacts' => '',
			'name' => '',
			'placeregionid' => '',
			'created_begintime' => '',
			'created_endtime' => '',
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
			$uda_list = &uda::factory('voa_uda_frontend_superreport_list');
			// 数据结果
			$result = array();
			// 实际查询条件
			$conditions = $issearch ? $conditions : array();
			$uda_list->result($result, $conditions, $page_option);
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


		$usernames = array();
		$placenames = array();
		if ($result['list']) {
			//获取所有用户名
			$uids = array_column($result['list'], 'm_uid');
			$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $servm->fetch_all_by_ids($uids);
			$usernames = array_column($users, 'm_username','m_uid');

			//获取所有门店名称
			$placeids = array_column($result['list'], 'csp_id');
			$place_result = array();
			$uda_place_list = &uda::factory('voa_uda_frontend_common_place_list');
			$place_cond = array(
				'name' => '',
				'placeregionid' => '',
				'palceid' => $placeids,
				'placetypeid' => $this->_p_sets['placetypeid'],
				'address' => '',
				'lng' => '',
				'lat' => '',
			);
			$uda_place_list->doit($place_cond, $place_result);//print_r($place_result);die;
			$placenames = array_column($place_result['result'], 'name','placeid');

			foreach ($result['list'] as &$v) {
				$v['area'] =  isset($place_result['placeregion'][$v['csp_id']]) ? $this->__union_area($place_result['placeregion'][$v['csp_id']]) : '';
			}
		} else {
			$result['list'] = array();
		}

		//获取已选择的权限
		$contacts =  $this->request->get('contacts');
		$deps =  $this->request->get('deps');
		$existed_rights = $this->__get_rights_info($contacts, $deps);

		// 注入模板变量
		$this->view->set('total', $result['total']);
		$this->view->set('list', $result['list']);
		$this->view->set('usernames', $usernames);
		$this->view->set('placenames', $placenames);
		$this->view->set('multi', $multi);
		$this->view->set('issearch', $this->request->get('issearch'));
		$this->view->set('existed_rights', rjson_encode($existed_rights));
		$this->view->set('search_conds', array_merge($search_default, $search_conds));
		$this->view->set('view_url', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('dr_id'=>'')));
		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));

		// 输出模板
		$this->output('office/superreport/list');
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
				$conditons[$_k] = $this->request->get($_k);
			}
		}
		return true;
	}

	/**
	 * 取回有权限查看的部门及人员信息
	 * @param array $arr 信息数组
	 */
	private function __get_rights_info ($contacts, $deps) {

		$existed_rights = array();
		if (!empty($contacts)) { //取回有权限查看文章的人员信息
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $serv_m->fetch_all_by_ids($contacts);
			if ($users) {
				foreach ($users as $user) {
					$existed_rights[] = array(
						'id' => $user['m_uid'],
						'name' => $user['m_username'],
						'input_name' => 'contacts[]'
					);
				}
			}
		}
		if (!empty($deps)) { //取回有权限查看文章的部门信息
			$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
			$depms = $serv_d->fetch_all_by_key($deps);
			if ($depms) {
				foreach ($depms as $dep) {
					$existed_rights[] = array(
						'id' => $dep['cd_id'],
						'name' => $dep['cd_name'],
						'input_name' => 'deps[]'
					);
				}
			}
		}

		return $existed_rights;
	}


	/**
	 * 拼接各级区域
	 * @param array $area
	 * @return string
	 */
	private  function __union_area($area) {

		$str = '';
		if (isset($area) && !empty ($area)) {
			ksort($area);
			$arr = array_column($area, 'name');
			$str = implode('-', $arr);
		}

		return $str;
	}

}
