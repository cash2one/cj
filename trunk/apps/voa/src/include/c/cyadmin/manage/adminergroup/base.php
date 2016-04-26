<?php
/**
 * voa_c_cyadmin_manage_adminergroup_base
 * 主站后台/后台管理/管理组/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_manage_adminergroup_base extends voa_c_cyadmin_manage_base {

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 删除指定cag_id的管理组
	 * @param number $cag_id
	 */
	protected function _adminergroup_delete($cag_id = 0) {
		$adminergroup = $this->_adminergroup_get($cag_id, false);
		if (!empty($adminergroup) && $adminergroup['cag_enable'] != voa_d_cyadmin_common_adminergroup::ENABLE_SYS) {
			// 存在此管理组且其不是默认管理组
			return $this->_serv_adminergroup->delete($cag_id);
		}
	}

	/**
	 * 返回指定cag_id的管理组信息
	 * @param number $cag_id
	 * @param string $return_default 如果没有管理组信息是否返回默认数据
	 * @return boolean|array
	 */
	protected function _adminergroup_get($cag_id = 0, $return_default = false) {

		// 找到指定 cag_id 的管理组信息
		$adminergroup = $this->_serv_adminergroup->fetch($cag_id);
		if (empty($adminergroup)) {
			// 如果未找到，且未设置返回默认值
			if ($return_default) {
				// 允许返回默认值
				$adminergroup = $this->_serv_adminergroup->fetch_all_field();
			} else {
				return false;
			}
		}

		return $adminergroup;
	}

	/**
	 * 添加、更改管理组信息
	 * @param array $adminergroup 原来数据、旧数据
	 * @param array $submit 新提交的数据
	 * @param string $result_msg <strong style="color:red">(引用)</strong>操作结果提示
	 * @return boolean | array
	 */
	protected function _adminergroup_update($adminergroup = array(), $submit = array(), &$result_msg = '') {

		// 发生更改的数据
		$updated = array();

		// 检查哪些数据改变了
		if ($adminergroup['cag_id']) {
			foreach ($adminergroup as $key => $value) {
				if (isset($submit[$key]) && $submit[$key] != $value) {
					$updated[$key] = $submit[$key];
				}
			}
		} else {
			$updated = $submit;
		}

		if (isset($updated['cag_enable'])) {

			if ($updated['cag_enable'] == voa_d_cyadmin_common_adminergroup::ENABLE_SYS) {
				// 如果设置为系统组，则不需要更改菜单
				unset($updated['cag_role']);
			}

			if ($adminergroup['cag_enable'] == voa_d_cyadmin_common_adminergroup::ENABLE_SYS || $updated['cag_enable'] == voa_d_cyadmin_common_adminergroup::ENABLE_SYS) {
				// 任何时候都禁止设置为系统最高权限组
				unset($updated['cag_enable']);
			}

		}

		if ($adminergroup['cag_id'] && $adminergroup['cag_enable'] == voa_d_cyadmin_common_adminergroup::ENABLE_SYS) {
			// 系统默认管理组，禁止更改菜单权限
			unset($updated['cag_role']);
		}

		if (empty($updated)) {
			$result_msg = '没有发生改变的数据，无须提交';
			return false;
		}

		if (empty($adminergroup['cag_id'])) {
			// 新增
			if (!isset($updated['cag_title'])) {
				$result_msg = '管理组名称必须填写';
				return false;
			}
		}

		// 检查管理组名称
		if (isset($updated['cag_title'])) {
			$updated['cag_title'] = (string)$updated['cag_title'];
			if (!validator::is_len_in_range($updated['cag_title'], 1, 32)) {
				$result_msg = '管理组名称长度应该介于 1到32 字节之间';
				return false;
			}
			if ($updated['cag_title'] != rhtmlspecialchars($updated['cag_title'])) {
				$result_msg = '管理组名称不能包含特殊字符';
				return false;
			}
			if ($this->_serv_adminergroup->count_by_title_notid($updated['cag_title'], $adminergroup['cag_id']) > 0) {
				$result_msg = '管理组名称“'.$updated['cag_title'].'”已被使用';
				return false;
			}
			$update['cag_title'] = $updated['cag_title'];
		}

		// 检查启用状态
		if (isset($updated['cag_enable'])) {
			$updated['cag_enable'] = (string)$updated['cag_enable'];
			if (!isset($this->_adminergroup_enable_map[$updated['cag_enable']])) {
				$updated['cag_enable'] = voa_d_cyadmin_common_adminergroup::ENABLE_NO;
			}
			$update['cag_enable'] = $updated['cag_enable'];
		}

		// 管理组描述
		if (isset($updated['cag_description'])) {
			$updated['cag_description'] = (string)$updated['cag_description'];
			if (!validator::is_len_in_range($updated['cag_description'], -1, 100)) {
				$result_msg = '管理组描述文字不能超过 100字节';
				return false;
			}
			$update['cag_description'] = $updated['cag_description'];
		}

		// 检查角色菜单权限
		if (isset($updated['cag_role'])) {

			if ($updated['cag_role'] && is_array($updated['cag_role'])) {

				// 遍历提交过来的所选的菜单，并解析出对应的菜单id
				$menuids = array();
				// 提交的不带默认菜单的项目
				$_remove = array();
				foreach ($this->_default_list['subop'] as $_m => $_os) {
					foreach ($_os as $_o => $_s) {
						$_ss = $_s['subop'];
						if (!in_array("{$_m}_{$_o}_{$_ss}", $updated['cag_role'])) {
							$_remove[$_m][$_o] = 1;
						}
					}
				}

				foreach ($updated['cag_role'] as $rolegroup) {
					if (!is_string($rolegroup)) {
						continue;
					}
					list($m,$o,$s) = explode('_', $rolegroup);

					// 不带默认菜单的菜单则跳过
				/* 	if (isset($_remove[$m][$o])) {
						continue;
					} */

					// 判断菜单子业务是否存在
					if (isset($this->_subop_list[$m][$o][$s])) {
						$_subop = $this->_subop_list[$m][$o][$s];
					} else {
						//不存在的子业务，则跳出
						continue;
					}

					// 导入权限组的子业务id
					if (!isset($menuids[$_subop['id']])) {
						$menuids[$_subop['id']] = $_subop['id'];
					}

					// 导入权限组的主业务id
					if (!isset($menuids[$this->_operation_list[$m][$o]['id']])) {
						$menuids[$this->_operation_list[$m][$o]['id']] = $this->_operation_list[$m][$o]['id'];
					}

					// 导入权限组的菜单模块id
					if (!isset($menuids[$this->_module_list[$m]['id']])) {
						$menuids[$this->_module_list[$m]['id']] = $this->_module_list[$m]['id'];
					}

				}

				// 对所有菜单id，按小到大顺序排序
				sort($menuids, SORT_NUMERIC);
				$updated['cag_role'] = implode(',', $menuids);
				if ($updated['cag_role'] != $adminergroup['cag_role']) {
					$update['cag_role'] = $updated['cag_role'];
				}

			} else {
				$updated['cag_role'] = '';
				$update['cag_role'] = $updated['cag_role'];
			}

		}

		if (empty($update)) {
			$result_msg = '数据未改动无须提交';
			return false;
		}

		if ($adminergroup['cag_id']) {
			// 更新
			$this->_serv_adminergroup->update($update, $adminergroup['cag_id']);
		} else {
			// 添加
			$this->_serv_adminergroup->insert($update);
		}

		return $update;
	}


}
