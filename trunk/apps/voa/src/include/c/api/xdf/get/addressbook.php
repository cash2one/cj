<?php

/**
 * Class voa_c_api_xdf_get_addressbook
 * 接口/新东方/ 获取通讯录
 * @create-time: 2015-07-14
 * @author: huanw
 * @email: wanghuan@vchangyi.com
 */
class voa_c_api_xdf_get_addressbook extends voa_c_api_xdf_base {

	public function execute() {

		//签名合法性验证
		if (!$this->_validate_sig()) {
			$this->_set_errcode('102:invalid request address');
			return false;
		}
		//获取分页参数
		$addressbookList = array ();
		$total = 0;
		$params_limit = $this->request->get('limit');
		$limit = !empty($params_limit) ? $params_limit : false;
		$params_page = $this->request->get('page');
		$page = !empty($params_page) ? $params_page : 1;
		$conditions = array ();
		$allow_field = array (
			'realname',
			'mobilephone',
			'jobid',
			'departmentid'
		);
		$field_maps_flip = array_flip($this->_field_maps);
		// 初始化查询条件
		foreach ($this->request->getx() as $key => $val) {
			if (in_array($key, $allow_field) && $val) {
				if ($key == 'realname') {
					$conditions[$field_maps_flip[$key]] = array (
						"%$val%",
						'like'
					);
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
		} else {
			// 根据查询条件读取
			list($total, $pages, $addressbookList) = $this->_addressbook_search($conditions, $limit, $page);
		}
		// 解析通讯录
		$faces = array ();
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
			if (!empty($_user['m_face'])) {
				$faces[$_user['m_uid']] = $_user['m_face'];
				$_user['face'] = $_user['m_uid'];
				unset($_user['m_face']);
			} else {
				$_user['_' + $_user['m_gender']] = voa_d_oa_member::GENDER_MALE == $_user['m_gender'] ? voa_d_oa_member::GENDER_MALE : voa_d_oa_member::GENDER_FEMALE;
			}
			$addressbookList[$_id]['face'] = voa_h_user::avatar($_user['m_uid'], $_user);
			if (!empty($this->_departments[$_user['cd_id']])) {
				$addressbookList[$_id]['department'] = $this->_departments[$_user['cd_id']]['cd_name'];
			}
			if (!empty($this->_jobs[$_user['cj_id']])) {
				$addressbookList[$_id]['jobtitle'] = $this->_jobs[$_user['cj_id']]['cj_name'];
			}
		}
		// 输出结果
		$this->_result = array (
			'total'       => $total,
			'page'        => $page,
			'pages'       => $pages,
			'list'        => array_values($addressbookList),
			'faces'       => $faces,
			'departments' => $this->_departments,
			'jobs'        => $this->_jobs
		);
		return true;
	}

	/**
	 * 根据uid读取用户列表
	 * @param array $addrlist 返回通讯录列表
	 */
	protected function _list_by_uids(&$addrlist) {

		// 先获取uid列表
		$uda_mem = new voa_uda_frontend_member_get();
		$uids = array ();
		$uda_mem->sub_muids_by_muid($this->_member['m_uid'], $uids);
		// 根据uid读取用户信息
		$serv = &service::factory('voa_s_oa_member');
		$addrlist = $serv->fetch_all_by_ids($uids);
		return true;
	}

	/**
	 * 按指定条件搜索通讯录
	 * @param array $defaults 默认条件
	 * @param array $conditions
	 * @param number $perpage
	 * @param string $mpurl
	 * @return array
	 */
	protected function _addressbook_search($conds, $perpage, $current_page = 0) {

		// 检查部门id是否存在
		$departmentList = $this->_department_list();
		if (isset($conds['cd_id']) && is_numeric($conds['cd_id']) && $conds['cd_id'] > 0) {
			if (!isset($departmentList[$conds['cd_id']])) {
				unset($conds['cd_id']);
			}
		}
		// 检查职务id是否存在
		$jobList = $this->_job_list();
		if (isset($conds['cj_id']) && is_numeric($conds['cj_id']) && $conds['cj_id'] > 0) {
			if (!isset($jobList[$conds['cj_id']])) {
				unset($conds['cj_id']);
			}
		}
		// 检查在职状态是否存在
		$conds['m_active'] = 1;
		$total = 0;
		$pages = 0;
		// 列出全部数据
		if ($perpage === false) {
			$total = $this->_sev_member->count_by_conditions($conds);
			$pages = 1;
			$list = $this->_sev_member->fetch_addrbook($conds, implode(',', $this->_fields), array ('m_index' => 'ASC'), 0, 5000);
		} else {
			$list = array ();
			$total = $this->_sev_member->count_by_conditions($conds);
			if ($total > 0) {
				$pagerOptions = array (
					'total_items'      => $total,
					'current_page'     => $current_page,
					'per_page'         => $perpage,
					'show_total_items' => true
				);
				$pages = ceil($total / $perpage);
				pager::resolve_options($pagerOptions);
				$list = $this->_sev_member->fetch_addrbook($conds, implode(',', $this->_fields), array ('m_index' => 'ASC'), $pagerOptions['start'], $pagerOptions['per_page']);
			}
		}
		return array (
			$total,
			$pages,
			$list
		);
	}
}
