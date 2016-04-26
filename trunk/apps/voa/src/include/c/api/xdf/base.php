<?php

/**
 * Class voa_c_api_xdf_base
 * 接口/新东方/基本控制
 * @create-time: 2015-06-17
 * @author: huanw
 * @email: wanghuan@vchangyi.com
 */
class voa_c_api_xdf_base extends voa_c_api_base {

	//二维码url基本路径
	public $qrcode_url_base = '';

	// 部门和职位
	protected $_departments = array ();
	protected $_jobs = array ();

	// _sev_member
	protected $_sev_member;
	protected $_sev_member_field;
	protected $_sev_common_department;
	protected $_sev_common_job;

	// 通讯录表字段与输出字段名映射关系
	public $_field_maps = array (
		'm_openid'      => 'openid',
		'm_face'        => 'face',
		'm_index'       => 'alphaindex',
		'm_username'    => 'realname',
		'm_uid'         => 'uid',
		'm_mobilephone' => 'mobilephone',
		'm_gender'      => 'gender',
		'm_active'      => 'active',
		'm_email'       => 'email',
		'm_unionid'     => 'weixinid',
		'cj_id'         => 'jobid',
		'cd_id'         => 'departmentid',
		'm_created'     => 'created',
		'm_deleted'     => 'deleted',
		'm_status'      => 'status',
		'm_qywxstatus'  => 'qywxstatus'
	);

	// 通讯录字段信息
	protected $_fields = array (
		'm_uid',
		'm_username',
		'm_mobilephone',
		'cd_id',
		'cj_id',
		'm_index',
		'm_face',
		'm_gender'
	);

	protected function _before_action($action) {

		// 不需要登录
		$this->_require_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}
		$this->_departments = voa_h_cache::get_instance()->get('department', 'oa');
		$this->_jobs = voa_h_cache::get_instance()->get('job', 'oa');
		$this->_sev_member = &service::factory('voa_s_oa_member', array ('pluginid' => 0));
		$this->_sev_common_department = &service::factory('voa_s_oa_common_department', array ('pluginid' => 0));
		$this->_sev_common_job = &service::factory('voa_s_oa_common_job', array ('pluginid' => 0));
		$this->_sev_member_field = &service::factory('voa_s_oa_member_field', array ('pluginid' => 0));
		$this->qrcode_url_base = config::get("voa.oa_http_scheme").$_SERVER['HTTP_HOST'].'/frontend/xdf/loginqrcode';
		return true;
	}

	protected function _after_action($action) {

		parent::_after_action($action);
		return true;
	}

	/**
	 * 外部请求url合法性验证f
	 * @return bool
	 */
	protected function _validate_sig() {

		//获取参数
		$params = $this->request->getx();
		//验证签名的合法性
		$result = voa_h_login::sig_check($params);
		if ($result) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * (admincp/base) 返回所有部门列表
	 * @return array
	 */
	protected function _department_list($force = false) {

		if (!$force && isset($this->_departments)) {
			return $this->_departments;
		}

		$list = array ();
		$tmp = $this->_sev_common_department->fetch_all(array ());

		foreach ($tmp as $_cd) {
			$_cd_id = intval($_cd['cd_id']);
			$cd = array ();
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
	 * @return array
	 */
	protected function _job_list($force = false) {

		if (!$force && isset($this->_job_list_)) {
			return $this->_job_list_;
		}

		$list = array ();
		$tmp = $this->_sev_common_job->fetch_all(array ());

		foreach ($tmp as $_cj) {
			$_cj_id = intval($_cj['cj_id']);
			$cj = array ();
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
