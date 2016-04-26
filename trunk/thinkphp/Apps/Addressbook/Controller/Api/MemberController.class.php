<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/1/25
 * Time: 下午10:01
 */

namespace Addressbook\Controller\Api;
use Common\Common\Department;

class MemberController extends AbstractController {

	// 名字搜索还是名字首字母搜索
	const SEARCH_NAME = 1;
	const SEARCH_INDEX = 2;

	// 每页最大个数
	const MAX_LIMIT = 500;

	/**
	 * 获取人员列表
	 * @return bool
	 */
	public function List_get() {

		// 获取部门id参数
		$kw = I('get.kw');
		// 搜索类型 1.名字 2.名字首字母
		$type = I('get.type', 1, 'intval');
		$page = I('get.page', 1, 'intval');
		$limit = I('get.limit', 50, 'intval');
		$cd_id = I('get.cd_id', 0, 'intval');
		$limit = min($limit, self::MAX_LIMIT);

		$condi = array();
		$serv_mem = D('Common/Member', 'Service');

		// 分页参数
		list($start, $limit, $page) = page_limit($page, $limit);
		// 分页参数
		$page_option = array($start, $limit);

		// 获取自己所在部门以及对应的所有上级部门
		$my_cdids = array();
		$p_cdids = array();
		if (!$this->_list_my_cdids($my_cdids, $p_cdids)) {
			E('_ERR_USER_DEPARTMENT_ERROR');
			return false;
		}

		// 如果是按关键字或者用户名称头字母索引进行搜索操作
		$count = 0;

		// 搜索操作
		if (!empty($kw)) {
			if (self::SEARCH_INDEX == $type) { // 按用户名索引搜索
				list($members, $count) = $this->_so_index_cdids($kw, $my_cdids, $page_option);
			} else { // 按关键字搜索
				list($memso_list, $count) = $this->_so_keyword_cdids($kw, $my_cdids, $page_option);
				$members = $serv_mem->list_by_pks(array_column($memso_list, 'm_uid'), array('m_index' => 'ASC'));
			}
			$count = $count[0]['count(*)'];
		} else {
			// 如果指定了部门id
			if (!empty($cd_id)) {
				$cdids = array($cd_id);
				$cdids = Department::instance()->list_childrens_by_cdid($cdids, true);
				// 如果部门权限是全公司
				if (empty($my_cdids)) {
					$my_cdids = $cdids;
				} else {
					// 指定的部门和 有权限查看的部门交集, 获取有权限能查看的部门
					$my_cdids = array_intersect($cdids, $my_cdids);

					if (empty($my_cdids)) {
						return true;
					}
				}
			}

			// 搜索用户
			$serv_mdp = D('Common/MemberDepartment', 'Service');
			if (empty($my_cdids)) {
				$members = $serv_mem->list_all($page_option, array('m_index' => 'ASC'));
				$count = $serv_mem->count();
			} else {
				$memso_list = $serv_mdp->list_by_cdid($my_cdids, $page_option);
				$count = $serv_mdp->count_by_cdid($my_cdids);
				$members = $serv_mem->list_by_pks(array_column($memso_list, 'm_uid'), array('m_index' => 'ASC'));
			}
		}

		// 获取职位缓存
		$cache = &\Common\Common\Cache::instance();
		$job = $cache->get('Common.job');
		$list = array();
		// 人员数据
		foreach ($members as $_mem) {
			$_mem['cj_name'] = empty($_mem['cj_id']) ? '' : $job[$_mem['cj_id']]['cj_name'];
			$index = substr($_mem['m_index'], 0, 1);
			// 如果首字母不在26个字母里
			if (empty($index) || !preg_match('/[a-z]/i', $index)) {
				$index = '#';
			}

			$list[$index][] = array(
				'm_uid' => $_mem['m_uid'],
				'm_username' => $_mem['m_username'],
				'm_face' => $_mem['m_face'],
				'cj_name' => $_mem['cj_name'],
				'index' => $index,
			);
		}
		$this->_sort_index($list);

		$this->_result = array(
			'list' => $list,
			'page' => $page,
			'count' => $count,
			'limit' => $limit,
		);

		return true;
	}

	/**
	 * 根据人员首字母 排序
	 * @param $list
	 * @return bool
	 */
	protected function _sort_index(&$list) {

		// 获取26个字符表
		$a_z = array();
		$a_z[] = '#';
		for ($i = 65; $i <=90; $i ++) {
			$a_z[] = chr($i);
		}

		$temp = array();
		foreach ($a_z as $_e) {
			if (isset($list[$_e])) {
				$temp = array_merge($temp, $list[$_e]);
			}
		}

		$list = $temp;

		return true;
	}

	/**
	 * 根据关键字和部门id进行检索
	 * @param string $kw 关键字
	 * @param array|int $my_cdids 部门id
	 * @param array|int $page_option 分页参数
	 * @return unknown
	 */
	protected function _so_keyword_cdids($kw, $my_cdids, $page_option) {

		$serv_so = D('Common/MemberSearch', 'Service');
		// 如果部门id为空, 则只搜索关键字
		if (empty($my_cdids)) {
			$ms_list = $serv_so->list_by_keyword($kw, $page_option, array('m_index' => 'ASC'));
			$count = $serv_so->count_by_keyword($kw);
		} else {
			$ms_list = $serv_so->list_by_keyword_cdids($kw, $my_cdids, $page_option, array('m_index' => 'ASC'));
			$count = $serv_so->count_by_keyword_cdids($kw, $my_cdids);
		}

		return array($ms_list, $count);
	}

	/**
	 * 根据索引和部门id进行检索
	 * @param string $index 索引字符
	 * @param array $my_cdids 部门id
	 * @param array|int $page_option 分页参数
	 * @return unknown
	 */
	protected function _so_index_cdids($index, $my_cdids, $page_option) {

		$serv_mem = D('Common/Member', 'Service');
		// 如果部门id为空, 则只搜索索引
		if (empty($my_cdids)) {
			$members = $serv_mem->list_by_index($index, $page_option, array('m_index' => 'ASC'));
			$count = $serv_mem->count_by_index($index);
		} else {
			$members = $serv_mem->list_by_index_cdids($index, $my_cdids, $page_option, array('m_index' => 'ASC'));
			$count = $serv_mem->count_by_index_cdids($index);
		}

		return array($members, $count);
	}

	/**
	 * 获取我的部门列表, 包括子部门和上级部门两部分
	 * @param array $my_cdids 有权限的部门
	 * @param array $p_cdids 上级部门
	 * @return boolean
	 */
	protected function _list_my_cdids(&$my_cdids, &$p_cdids) {

		$my_cdids = Department::instance()->list_cdid_by_uid($this->_login->user['m_uid']);
		// 检查部门权限
		$serv_dp = D('Common/CommonDepartment', 'Service');
		if (!$departments = $serv_dp->list_by_pks($my_cdids)) {
			return false;
		}

		// 遍历自己所在部门
		$my_cdids = array();
		foreach ($departments as $_dp) {
			// 如果有全公司的权限, 则
			if (\Common\Model\CommonDepartmentModel::PERMISSION_ALL == $_dp['cd_permission']) {
				$my_cdids = array();
				return true;
			}

			$my_cdids[$_dp['cd_id']] = $_dp['cd_id'];
			Department::instance()->list_parent_cdids($_dp['cd_id'], $p_cdids);
		}

		// 查询权限部门
		$conds_perm = array('cd_id' => $my_cdids);
		$serv_perm = D('Common/CommonDepartmentPermission', 'Service');
		$perm_list = $serv_perm->list_by_conds($conds_perm);
		foreach ($perm_list as $_addrdep) {
			$my_cdids[$_addrdep['per_id']] = $_addrdep['per_id'];
		}

		// 获取部门下所有子部门
		$my_cdids = Department::instance()->list_childrens_by_cdid($my_cdids, true);
		return true;
	}

	/**
	 * 获取人员详情
	 * @return bool
	 */
	public function View_get() {

		$uid = I('get.m_uid');
		// 判断提交是否为空
		if (empty($uid)) {
			E('_ERR_EMPTY_POST_UID');
			return false;
		}
		// 提交的 uid 不得为多个
		if (is_array($uid)) {
			E('_ERR_VIEW_UID_CAN_NOT_ARRAY');
			return false;
		}

		// 获取自己所在部门以及对应的所有上级部门
		$my_cdids = array();
		$p_cdids = array();
		if (!$this->_list_my_cdids($my_cdids, $p_cdids)) {
			E('_ERR_USER_DEPARTMENT_ERROR');
			return false;
		}
		// 获取查看用户所在的部门
		if (!empty($my_cdids)) {
			$serv_mem_dep = D('Common/MemberDepartment', 'Service');
			$dep_lists = $serv_mem_dep->list_by_conds(array('m_uid' => $uid));
			$mem_dep_cdids = array_column($dep_lists, 'cd_id');
			if (!array_intersect($my_cdids, $mem_dep_cdids)) {
				E('_ERR_EMPTY_USER_DATA_OR_NO_PERMISSION');

				return false;
			};
		}

		// 获取用户信息
		$serv_mem = D('Common/Member', 'Service');
		// 人员信息是否为空
		if (!$user_data = $serv_mem->get_by_conds(array('m_uid' => $uid))) {
			E('_ERR_EMPTY_USER_DATA');
			return false;
		}
		$this->_format_user_data($user_data);

		// 是否敏感成员
		if ($this->_login->user['m_uid'] != $uid) {
			$this->_is_mingan($uid, $mingan);
		}

		// 获取自定义字段
		$customs = array();
		$this->_get_custom($uid, $customs, $mingan);

		// 敏感成员属性设置
		if ($this->_login->user['m_uid'] != $uid) {
			$this->_view_mingan($user_data, $mingan);
		}

		$this->_result = array(
			'user_data' => $user_data,
			'custom' => $customs
		);

		return true;
	}

	/**
	 * 敏感成员fixed属性过滤
	 * @param $user_data
	 * @return bool
	 */
	protected function _view_mingan(&$user_data, $mingan) {

		if (!empty($mingan)) {
			$zhuanhuan = array(
				'name' => 'm_username',
				'gender' => 'm_gender',
				'mobile' => 'm_mobilephone',
				'weixinid' => 'm_weixin',
				'email' => 'm_email',
				'department' => 'cd_name',
				'position' => 'cj_name',
			);

			// 转换字段
			foreach ($zhuanhuan as $_name => $_value) {
				$key = array_search($_name, $mingan);
				if ($key !== false) {
					$mingan[$key] = $_value;
				}
			}

			// 不过滤的特殊字段
			$teshu = array(
				'm_face',
			);
			$mingan = array_merge($mingan, $teshu);

			foreach ($user_data as $_name => $_value) {
				if (!in_array($_name, $mingan)) {
					unset($user_data[$_name]);
				}
			}
		}

		return true;
	}
}
