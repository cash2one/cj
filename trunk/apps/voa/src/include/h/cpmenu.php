<?php
/**
 * cpmenu.php
 * 后台菜单读取类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_h_cpmenu {

	/**
	 * 系统固有菜单，所有人均有这些功能，这些菜单不会写在数据库中
	 * @see voa_h_cpmenu::_set_intrinsic_menu()
	 * @var array
	 */
	protected static $_intrinsic_menu = array();

	/**
	 * 系统固有菜单的路由路径，所有人均有这些功能，这些是为了方便将写在数据库的菜单永远显示
	 * @see voa_h_cpmenu::_set_intrinsic_menu_path()
	 * @var array
	 */
	protected static $_intrinsic_menu_path = array();

	/**
	 * 需要隐藏的菜单
	 * @see voa_h_cpmenu::_set_hide_menu()
	 * @var array
	 */
	protected static $_hide_menu = array();

	/** 系统全部有效菜单数据 */
	protected static $_cpmenu = array();

	/** 一级菜单 */
	protected static $__module_list = array();
	/** 二级菜单 */
	protected static $__operation_list = array();
	/** 三级菜单 */
	protected static $__subop_list = array();
	/** 各级菜单的默认项目 */
	protected static $__defaults = array();
	/** 当前的权限组有权限的菜单id数组 */
	protected static $__ids = array();

	/**
	 * 获取指定管理组的有效菜单
	 * @param number $cag_id
	 * @return array
	 */
	public static function adminer_group_cpmenu($cag_id = 0) {

		// 获取所有全部菜单数据
		self::_get_cpmenu();
		// 获取当前的权限组信息
		if (!self::_get_adminergroup($cag_id)) {
			return self::_set_output();// 无权则直接返回空值
		}

		// 设置需要隐藏的菜单
		self::_set_hide_menu();

		// 所有二级菜单
		$all_operation = array();

		/** 遍历所有菜单以取得有效的 */
		foreach (self::$_cpmenu as $menu) {

			// 赋值二级菜单
			if ('operation' == $menu['type'] && !isset($all_operation[$menu['module']][$menu['operation']][$menu['cp_pluginid']])) {
				$all_operation[$menu['module']][$menu['operation']][$menu['cp_pluginid']] = $menu;
			}

			// 菜单自身设置隐藏了的，忽略
			if (!$menu['display']) {
				continue;
			}

			// 无权的菜单
			if (!self::_have_power($menu)) {
				continue;
			}

			// 需要隐藏的菜单则跳过
			if (self::_is_hide($menu)) {
				continue;
			}

			// 移除后面逻辑不再需要的字段
			unset($menu['status']);

			/** 设置各项菜单数据 */
			if ('module' == $menu['type']) {// 一级菜单(顶部导航)
				self::$__module_list[$menu['module']] = $menu;
			} elseif ('operation' == $menu['type']) { // 二级菜单(左侧菜单)
				self::$__operation_list[$menu['module']][$menu['operation']][$menu['cp_pluginid']] = $menu;
			} elseif ('subop' == $menu['type']) { // 三级菜单(子导航菜单)
				self::$__subop_list[$menu['module']][$menu['operation']][$menu['cp_pluginid']][$menu['subop']] = $menu;
			}

			// 定义动作的默认菜单
			if ($menu['default']) {
				// 一级菜单的默认菜单
				if (!isset(self::$__defaults['module'])) {
					self::$__defaults['module'] = $menu['module'];
				}
				// 二级菜单的默认菜单
				if (!isset(self::$__defaults['operation'][$menu['module']])) {
					self::$__defaults['operation'][$menu['module']] = $menu['operation'];
				}
				// 三级菜单的默认菜单
				if (!isset(self::$__defaults['subop'][$menu['module']][$menu['operation']]) && $menu['type'] == 'subop') {
					self::$__defaults['subop'][$menu['module']][$menu['operation']] = $menu;
				}
			}
		}

		// 补充可能遗失的二级菜单
		foreach (self::$__subop_list as $_module => $_array) {
			foreach ($_array as $_operation => $_array2) {
				foreach ($_array2 as $_pluginid => $_array3) {
					if (!isset(self::$__operation_list[$_module][$_operation][$_pluginid])) {
						self::$__operation_list[$_module][$_operation][$_pluginid] = $all_operation[$_module][$_operation][$_pluginid];
					}
				}
			}
		}
		unset($all_operation, $_module, $_array, $_operation, $_array2, $_pluginid, $_array3);

		// 移除无下级的第一级菜单
		foreach (self::$__module_list AS $_module => $_array) {
			if (!isset(self::$__subop_list[$_module]) || 1 > count(self::$__subop_list[$_module])) {
				unset(self::$__module_list[$_module], self::$__operation_list[$_module]);
			}
		}
		unset($_module, $_array);

		return self::_set_output();
	}

	/**
	 * 设置输出返回
	 * @return array
	 */
	protected static function _set_output() {
		return array(self::$__defaults, self::$__module_list, self::$__operation_list, self::$__subop_list);
	}

	/**
	 * 检查给定的菜单项目是否有权限
	 * @param array $menu
	 * @return boolean
	 */
	protected static function _have_power($menu) {

		// 顶级菜单
		if ($menu['type'] == 'module') {
			return true;
		}

		// 系统固有菜单
		if (0 >= $menu['id']) {
			return true;
		}

		// 最高权限组
		if (self::$__ids === true) {
			return true;
		}

		// 菜单在权限组的有效范围内
		if (in_array($menu['id'], self::$__ids)) {
			return true;
		}

		// 菜单路由路名名在系统固有菜单路径内
		if (in_array("{$menu['module']}_{$menu['operation']}_{$menu['subop']}", self::$_intrinsic_menu_path)) {
			return true;
		}

		// 否则 则无权，忽略后面操作
		return false;
	}

	/**
	 * 判断给定的菜单项目是否需要隐藏
	 * @param array $menu
	 * @return boolean
	 */
	protected static function _is_hide($menu) {

		if (!empty(self::$_hide_menu) && isset(self::$_hide_menu[$menu['module']])) {

			// 整体隐藏
			if (self::$_hide_menu[$menu['module']] === true) {
				return true;
			}

			if (isset(self::$_hide_menu[$menu['module']][$menu['operation']])) {

				// 隐藏二级下的所有
				if (self::$_hide_menu[$menu['module']][$menu['operation']] === true) {
					return true;
				}

				// 隐藏具体的某个菜单
				if (is_array(self::$_hide_menu[$menu['module']][$menu['operation']])) {
					if (in_array($menu['subop'], self::$_hide_menu[$menu['module']][$menu['operation']])) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * 获取后台全部有效菜单数据
	 * @return array
	 */
	protected static function _get_cpmenu() {

		// 设置系统固有菜单
		self::_set_intrinsic_menu();
		// 设置系统固有菜单的路由路径
		self::_set_intrinsic_menu_path();

		// 全部菜单数据
		$data = array();

		// 系统固有菜单
		foreach (self::$_intrinsic_menu as $key => $menu) {
			$data[$key] = $menu;
		}

		// 数据库内的动态菜单
		$serv = &service::factory('voa_s_oa_common_cpmenu', array('pluginid' => 0));
		foreach ($serv->fetch_all() AS $pk => $v) {
			unset($v['ccm_created'], $v['ccm_updated'], $v['ccm_deleted']);
			$data[$pk] = self::_trim_field($v, 'ccm_');
		}
		self::$_cpmenu = $data;

		unset($data);

		return self::$_cpmenu;
	}

	/**
	 * 获取指定管理组信息，并提取具有权限的菜单id
	 * @param number $cag_id
	 * @return boolean
	 */
	protected static function _get_adminergroup($cag_id = 0) {

		// 取得用户组信息
		$serv = &service::factory('voa_s_oa_common_adminergroup', array('pluginid' => 0));
		$adminergroup = $serv->fetch($cag_id);
		if (empty($adminergroup)) {
			return false;
		}

		// 用户有权限的菜单
		switch ($adminergroup['cag_enable']) {
			case voa_d_oa_common_adminergroup::ENABLE_SYS :// 最高系统权限组，拥有全部功能
				self::$__ids = true;
				break;
			case voa_d_oa_common_adminergroup::ENABLE_YES :// 普通权限组，只启用设定了的权限
				self::$__ids = explode(',', $adminergroup['cag_role']);
				break;
			default :// 未知的启用状态，权限id为空
				self::$__ids = array();
		}
		unset($adminergroup);

		return true;
	}

	/**
	 * 系统固有菜单的路由，避免用户由于设置管理组而不勾选导致无法使用核心功能
	 * @return array
	 */
	protected static function _set_intrinsic_menu_path() {

		$intrinsic_menu_path = array();
		foreach (self::$_intrinsic_menu as $m) {
			$intrinsic_menu_path[] = $m['module'].'_'.$m['operation'].'_'.$m['subop'];
		}

		return $intrinsic_menu_path;
	}

	/**
	 * 设置需要隐藏的菜单
	 * @return void
	 */
	protected static function _set_hide_menu() {

		self::$_hide_menu = array();

		// 禁止 人员管理/职务管理
		self::$_hide_menu['manage']['job'] = true;
		// 禁止 人员管理/部门管理
		self::$_hide_menu['manage']['department'] = true;
		// 禁止 人员管理/人员管理 下的 添加、删除、编辑、导入 等功能
		self::$_hide_menu['manage']['member'] = array('add', 'delete', 'edit', 'import');

	}

	/**
	 * 系统固有的菜单，所有人均有此权限
	 * @return void
	 */
	protected static function _set_intrinsic_menu() {
		$menu = array();
		$menu[-1] = array(
			'id' => -1,
			'cp_pluginid' => 0,
			'system' => 1,
			'module' => 'system',
			'operation' => 'status',
			'subop' => '',
			'type' => 'operation',
			'default' => 0,
			'name' => '账号信息',
			'icon' => '',
			'display' => 1,
			'displayorder' => 9999,
			'subdisplay' => 0,
			//'status' => 1,
		);
		$menu['-2'] = array(
			'id' => -2,
			'cp_pluginid' => 0,
			'system' => 1,
			'module' => 'system',
			'operation' => 'status',
			'subop' => 'account',
			'type' => 'subop',
			'default' => 1,
			'name' => '账号信息',
			'icon' => '',
			'display' => 1,
			'displayorder' => 9999,
			'subdisplay' => 1,
			//'status' => 1,
		);
		$menu[-3] = array(
			'id' => -3,
			'cp_pluginid' => 0,
			'system' => 1,
			'module' => 'system',
			'operation' => 'profile',
			'subop' => '',
			'type' => 'operation',
			'default' => 0,
			'name' => '修改密码',
			'icon' => '',
			'display' => 1,
			'displayorder' => 9999,
			'subdisplay' => 1,
			//'status' => 1,
		);
		$menu[-4] = array(
			'id' => -4,
			'cp_pluginid' => 0,
			'system' => 1,
			'module' => 'system',
			'operation' => 'profile',
			'subop' => 'pwd',
			'type' => 'subop',
			'default' => 1,
			'name' => '修改密码',
			'icon' => 'fa-lock',
			'display' => 1,
			'displayorder' => 9999,
			'subdisplay' => 1,
			//'status' => 1,
		);


		/*$menu[-5] = array(
			'id' => -5,
			'cp_pluginid' => 0,
			'system' => 1,
			'module' => 'system',
			'operation' => 'message',
			'subop' => '',
			'type' => 'operation',
			'default' => 0,
			'name' => '消息中心',
			'icon' => '',
			'display' => 1,
			'displayorder' => 9999,
			'subdisplay' => 0,
			//'status' => 1,
		);
		$menu['-6'] = array(
			'id' => -6,
			'cp_pluginid' => 0,
			'system' => 1,
			'module' => 'system',
			'operation' => 'message',
			'subop' => 'list',
			'type' => 'subop',
			'default' => 1,
			'name' => '未读消息',
			'icon' => '',
			'display' => 1,
			'displayorder' => 9999,
			'subdisplay' => 1,
			'subnavdisplay' => 1
			//'status' => 1,
		);

		$menu['-7'] = array(
			'id' => -7,
			'cp_pluginid' => 0,
			'system' => 1,
			'module' => 'system',
			'operation' => 'message',
			'subop' => 'view',
			'type' => 'subop',
			'default' => 1,
			'name' => '消息详情',
			'icon' => '',
			'display' => 1,
			'displayorder' => 9999,
			'subdisplay' => 1,
			//'status' => 1,
		);*/

		self::$_intrinsic_menu = $menu;
	}

	/**
	 * 移除一个数组键名的前后缀字符
	 * @param array $data
	 * @param string $prefix
	 * @param string $suffix
	 * @return array
	 */
	protected static function _trim_field($data, $prefix = '', $suffix = '') {

		if (empty($prefix) && empty($suffix)) {
			return $data;
		}

		if (!is_array($data)) {
			return $data;
		}

		$ret = array();
		foreach ($data as $k => $v) {
			// 剔除前缀
			if (!empty($prefix)) {
				$k = preg_replace('/^'.addslashes($prefix).'/i', '', $k);
			}

			// 剔除后缀
			if (!empty($suffix)) {
				$k = preg_replace('/'.addslashes($prefix).'$/i', '', $k);
			}

			$ret[$k] = $v;
		}

		return $ret;
	}

}
