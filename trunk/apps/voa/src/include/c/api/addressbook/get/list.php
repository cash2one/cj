<?php

class voa_c_api_addressbook_get_list extends voa_c_api_addressbook_base {
	// 字段信息
	protected $_fields = array(
		'm_uid', 'm_username', 'm_mobilephone', 'cd_id',
		'cj_id', 'm_index', 'm_face', 'm_gender'
	);

	public function execute() {

		// 待返回的数据
		$data = array();
		// 定义有效的请求参数数组
		// $this->_params;
		// $param_allow = array('var1', 'var2', 'var3', 'var4');
		$addressbookList = array();
		$total = 0;
		// $multi = '';
		$limit = ! empty($this->_params['limit']) ? $this->_params['limit'] : false;
		$limit = ($limit > 100 ? 100 : $limit);
		$page = ! empty($this->_params['page']) ? $this->_params['page'] : 1;
		$conditions = array();
		$allow_field = array('realname', 'mobilephone', 'jobid', 'departmentid');
		$field_maps_flip = array_flip($this->_field_maps);
		// 初始化查询条件
		foreach ($this->_params as $key => $val) {
			if (in_array($key, $allow_field) && $val) {
				if ($key == 'realname') {
					$conditions[$field_maps_flip[$key]] = array("%$val%", 'like');
				} else {
					$conditions[$field_maps_flip[$key]] = $val;
				}
			}
		}

		// 根据用户UID读取
		$ac = $this->request->get('ac', '');
		$pages = 0;
		if ('byuser' == $ac) {
			$this->_list_by_uids($addressbookList);
		} else { // 根据查询条件读取
			list($total, $pages, $addressbookList) = $this->_addressbook_search($conditions, $limit, $page);
		}

		// 解析通讯录
		//$index2fields = array();
		$faces = array();
		//$faces[voa_d_oa_member::GENDER_MALE] = voa_h_user::avatar(0, array('m_gender' => voa_d_oa_member::GENDER_MALE));
		//$faces[voa_d_oa_member::GENDER_FEMALE] = voa_h_user::avatar(0, array('m_gender' => voa_d_oa_member::GENDER_FEMALE));

		foreach ($addressbookList as $_id => $_user) {
			// 遍历所有字段
			foreach ($this->_fields as $_k => $_f) {
				// 如果不存在于字段对照表, 则
				if (empty($this->_field_maps[$_f])) {
					continue;
				}

				$addressbookList[$_id][$this->_field_maps[$_f]] = $_user[$_f];
				unset($addressbookList[$_id][$_f]);
			}
			// 如果头像存在
			/**if (!empty($_user['m_face'])) {
				$faces[$_user['m_uid']] = $_user['m_face'];
				$_user['face'] = $_user['m_uid'];
				unset($_user['m_face']);
			} else {
				$_user['_' + $_user['m_gender']] = voa_d_oa_member::GENDER_MALE == $_user['m_gender'] ? voa_d_oa_member::GENDER_MALE : voa_d_oa_member::GENDER_FEMALE;
			}*/
			$addressbookList[$_id]['face'] = voa_h_user::avatar($_user['m_uid'], $_user);

			if (!empty($this->_departments[$_user['cd_id']])) {
				$addressbookList[$_id]['department'] = $this->_departments[$_user['cd_id']]['cd_name'];
			}

			if (!empty($this->_jobs[$_user['cj_id']])) {
				$addressbookList[$_id]['jobtitle'] = $this->_jobs[$_user['cj_id']]['cj_name'];
			}
		}

		/**foreach ($this->_fields as $_k => $_f) {
			if (empty($this->_field_maps[$_f])) {
				continue;
			}

			$index2fields[$_k] = $this->_field_maps[$_f];
		}*/

		/**$list = $addressbookList;

		for ($i = 0; $i < 80; $i ++) {
			foreach ($list as $_v) {
				$_v['mobilephone'] = '13588119714';
				$addressbookList[] = $_v;
			}
		}*/

		// 输出结果
		$this->_result = array(
			'total' => $total, 'page' => $page, 'pages' => $pages,
			'list' => array_values($addressbookList),
			'faces' => $faces,
			'departments' => $this->_departments,
			'jobs' => $this->_jobs
			//, 'index2field' => $index2fields
		);
		return;
	}

	/**
	 * 根据uid读取用户列表
	 * @param array $addrlist 返回通讯录列表
	 */
	protected function _list_by_uids(&$addrlist) {

		// 先获取uid列表
		$uda_mem = new voa_uda_frontend_member_get();
		$uids = array();
		$addressbookList = $uda_mem->sub_muids_by_muid($this->_member['m_uid'], $uids);

		// 根据uid读取用户信息
		$serv = &service::factory('voa_s_oa_member');
		$addrlist = $serv->fetch_all_by_ids($uids);
		return true;
	}

	/**
	 * 按指定条件搜索通讯录
	 *
	 * @param array $defaults 默认条件
	 * @param array $conditions
	 * @param number $perpage
	 * @param string $mpurl
	 * @return array
	 */
	protected function _addressbook_search($conds, $perpage, $current_page = 0) {

		/**
		 * foreach ($defaults AS $k=>$v) {
		 * if (isset($conditions[$k]) && is_scalar($conditions[$k]) && $v != $conditions[$k]) {
		 * $conds[$k] = $conditions[$k];
		 * }
		 * }
		 */
		// 检查部门id是否存在
		$departmentList = $this->_department_list();
		if (isset($conds['cd_id']) && is_numeric($conds['cd_id']) && $conds['cd_id'] > 0) {
			if (! isset($departmentList[$conds['cd_id']])) {
				unset($conds['cd_id']);
			}
		}

		// 检查职务id是否存在
		$jobList = $this->_job_list();
		if (isset($conds['cj_id']) && is_numeric($conds['cj_id']) && $conds['cj_id'] > 0) {
			if (! isset($jobList[$conds['cj_id']])) {
				unset($conds['cj_id']);
			}
		}

		// 检查在职状态是否存在
		$conds['m_active'] = 1;
		/**
		 * if (isset($conds['cab_active']) && !isset($this->_status_active_description[$conds['cab_active']])) {
		 * unset($conds['cab_active']);
		 * }
		 */
		$list = array();
		$total = 0;
		$multi = '';
		$pages = 0;
		// 列出全部数据
		if (true || $perpage === false) {
			$list = $this->_sev_member->fetch_addrbook($conds, implode(',', $this->_fields), array('m_displayorder' => 'DESC', 'm_index' => 'ASC'), 0, 5000);
			// $tmp = $this->_service_single('member', 'fetch_all_by_conditions', $conds, true, true);
			//$list = $this->_addressbook_list_format($tmp);
			//unset($tmp);
		} else {
			$list = array();
			$total = $this->_sev_member->count_by_conditions($conds);
			// $total = $this->_service_single('member', 'count_by_conditions', $conds, true);
			$multi = '';
			if ($total > 0) {
				$pagerOptions = array(
					'total_items' => $total,
					// 'per_page' => $perpage, /** 一次性, 全返回 */
					'current_page' => $current_page,
					'show_total_items' => true
				);
				$pages = ceil($total / $perpage);
				// $multi = pager::make_links($pagerOptions);
				pager::resolve_options($pagerOptions);
				$list = $this->_sev_member->fetch_addrbook($conds, implode(',', $this->_fields), array('m_displayorder' => 'DESC', 'm_index' => 'ASC'), $pagerOptions['start'], $pagerOptions['per_page']);
				// $tmp = $this->_service_single('member', 'fetch_all_by_conditions', $conds, $pagerOptions['start'], $pagerOptions['per_page'], true);
				//$list = $this->_addressbook_list_format($tmp);
				//unset($tmp);
			}
		}

		return array($total, $pages, $list);
	}

}
