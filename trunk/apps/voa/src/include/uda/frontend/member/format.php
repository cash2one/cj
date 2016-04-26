<?php
/**
 * voa_uda_frontend_member_format
 * 统一数据访问/用户表/用户信息格式化
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_member_format extends voa_uda_frontend_member_base {

	public $uda_department = NULL;
	public $uda_job = NULL;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化用户信息
	 * @param array $member
	 * @param array $get_fields 需要获取的字段
	 * @return boolean
	 */
	public function format(&$member, $get_fields = array()) {

		// 部门、职务名称
		$_department = $_job = '';

		// 部门名称
		if ($this->uda_department === null) {
			$this->uda_department = &uda::factory('voa_uda_frontend_department_common');
		}
		$this->uda_department->get_dpname($member['cd_id'], $_department);
		$member['_department'] = $_department;

		// 职务名称
		if ($this->uda_job === null) {
			$this->uda_job = &uda::factory('voa_uda_frontend_job_common');
		}
		$this->uda_job->get_jobname($member['cj_id'], $_job);
		$member['_job'] = $_job;
		// 头像
		$member['_face'] = voa_h_user::avatar($member['m_uid'], $member);
		// 性别
		$member['_gender'] = isset($this->gender_list[$member['m_gender']]) ? $this->gender_list[$member['m_gender']] : '';
		// 注册时间
		$member['_created'] = rgmdate($member['m_created'], 'Y-m-d H:i');
		// 个性化的注册时间
		$member['_created_u'] = rgmdate($member['m_created'], 'u');
		// 最后修改时间
		$member['_updated'] = rgmdate($member['m_updated'], 'Y-m-d H:i');
		// 个性化的最后修改时间
		$member['_updated_u'] = rgmdate($member['m_updated'], 'u');
		// 真实姓名
		$member['_realname'] = $member['m_username'];
		// 在职状态
		$member['_active'] = isset($this->active_list[$member['m_active']]) ? $this->active_list[$member['m_active']] : '';

		// 获取需要的字段数据
		if ($get_fields) {
			$new_member = array();
			foreach ($get_fields as $key) {
				$new_member[$key] = isset($member[$key]) ? $member[$key] : '';
			}
			$member = $new_member;
			unset($new_member);
		}
		return true;
	}

	/**
	 * 自一组数据内提取用户信息列表传值给 第3个参数：$member_list
	 * @param array $data
	 * @param string $uid_key
	 * @param array $member_list
	 * @return boolean
	 */
	public function data_list($data = array(), $uid_key = '', &$member_list) {

		if (!is_array($data)) {
			return true;
		}

		$m_uids = array();
		foreach ($data as $row) {
			if (isset($row[$uid_key]) && !isset($m_uids[$row[$uid_key]])) {
				$m_uids[$row[$uid_key]] = $row[$uid_key];
			}
		}
		unset($data, $row);

		$member_list = voa_h_user::get_multi($m_uids);
		foreach ($member_list as &$m) {
			$this->format($m);
		}
		unset($m_uids, $m);

		return true;
	}

	/**
	 * 格式化用户信息列表
	 * @param array $list
	 * @return boolean
	 */
	public function format_list(&$list) {
		foreach ($list as &$data) {
			$this->format($data);
		}

		return true;
	}

}
