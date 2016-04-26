<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/1/25
 * Time: 下午9:59
 */

namespace Addressbook\Controller\Api;

abstract class AbstractController extends \Common\Controller\Api\AbstractController {

	// 部门缓存
	protected $_departments = array();
	// 职位
	protected $_jobs = array();

	// 人员性别
	const MALE = 1;
	const G_UNKNOWN= 0;
	const FMALE = 2;
	const C_MALE = '男';
	const C_G_UNKNOWN = '未知';
	const C_FMALE = '女';

	// 人员关注状态
	const QYWXST_FOLLOW = 1;
	const QYWXST_UNFOLLOW = 4;
	const QYWXST_FROZEN = 2;
	const C_FOLLOW = '已关注';
	const C_UNFOLLOW = '未关注';
	const C_FROZEN = '已禁用';

	// 规则 (开启)
	const ALLOW = 1;
	// 规则 (关闭)
	const UNALLOW = 0;

	public function before_action($action = '') {

		if (!parent::before_action($action)) {
			return false;
		}

		// 读取部门缓存
		$cache = &\Common\Common\Cache::instance();
		$this->_departments = $cache->get('Common.department');

		// 读取职位缓存
		$cache = &\Common\Common\Cache::instance();
		$this->_jobs = $cache->get('Common.job');
		return true;
	}

	/**
	 * 获取职位名称
	 * @param int $cj_id 职位ID
	 * @return bool
	 */
	protected function _get_cj_name($cj_id) {

		return isset($this->_jobs[$cj_id]) ? $this->_jobs[$cj_id]['cj_name'] : '';
	}

	/**
	 * 人员详情数据处理
	 * @param array $user_data
	 * @return bool
	 */
	protected function _format_user_data(&$user_data) {

		if (empty($user_data)) {
			return true;
		}

		// 性别
		switch ($user_data['m_gender']) {
			case self::G_UNKNOWN :
				$user_data['m_gender'] = self::C_G_UNKNOWN;
				break;
			case self::MALE :
				$user_data['m_gender'] = self::C_MALE;
				break;
			case self::FMALE :
				$user_data['m_gender'] = self::C_FMALE;
				break;
		}

		// 关注状态
		switch ($user_data['m_qywxstatus']) {
			case self::QYWXST_FOLLOW:
				$user_data['m_qywxstatus_name'] = self::C_FOLLOW;
				break;
			case self::QYWXST_UNFOLLOW:
				$user_data['m_qywxstatus_name'] = self::C_UNFOLLOW;
				break;
		}

		if ($user_data['m_active'] == self::UNALLOW) {
			$user_data['m_qywxstatus_name'] = self::C_FROZEN;
		}

		// 职位
		if (!empty($user_data['cj_id'])) {
			$user_data['cj_name'] = $this->_get_cj_name($user_data['cj_id']);
		}

		// 查询人员部门关联
		$serv_mem_department = D('Common/MemberDepartment', 'Service');
		$dep_list = $serv_mem_department->list_by_conds(array('m_uid' => $user_data['m_uid']));
		// 获取部门列表
		$cd_name = array();
		foreach ($dep_list as $_dep) {
			if (isset($this->_departments[$_dep['cd_id']])) {
				$cd_name[] = $this->_departments[$_dep['cd_id']]['cd_name'];
			}
		}
		$cd_name = implode('、', $cd_name);

		$user_data = array(
			'm_uid' => $user_data['m_uid'],
			'm_weixin' => empty($user_data['m_weixin']) ? '' : $user_data['m_weixin'],
			'm_username' => $user_data['m_username'],
			'm_active' => $user_data['m_active'],
			'm_mobilephone' => empty($user_data['m_mobilephone']) ? '' : $user_data['m_mobilephone'],
			'm_email' => empty($user_data['m_email']) ? '' : $user_data['m_email'],
			'm_qywxstatus_name' => $user_data['m_qywxstatus_name'],
			'm_gender' => $user_data['m_gender'],
			'cd_name' => empty($cd_name) ? '' : $cd_name,
			'cj_name' => empty($user_data['cj_name']) ? '' : $user_data['cj_name'],
			'm_face' => empty($user_data['m_face']) ? '' : $user_data['m_face'],
		);

		// 排除设置未显示属性
		$field = $this->_get_field();
		if (!empty($field)) {
			$_except = array(
				'gender' => 'm_gender',
				'mobile' => 'm_mobilephone',
				'weixinid' => 'm_weixin',
				'email' => 'm_email',
				'department' => 'cd_name',
			);
			foreach ($field['fixed'] as $_name => $_rule) {
				if ($_rule['view'] == self::UNALLOW) {
					unset($user_data[$_except[$_name]]);
				}
			}
		}

		return true;
	}

	/**
	 * 获取人员自定义 字段数据
	 * @param int $uid 用户uid
	 * @param array $custom 自定义信息
	 * @param array $mingan 敏感成员属性设置
	 * @return bool
	 */
	protected function _get_custom($uid, &$custom, $mingan) {

		$field = $this->_get_field();

		// 查询
		$serv_mem = D('Common/Member', 'Service');
		$serv_field = D('Common/MemberField', 'Service');
		$memfield = $serv_field->get_by_conds(array('m_uid' => $uid));

		// 选出开启的自定义字段 并且 显示
		$custom = array();
		if (empty($field['custom'])) {
			return true;
		}

		foreach ($field['custom'] as $_name => $_rule) {
			// 排除敏感成员数据
			if ($this->_login->user['m_uid'] != $uid) {
				if (!empty($mingan) && !in_array($_name, $mingan)) {
					continue;
				}
			}

			if ($_rule['open'] != self::ALLOW || $_rule['view'] != self::ALLOW) {
				continue;
			}

			// 直属领导
			if ($_name == 'leader') {
				if (empty($memfield['mf_' . $_name])) {
					continue;
				}
				$leader_id = explode(',', $memfield['mf_' . $_name]);
				$leader_list = $serv_mem->list_by_conds(array('m_uid' => $leader_id));
				$leader_name = '';
				if (!empty($leader_list)) {
					$leader_name = implode(',', array_column($leader_list, 'm_username'));
				}
				$custom[] = array(
					'name' => $_rule['name'],
					'value' => $leader_name,
				);

				continue;
			}

			// 过滤为空数据
			if (!empty($memfield['mf_' . $_name]) || $memfield['mf_' . $_name] === 0 || $memfield['mf_' . $_name] === '0') {
				$custom[] = array(
					'name' => $_rule['name'],
					'value' => $memfield['mf_' . $_name]
				);
			}
		}

		return true;
	}

	/**
	 * 获取人员属性规则
	 * @return array|mixed
	 */
	protected function _get_field() {

		// 获取设置缓存
		$cache = &\Common\Common\Cache::instance();
		$setting = $cache->get('Common.member_setting');

		return $setting['fields'];
	}

	/**
	 * 获取标签成员敏感属性
	 * @param       $m_uid
	 * @param array $mingan
	 * @return bool
	 */
	protected function _is_mingan($m_uid, &$mingan = array()) {

		// 敏感标签成员属性规则
		$sensitive = $this->_get_mingan();
		if (empty($sensitive)) {
			return true;
		}

		// 获取人员所在标签
		$serv_mingan = D('Common/CommonLabelMember', 'Service');
		$label_array = $serv_mingan->list_by_conds(array('m_uid' => $m_uid));
		$label = array_column($label_array, 'laid');
		if (empty($label)) {
			return true;
		}

		$temp = array();
		foreach ($sensitive as $_field) {
			// 人员所在标签 有设置规则
			$label_intersect = array_intersect($label, $_field['laid']);
			if (!empty($label_intersect)) {
				$temp[] = $_field['view'];
			}
		}

		// 可见属性
		foreach ($temp as $_mingan) {
			foreach ($_mingan as $_name => $_field) {
				if ($_field['view'] == self::ALLOW && !isset($mingan[$_name])) {
					$mingan[] = $_name;
				}
			}
		}

		return true;
	}

	/**
	 * 获取敏感成员规则
	 * @return array|mixed
	 */
	protected function _get_mingan() {

		// 获取设置缓存
		$cache = &\Common\Common\Cache::instance();
		$setting = $cache->get('Common.member_setting');

		return $setting['sensitive'];
	}
}
