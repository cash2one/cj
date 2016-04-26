<?php
/**
 * voa_c_admincp_system_adminergroup_base
 * 企业后台/系统设置/后台管理组/基本控制
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_adminergroup_base extends voa_c_admincp_system_base {

	/**
	 * (system/adminergroup/base) 模块初始化启动
	 * @param mixed $action
	 * @access protected
	 * @return void
	 */
	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		/** 管理组状态数组推送到模板内 */
		$this->view->set('groupEnableStatus', $this->adminergroup_enables_desription);

		/** 系统组的状态标记 */
		$this->view->set('systemgroup', $this->adminergroup_enables['sys']);

		/** 系统管理员（创建人）标记 */
		$this->view->set('systemadminer', $this->adminer_locked['sys']);

		return true;

	}

	/**
	 * (system/adminergroup/base) 获取用户组详情，不存在则返回用户组字段默认数据
	 * @param number $cag_id
	 * @return array
	 */
	protected function _group_detail($cag_id = 0){
		if ($cag_id) {
			$group	=	$this->_get_usergroup($cag_id);
			return $group ? $group : self::_group_detail(0);
		} else {
			return $this->_service_single('common_adminergroup', 'fetch_all_field', array());
		}
	}

	/**
	 * (system/adminergroup/base) 删除管理组
	 * @param number $cag_id
	 */
	protected function _adminergroup_delete($cag_id){
		$this->_service_single('common_adminergroup', 'delete', $cag_id);
	}

	/**
	 * 根据管理组ID 统计该组下 有多少管理员
	 * @param $cag_id
	 * @return object
	 * @throws controller_exception
	 */
	protected function _count_adminer_by_cag_id($cag_id) {
		return $this->_service_single('common_adminer', 'count_by_cag_id', $cag_id);
	}

	/**
	 * (system/adminergroup/base) 提交处理添加或编辑管理组动作
	 * @param number $cag_id
	 * @param boolean $returnMessage 操作成功后是否返回提示信息
	 */
	protected function _response_submit_edit($cag_id = 0, $returnMessage = true){

		/** 当前管理的组的详情 */
		$groupDetail	=	$this->_group_detail($cag_id);
		/** 如果是新增，判断管理组数量是否超过限制 */
		if ((!$cag_id || !$groupDetail['cag_id']) && $this->_service_single('common_adminergroup', 'count_all', array()) >= $this->adminergroup_maxcount) {
			$this->message('error', '系统限制只允许添加最多 '.$this->adminergroup_maxcount.' 个管理组，请返回');
		}

		/** 管理组标题 */
		$param['cag_title']			=	$this->request->post('cag_title');
		/** 启用状态 */
		$param['cag_enable']		=	$this->request->post('cag_enable');
		/** 描述 */
		$param['cag_description']	=	$this->request->post('cag_description');
		/** 管理权限 */
		$param['cag_role']			=	$this->request->post('cag_role');

		/** 被改变了的字段数据 */
		$newParam	=	array();

		/** 检查管理组名 */
		if (!isset($param['cag_title']) || !is_scalar($param['cag_title'])) {
			$this->message('error', '请填写管理组名称');
		}
		$length	=	strlen($param['cag_title']);
		if ($length < 1) {
			$this->message('error', '管理组名称不能为空');
		}
		if ($length > 32) {
			$this->message('error', '管理组名称长度应小于32个字节');
		}
		if ($param['cag_title'] != rhtmlspecialchars($param['cag_title'])) {
			$this->message('error', '管理组名称不允许包含特殊字符');
		}
		if ($this->_service_single('common_adminergroup', 'count_by_title_notid', $param['cag_title'], $cag_id) > 0) {
			$this->message('error', '管理组名称  “'.rhtmlspecialchars($param['cag_title']).'” 已经被使用过，请更换一个');
		}
		if ($param['cag_title'] != $groupDetail['cag_title']) {
			$newParam['cag_title']	=	$param['cag_title'];
		}

		/** 检查管理组启用状态 */
		if (isset($param['cag_enable'])) {
			if ($param['cag_enable'] == $this->adminergroup_enables['sys']) {
				$this->message('error', '禁止更改系统管理组状态');
			}
			if (!isset($this->adminergroup_enables_desription[$param['cag_enable']])) {
				$this->message('error', '请正确选择管理组状态');
			}
			if ($param['cag_enable'] != $groupDetail['cag_enable']) {
				$newParam['cag_enable']	=	$param['cag_enable'];
			}
		}

		/** 检查管理组描述 */
		if (isset($param['cag_description'])) {
			if (strlen($param['cag_description']) > 100) {
				$this->message('error', '管理组描述文字长度应该小于100字节');
			}
			if ($param['cag_description'] != $groupDetail['cag_description']) {
				$newParam['cag_description']	=	$param['cag_description'];
			}
		}

		/** 检查权限设定 */
		if (isset($param['cag_role']) && is_array($param['cag_role'])) {

			if (isset($param['cag_enable']) && $param['cag_enable'] == $this->adminergroup_enables['sys']) {
				$this->message('error', '禁止修改系统组权限');
			}
			if ($groupDetail && $groupDetail['cag_id'] && $groupDetail['cag_enable'] == $this->adminergroup_enables['sys']) {
				$this->message('error', '不能修改系统组权限');
			}

			/** 遍历提交过来的所选的菜单，并解析出对应的菜单id */
			$menuids	=	array();
			/** 提交的不带默认菜单的项目 */
			$_remove = array();
			foreach ($this->_default_list['subop'] AS $_m => $_os) {
				foreach ($_os AS $_o => $_s) {
					if (!in_array("{$_m}_{$_o}_{$_s['subop']}", $param['cag_role'])) {
						$_remove[$_m][$_o] = 1;
					}
				}
			}

			$subop_list = array();
			foreach ($this->_subop_list as $m => $os) {
				foreach ($os as $o => $ss) {
					foreach ($ss as $ssarray) {
						foreach ($ssarray as $sssarray) {
							if (!isset($subop_list[$m][$o][$sssarray['subop']])) {
								$subop_list[$m][$o][$sssarray['subop']] = $sssarray;
							}
						}
					}
				}
			}

			// 遍历提交的数据，取得有效的菜单
			foreach ($param['cag_role'] AS $rolegroup) {
				if (!is_string($rolegroup)) {
					continue;
				}
				list($m, $o, $s)	=	explode('_', $rolegroup);

				/** 不带默认菜单的菜单则跳过 */
				if (isset($_remove[$m][$o])) {
					continue;
				}

				/** 判断菜单子业务是否存在 */
				if (isset($subop_list[$m][$o][$s])) {
					$_subop	=	$subop_list[$m][$o][$s];
				} else {
					//不存在的子业务，则跳出
					continue;
				}

				/** 导入权限组的子业务id */
				if (!isset($menuids[$_subop['id']])) {
					$menuids[$_subop['id']]	=	$_subop['id'];
				}

				/** 导入权限组的主业务id */
				if (!isset($this->_operation_list[$m]) && !isset($menuids[$this->_operation_list[$m][$o][0]['id']])) {
					$menuids[$this->_operation_list[$m][$o][0]['id']]	=	$this->_operation_list[$m][$o][0]['id'];
				}

				/** 导入权限组的菜单模块id */
				if (!isset($menuids[$this->_module_list[$m]['id']])) {
					$menuids[$this->_module_list[$m]['id']]	=	$this->_module_list[$m]['id'];
				}
			}

			/** 对所有菜单id，按小到大顺序排序 */
			sort($menuids, SORT_NUMERIC);
			$param['cag_role']	=	implode(',', $menuids);
			if ($param['cag_role'] != $groupDetail['cag_role']) {
				$newParam['cag_role']	=	$param['cag_role'];
			}
		} elseif ($groupDetail && $groupDetail['cag_id'] && $groupDetail['cag_enable'] != $this->adminergroup_enables['sys']) {
			$newParam['cag_role']	=	'';
		}

		/** 未发生改变 */
		if (empty($newParam)) {
			$this->message('error', '未发生改动，无须提交');
		}

		if ($groupDetail && $groupDetail['cag_id'] && $groupDetail['cag_enable'] != $this->adminergroup_enables['sys'] && empty($newParam['cag_role'])) {
			$this->message('error', '对不起，必须至少设置一项权限');
		}

		/** 编辑 */
		if ($cag_id) {
			$this->_service_single('common_adminergroup', 'update', $newParam, $cag_id);
			$message	=	'编辑管理组操作完毕';

			/** 新增 */
		} else {
			$this->_service_single('common_adminergroup', 'insert', $newParam);
			$message	=	'添加新管理组操作完毕';
		}

		/** 直接返回操作提示信息 */
		if ($returnMessage === true) {
			$this->message('success', $message, $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id), false);
		}

		/** 只返回结果，后续另行操作 */

		return true;

	}

}
