<?php
/**
 * AbstractService.class.php
 * $author$
 */

namespace Sales\Service;

abstract class AbstractService extends \Common\Service\AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 获得用户信息
	 * @param $uid 用户ID
	 * @return array 用户信息
	 */
	protected function get_user($uid) {
		$serv_m = D('Common/Member', 'Service');
		return $serv_m->get($uid);
	}

	/**
	 * 获得用户信息和用户所属部门信息
	 * @param $uid 用户ID
	 * @return array 用户信息,用户所属部门级联关系,用户所属部门信息
	 */
	protected function get_member_department_all($m_uid) {
		//获取用户基本信息
		$member = $this->get_user($m_uid);
		$cd_id = $member['cd_id'];
		//获取用户所属部门级关系
		$serv_d = D('Common/MemberDepartment', 'Service');
		$departments = $serv_d->list_by_cdid($cd_id);

		$member_dp_array = array();
		foreach ($departments as $mda){
			$member_dp_array[] = $mda['mp_id'];
		}
		//获取职位信息
		$serv_p = D('Common/MemberPosition', 'Service');
		$member_position = $serv_p->get_member_position($member_dp_array);
		//$mp_name = $member_position['mp_name'];

		$member_info_array = array(
			'member' => $member,
			'member_department' => $member_department_array,
			'member_position' => $member_position
		);

		return $member_info_array;
	}

	/**
	 * 获得用户信息和用户所属部门信息
	 * @param $uid 用户ID
	 * @return array 用户信息,用户所属部门级联关系,用户所属部门信息
	 */
	protected function get_member_department($m_uid) {

		//获取用户基本信息
		$member = $this->get_user($m_uid);
		$cd_id = $member['cd_id'];
		//获取用户所属部门级关系
		$serv_d = D('Common/MemberDepartment', 'Service');
		$departments = $serv_d->list_by_cdid($cd_id);

		$member_dp_array = array();
		foreach ($departments as $mda){
			$member_dp_array[] = $mda['mp_id'];
		}
		//获取职位信息
		$serv_p = D('Common/MemberPosition', 'Service');
		$member_position_array = $serv_p->get_member_position($member_dp_array);

		//拼接用户所在组
		$mp_name = '';
		foreach ($member_position_array as $member_position){
			$mp_name += $mp_name .' '. $member_position['mp_name'];
		}

		$member_info = array(
			'm_username' => $member['m_username'],
			'mp_name' => $mp_name
		);

		return $member_info;
	}
}
