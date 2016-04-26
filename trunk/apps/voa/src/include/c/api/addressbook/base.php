<?php

class voa_c_api_addressbook_base extends voa_c_api_base {
	protected $_addressbook_uda_get;
	protected $_addressbook_uda_update;
	// 部门表字段与输出字段名映射关系
	protected $_department_field_maps = array(
		'cd_id' => 'departmentid', 'cd_upid' => 'parentid', 'cd_usernum' => 'usernum',
		'cd_name' => 'name', 'cd_displayorder' => 'displayorder'
	);
	// _sev_member
	protected $_sev_member;
	protected $_sev_member_field;
	protected $_sev_common_department;
	protected $_sev_common_job;
	// 通讯录表字段与输出字段名映射关系
	public $_field_maps = array(
		'm_openid' => 'openid', 'm_face' => 'face', 'm_index' => 'alphaindex',
		'm_username' => 'realname', 'm_uid' => 'uid', 'm_mobilephone' => 'mobilephone',
		'm_gender' => 'gender', 'm_active' => 'active', 'm_email' => 'email',
		'm_unionid' => 'weixinid', 'cj_id' => 'jobid', 'cd_id' => 'departmentid',
		'm_created' => 'created', 'm_deleted' => 'deleted', 'm_status' => 'status',
		'm_qywxstatus' => 'qywxstatus',
		//'mf_address' => 'address', 'mf_idcard' => 'idcard', 'mf_telephone' => 'telephone',
		//'mf_qq' => 'qq', 'mf_birthday' => 'birthday', 'mf_remark' => 'remark',
	);
	/**
	 * 性别文字描述
	 *
	 * @var array
	 */
	protected $_status_gender_description = array(0 => '未设置', 1 => '男', 2 => '女');
	/**
	 * 在职状态文字描述
	 *
	 * @var array
	 */
	protected $_status_active_description = array(0 => '离职', 1 => '在职');
	// 部门和职位
	protected $_departments = array();
	protected $_jobs = array();
	/** 后台的cookie信息 */
	private $__cookie_data = array();

	protected function _access_check() {

		if (!parent::_access_check()) {
			// 取后台登录信息
			$uda_member_get = &uda::factory('voa_uda_frontend_adminer_get');
			// cookie 信息
			$uda_member_get->adminer_auth_by_cookie($this->__cookie_data, $this->session);
			if (!empty($this->__cookie_data['uid']) && 0 < $this->__cookie_data['uid']) {
				// 如果后台登陆信息存在, 则清理前台登陆账号
				$this->session->remove('uid');
				$this->_require_login = false;

				return true;
			} else {
				return false;
			}
		}

		return true;
	}

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_departments = voa_h_cache::get_instance()->get('department', 'oa');
		$this->_jobs = voa_h_cache::get_instance()->get('job', 'oa');

		$uda_get = &uda::factory('voa_uda_frontend_addressbook_get');
		$uda_update = &uda::factory('voa_uda_frontend_addressbook_update');
		$this->_addressbook_uda_update = $uda_update;
		$this->_addressbook_uda_get = $uda_get;
		// $this->_field_maps = $uda_get->_field_maps;
		$this->_sev_member = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$this->_sev_common_department = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
		$this->_sev_common_job = &service::factory('voa_s_oa_common_job', array('pluginid' => 0));
		$this->_sev_member_field = &service::factory('voa_s_oa_member_field', array('pluginid' => 0));

		// Start2 前后台用户
		// 如果是普通用户
		if (0 < startup_env::get('wbs_uid') || (!$this->_require_login && empty($this->__cookie_data['uid']))) {
			return true;
		}

		// 取管理员配置
		$serv_common_adminer = &service::factory('voa_s_oa_common_adminer');
		if (!$adminer = $serv_common_adminer->fetch($this->__cookie_data['uid'])) {
			$this->_set_errcode(voa_errcode_oa_travel::PLEASE_LOGIN);
			$this->_output();
			return false;
		}

		// 取用户信息
		$serv_mem = &service::factory('voa_s_oa_member');
		if (!$this->_member = $serv_mem->fetch_by_mobilephone($adminer['ca_mobilephone'])) {
			/**
			 * $this->_set_errcode(voa_errcode_oa_travel::PLEASE_LOGIN);
			 * $this->_output();
			 * return true;
			 */
			$this->_member = array('m_uid' => 1, 'm_username' => 'admin');
		}

		// 管理员标识
		$this->_is_admin = 1;
		// End2
		return true;
	}

	/**
	 * 格式化通讯录数据用以显示
	 *
	 * @param array $addressbookList
	 * @return array
	 */
	protected function _addressbook_list_format($addressbookList) {

		$list = array();
		if (!is_array($addressbookList) || empty($addressbookList)) {
			return $list;
		}

		// $memberEditBaseUrl = $this->cpurl('manage', 'member', 'edit', '', array('m_uid'=>''));
		foreach ($addressbookList as $_cab_id => $_cab) {
			$item = $this->_addressbook_format($_cab);
			$list[] = $item;
		}

		unset($addressbookList);
		return $list;
	}

	protected function _addressbook_format($_cab) {

		//$departmentList = $this->_department_list();
		//$jobList = $this->_job_list();
		//$member_fields = $this->_sev_member_field->fetch_by_id($_cab['m_uid']);
		//$_cab = array_merge($_cab, $member_fields);
		$item = array();
		if (empty($_cab)) {
			return $item;
		}

		foreach ($_cab as $key => $val) {
			if (!empty($this->_field_maps[$key]) && $this->_field_maps[$key] != 'face') {
				/**if ($key == 'cd_id') {
					$item['department'] = $_cab['cd_id'] ? (isset($this->_departments[$_cab['cd_id']]) ? $this->_departments[$_cab['cd_id']]['cd_name'] : '') : '--';
					$item['jobtitle'] = $_cab['cd_id'] ? (isset($this->_jobs[$_cab['cj_id']]) ? $this->_jobs[$_cab['cj_id']]['cj_name'] : '') : '--';
				} else*/
				if ($key == 'm_uid' && !empty($val)) {
					$item['face'] = voa_h_user::avatar($val, $_cab);
				}

				$item[$this->_field_maps[$key]] = $val;
			}
		}

		return $item;
	}

	protected function _department_format($_cab) {

		$item = array();
		if (!empty($_cab)) {
			foreach ($_cab as $key => $val) {
				if (!empty($this->_department_field_maps[$key])) {
					$item[$this->_department_field_maps[$key]] = $val;
				}
			}
		}

		return $item;
	}

	/**
	 * (admincp/base) 返回所有部门列表
	 *
	 * @return array
	 */
	protected function _department_list($force = false) {

		if (!$force && isset($this->_department_list_)) {
			return $this->_department_list_;
		}

		$list = array();
		$tmp = $this->_sev_common_department->fetch_all(array());
		foreach ($tmp as $_cd) {
			$_cd_id = intval($_cd['cd_id']);
			$cd = array();
			$cd['cd_name'] = $_cd['cd_name'];
			$cd['cd_displayorder'] = $_cd['cd_displayorder'];
			$cd['_update'] = rgmdate($_cd['cd_updated'], 'Y-m-d H:i');
			$cd['cd_usernum'] = $_cd['cd_usernum'];
			$list[$_cd_id] = $cd;
		}

		$this->_department_list_ = $list;
		unset($tmp, $_cd, $cd, $list);
		return $this->_department_list_;
	}

	/**
	 * (admincp/base) 返回所有职务列表
	 *
	 * @return array
	 */
	protected function _job_list($force = false) {

		if (!$force && isset($this->_job_list_)) {
			return $this->_job_list_;
		}

		$list = array();
		$tmp = $this->_sev_common_job->fetch_all(array());
		foreach ($tmp as $_cj) {
			$_cj_id = intval($_cj['cj_id']);
			$cj = array();
			$cj['cj_name'] = $_cj['cj_name'];
			$cj['cj_displayorder'] = $_cj['cj_displayorder'];
			$cj['_update'] = rgmdate($_cj['cj_updated'], 'Y-m-d H:i');
			$list[$_cj_id] = $cj;
		}

		$this->_job_list_ = $list;
		unset($tmp, $_cj, $cj, $list);
		return $this->_job_list_;
	}

}
