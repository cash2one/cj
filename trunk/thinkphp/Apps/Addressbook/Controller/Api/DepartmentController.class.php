<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/12/18
 * Time: 下午2:03
 */

namespace Addressbook\Controller\Api;
use Common\Common\Wxqy\Addrbook;
use Common\Common\Department;
use Common\Common\Cache;

class DepartmentController extends AbstractController {

	// 仅本部门
	const PERMISSION_SELF = 1;
	// 全公司
	const PERMISSION_ALL = 0;

	// 有附加权限
	const EXTRAPERM_YES = 1;
	// 无附加权限
	const EXTRAPERM_NO = 0;

	/**
	 * 部门列表接口
	 */
	public function List_get() {

		$params = I('get.');
		$cd_upid = $params['cd_id'];
		// 获取顶级部门id
		if (empty($cd_upid)) {
			Department::instance()->get_top_cdid($cd_upid);
		}

		$search = $params['cd_name'];
		// 如果部门id为空, 则返回空值
		if ($cd_upid < 1) {
			$this->_result = array();
			return true;
		}

		// 获取用户当前所在部门
		$serv_mem_dep = D('Common/MemberDepartment', 'Service');
		$mdp_list = $serv_mem_dep->list_by_conds(array(
			'm_uid' => $this->_login->user['m_uid']
		));

		// 当前用户所在部门
		$my_dep_list = array();
		// 所有有权限浏览的部门id
		$all_cdids = array();
		// 有全公司权限标识
		$all_permission = '';
		// 上级部门
		$my_up_cdids = array();
		// 所属部门的下级部门
		$my_dep_children_dep = array();
		// 遍历部门列表
		foreach ($mdp_list as $_mdp) {
			$all_cdids[$_mdp['cd_id']] = $_mdp['cd_id'];
			Department::instance()->list_parent_cdids($_mdp['cd_id'], $my_up_cdids);
			// 获取所属部门的下级部门
			$temp = Department::instance()->list_childrens_by_cdid($_mdp['cd_id']);
			$my_dep_children_dep = array_merge($temp, $my_dep_children_dep);
			// 判断部门权限是否有全公司
			if($this->_departments[$_mdp['cd_id']]['cd_permission'] == 0) {
				$all_permission = 'all';
			}
			$my_dep_list[] = array(
				'cd_id' => $_mdp['cd_id'],
				'cd_name' => $this->_departments[$_mdp['cd_id']]['cd_name'],
				'cd_usernum' => $this->_departments[$_mdp['cd_id']]['cd_usernum']
			);
		}
		unset($temp);

		// 查询权限部门
		$conds_per = array('cd_id' => $all_cdids);
		$serv_per = D('Common/CommonDepartmentPermission', 'Service');
		$perm_list = $serv_per->list_by_conds($conds_per);
		foreach ($perm_list as $_addrdep) {
			$all_cdids[$_addrdep['per_id']] = $_addrdep['per_id'];
			Department::instance()->list_parent_cdids($_addrdep['per_id'], $all_cdids);
			// 获取权限部门的下级部门
			$child_cdids = Department::instance()->list_childrens_by_cdid($_addrdep['per_id']);
			$all_cdids = array_merge($child_cdids, $all_cdids);
		}

		// 搜索操作
		if (!empty($search)) {
			// 获取标签列表
			$departments = $this->_search_departments($search, $all_cdids);
			return true;
		}

		// 遍历所有部门，获取up_id下的所有部门
		$departments = array();
		$main_cdids = array();
		// 获取所在部门的上级部门ID
		$all_cdids = array_merge($all_cdids, $my_up_cdids);

		// 上下级关系
		if ('all' == $all_permission) {
			$p2c = Cache::instance()->get('Common.department_p2c');
		} else {
			$p2c = array();
			foreach ($all_cdids as $_cdid) {
				if (empty($p2c[$this->_departments[$_cdid]['cd_upid']])) {
					$p2c[$this->_departments[$_cdid]['cd_upid']] = array();
				}

				$p2c[$this->_departments[$_cdid]['cd_upid']][] = $_cdid;
			}
		}

		// 获取所在部门的下级部门
		$all_cdids = array_unique(array_merge($all_cdids, $my_dep_children_dep));
		foreach ($this->_departments as $_dep) {
			if ($_dep['cd_upid'] == $cd_upid && (in_array($_dep['cd_id'], $all_cdids) || $all_permission == 'all')) {
				$departments[] = $this->_format_department($_dep, !empty($p2c[$_dep['cd_id']]));
			}
			// 顶级部门
			if (0 == $_dep['cd_upid']) {
				$main_cdids = $this->_format_department($_dep);
			}
		}

		// 返回值
		$this->_result = array(
			'departments' => $departments,
			'main_cdids' => $main_cdids,
			'my_department' => $my_dep_list,
		);

		return true;
	}

	/**
	 * 格式化输出内容
	 * @param array $dep 部门信息;
	 * @return boolean
	 */
	protected function _format_department($dep, $haschild = false) {

		return array(
			'cd_id' => $dep['cd_id'],
			'cd_name' => $dep['cd_name'],
			'cd_permission' => $dep['cd_permission'],
			'cd_displayorder' => $dep['cd_displayorder'],
			'cd_lastordertime' => empty($dep['cd_lastordertime']) ? 0 : $dep['cd_lastordertime'],
			'cd_usernum' => $dep['cd_usernum'],
			'cd_haschild' => $haschild
		);
	}

	/**
	 * 搜索的方法
	 * @param $search string 搜索的内容
	 * @return bool 接口返回数据
	 */
	protected function _search_departments($search, $cdids) {

		$list = array();
		if (empty($search)) {
			return $list;
		}

		foreach ($this->_departments as $_dep) {
			// 过滤掉总部门
			if ($_dep['cd_upid'] == 0) {
				$main_cdids = $this->_format_department($_dep);
				continue;
			}
			if (in_array($_dep['cd_id'], $cdids) && preg_match('/' . preg_quote($search) . '/i', $_dep['cd_name'])) {
				$list[] = $this->_format_department($_dep);
			}
		}

		$this->_result = array(
			'departments' => $list,
			'main_cdids' => $main_cdids,
		);

		return true;
	}

}
