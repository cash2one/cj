<?php
/**
 * execute.php
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */
error_reporting(E_ALL);
class execute {

	/** 数据库操作对象 */
	protected $_db = null;
	/** 表前缀 */
	protected $_tablepre = 'oa_';
	/** 当前站点系统设置 */
	protected $_settings = array();
	/** 来自命令行请求的参数 */
	protected $_options = array();
	/** 来自触发此脚本的父级参数 */
	protected $_params = array();
	/** 储存已执行的SQL语句，文件路径 */
	protected $_sql_logfile = '';
	/** 储存已执行SQL语句的恢复语句，文件路径 */
	protected $_sql_restore_logfile = '';

	public function __construct() {
	}

	/**
	 * 初始化环境参数
	 * @param object $db 数据库操作对象
	 * @param string $tablepre 表前缀
	 * @param array $settings 当前站点的setting
	 * @param array $options 传输进来的外部参数
	 * @param array $params 一些环境参数，来自触发执行本脚本
	 * @see voa_backend_tool_upgrade::main()
	 */
	public function init($db, $tablepre, $settings, $options, $params) {
		$this->_db = $db;
		$this->_tablepre = $tablepre;
		$this->_settings = $settings;
		$this->_options = $options;
		$this->_params = $params;

		$this->_sql_logfile = $this->_params['cachedir'].DIRECTORY_SEPARATOR.$this->_options['version'].'_sql.txt';
		$this->_sql_restore_logfile = $this->_params['cachedir'].DIRECTORY_SEPARATOR.$this->_options['version'].'_sql_restore.txt';

		// 避免重复运行覆盖，随机一个附加的文件名
		$mt_rand = time().'.'.mt_rand(100, 999);
		// 如果已经执行语句文件存在则重命名
		if (is_file($this->_sql_logfile)) {
			rename($this->_sql_logfile, $this->_sql_logfile.'.'.$mt_rand);
		}
		// 重命名存在了的sql_restore.sql
		if (is_file($this->_sql_restore_logfile)) {
			rename($this->_sql_restore_logfile, $this->_sql_restore_logfile.'.'.$mt_rand);
		}
	}

	/**
	 * 脚本执行的主方法，不同的升级脚本具体操作动作不相同
	 * @return void
	 */
	public function run() {

		error_reporting(E_ALL);

		$steps = array(
			'cpmenu_table_upgrade',// 升级cpmenu表结构
			'cpmenu_data_update',// 更改cpmenu表数据
			'cpmenu_cache_clear', // 清理cpmenu菜单缓存
		);

		foreach ($steps as $_step) {
			$classname = '_'.$_step;
			$this->$classname();
		}

	}

	/**
	 * cpmenu表结构升级
	 */
	protected function _cpmenu_table_upgrade() {

		// 检查数据表是否已升级
		$query = $this->_db->query("SHOW COLUMNS FROM `{$this->_tablepre}common_cpmenu` like 'ccm_subnavdisplay'");
		if ($this->_db->num_rows($query)) {
			return true;
		}

		// 未升级，则进行升级
		$this->_db->query("ALTER TABLE `{$this->_tablepre}common_cpmenu`
			ADD `ccm_subnavdisplay` tinyint(1) unsigned NOT NULL DEFAULT '1'
			COMMENT '是否在子导航内显示，如：编辑、删除一类的可设为0，不显示' AFTER `ccm_displayorder`;");

		return true;
	}

	/**
	 * 更新cpmenu表数据
	 */
	protected function _cpmenu_data_update() {

		// 读取全部应用
		$app_list = array();
		$query = $this->_db->query("SELECT * FROM `{$this->_tablepre}common_plugin`");
		while ($row = $this->_db->fetch_array($query)) {
			$app_list[$row['cp_identifier']] = $row;
		}

		// 读取全部应用模块组
		$appgroups = array();
		$query = $this->_db->query("SELECT * FROM `{$this->_tablepre}common_module_group`");
		while ($row = $this->_db->fetch_array($query)) {
			$appgroups[$row['cmg_id']] = $row;
		}

		// 获取应用所在的模块名（后台第一级菜单名）
		$modules = array();
		foreach ($app_list as $app) {
			if (isset($appgroups[$app['cmg_id']])) {
				$modules[$app['cp_identifier']] = $appgroups[$app['cmg_id']]['cmg_dir'];
			}
		}

		// 列出所有菜单
		$cpmenus = array();
		$query = $this->_db->query("SELECT * FROM `{$this->_tablepre}common_cpmenu` WHERE `ccm_type`='subop' AND `ccm_status`<3");
		while ($row = $this->_db->fetch_array($query)) {
			$cpmenus[$row['ccm_module']][$row['ccm_operation']][$row['ccm_subop']][$row['ccm_id']] = $row;
		}

		// 列出二级菜单
		$operation_menus = array();
		$query = $this->_db->query("SELECT * FROM `{$this->_tablepre}common_cpmenu` WHERE `ccm_type`='operation'");
		while ($row = $this->_db->fetch_array($query)) {
			$operation_menus[$row['ccm_module']][$row['ccm_operation']][$row['ccm_id']] = $row;
		}

		// 配置目录
		$config_dir = APP_PATH.DIRECTORY_SEPARATOR;
		$config_dir .= 'src'.DIRECTORY_SEPARATOR;
		$config_dir .= 'config'.DIRECTORY_SEPARATOR;
		$config_dir .= 'application'.DIRECTORY_SEPARATOR;

		// 读取所有的应用配置文件
		$handle = opendir($config_dir);
		if (!$handle) {
			echo "Dir '".$config_dir."' not open.\r\n";
			return;
		}

		while (false !== ($file = readdir($handle))) {

			// 当前应用菜单配置文件
			$cfg_file = $config_dir.$file;

			// 不是标准的应用菜单配置文件，则忽略
			if ($file == '.' || $file == '..' || stripos($file, '_') === 0 || !is_file($cfg_file)) {
				continue;
			}

			// 初始化变量
			$conf = array();
			// 载入当前应用菜单配置文件
			include $cfg_file;
			// 获取应用的唯一标识名
			list($identifier) = explode('.', $file);

			// 无法获取应用所在的模块组，则忽略
			if (!isset($modules[$identifier])) {
				continue;
			}

			// 菜单的主目录名
			$_module = $modules[$identifier];
			// 菜单二级目录名
			$_operation = $identifier;

			// 移动存在于原来应用中心的“设置”菜单项 目录
			if (!empty($operation_menus['setting'])) {
				foreach ($operation_menus['setting'] as $__operation => $_menu1) {

					// 不是当前应用则忽略
					if ($__operation != $_operation) {
						continue;
					}

					foreach ($_menu1 as $__ccm_id => $__menu) {
						// 执行删除，由于数据自身已无用，所以不需要恢复
						$sql = array();
						$sql[] = "DELETE FROM `{$this->_tablepre}common_cpmenu`";
						$sql[] = "WHERE `ccm_id`='{$__ccm_id}' LIMIT 1";
						$this->_rquery($sql, '');
					}
				}
			}

			// 移动存在于原来应用中心的“设置”菜单项
			// module=setting
			// operation=$_operation
			// ccm_subop=modify ccm_type=subop OR ccm_subop='' ccm_type=operation
			foreach ($cpmenus as $__module => $_menu1) {

				// 不是原“应用管理”下的目录，则忽略
				if ($__module != 'setting') {
					continue;
				}

				foreach ($_menu1 as $__operation => $_menu2) {

					// 不是当前应用则忽略
					if ($__operation != $_operation) {
						continue;
					}

					// 遍历所有菜单
					foreach ($_menu2 as $__subop => $_menu3) {
						foreach ($_menu3 as $__ccm_id => $_menu_data) {
							// 执行删除
							$sql = array();
							$sql[] = "DELETE FROM `{$this->_tablepre}common_cpmenu`";
							$sql[] = "WHERE `ccm_id`='{$__ccm_id}' LIMIT 1";
							// 数据恢复
							//TODO 由于数据都是可信的，因此语句中并未进行数据过滤，下同
							$sql_restore = array();
							// 由于数据本身已无用，所以不需要恢复
							//$sql_restore[] = "REPLACE INTO `{$this->_tablepre}common_cpmenu`";
							//$sql_restore[] = "(`".implode('`, `', array_keys($_menu_data))."`)";
							//$sql_restore[] = "VALUES";
							//$sql_restore[] = "('".implode("', '", array_values($_menu_data))."')";
							// 执行
							$this->_rquery($sql, $sql_restore);
						}
					}
				}
			}
			unset($__module, $__operation, $__subop, $__ccm_id, $sql, $sql_restore, $_menu1, $_menu2, $_menu3);
			// end 结束处理删除原应用设置菜单

			// 后台菜单未定义
			if (empty($conf['menu.admincp']) || !is_array($conf['menu.admincp'])) {
				continue;
			}

			// 不存在此应用（用户未开启过或者已删除），则忽略
			if (empty($cpmenus[$_module][$_operation])) {
				continue;
			}

			// 该应用的所有菜单
			$app_menus = $cpmenus[$_module][$_operation];

			// 查找新配置里已经移除不要的菜单项，然后删除掉
			foreach ($app_menus as $__subop => $__menu) {

				// 新配置里菜单存在，则忽略
				if (isset($conf['menu.admincp'][$__subop]) || !$__subop) {
					continue;
				}

				// 不存在，删除
				$sql = array();
				$sql[] = "DELETE FROM `{$this->_tablepre}common_cpmenu` WHERE `ccm_id`='{$__menu['ccm_id']}'";
				// 恢复
				$sql_restore = array();
				$sql_restore[] = "REPLACE INTO `{$this->_tablepre}common_cpmenu`";
				$sql_restore[] = "(`".implode('`, `', array_keys($__menu))."`)";
				$sql_restore[] = "VALUES";
				$sql_restore[] = "('".implode("', '", array_values($__menu))."')";
				// 执行
				$this->_rquery($sql, $sql_restore);
			}
			unset($__subop, $__menu, $sql, $sql_restore);
			// end 处理无用的菜单操作完毕

			// 更新旧的菜单数据
			foreach ($conf['menu.admincp'] as $_subop => $_menu_setting) {

				if (isset($cpmenus[$_module][$_operation][$_subop])) {

					// 遍历已存在了的，但只保留第一个，其他的删除，这个过程是为了确保之前没有重复数据
					$__haved = false;
					foreach ($cpmenus[$_module][$_operation][$_subop] as $__ccm_id => $__menu) {

						if (!$__haved) {
							// 更新第一条

							// 标记第一条
							$__haved = true;

							// 与之前显示状态一致
							$_menu_setting['display'] = $__menu['ccm_display'];
							$update_data = $restore_data = array();
							foreach ($_menu_setting as $__key => $__value) {
								// 与原数据一致则忽略
								if ($__value == $__menu['ccm_'.$__key]) {
									continue;
								}
								// 标记更新
								$update_data[] = "`ccm_{$__key}`='{$__value}'";
								$restore_data[] = "`ccm_{$__key}`='".$__menu['ccm_'.$__key]."'";
							}
							unset($__key, $__value);

							// 无变化，不需要更新
							if (empty($update_data)) {
								continue;
							}

							// 更新
							$sql = array();
							$sql[] = "UPDATE `{$this->_tablepre}common_cpmenu`";
							$sql[] = "SET ".implode(', ', $update_data);
							$sql[] = "WHERE `ccm_id`='{$__ccm_id}'";
							// 恢复
							$sql_restore = array();
							$sql_restore[] = "UPDATE `{$this->_tablepre}common_cpmenu`";
							$sql_restore[] = "SET ".implode(', ', $restore_data);
							$sql_restore[] = "WHERE `ccm_id`='{$__ccm_id}'";
							// 执行
							$this->_rquery($sql, $sql_restore);
						} else {
							// 删除其他条
							$sql = array();
							$sql[] = "DELETE FROM `{$this->_tablepre}common_cpmenu`";
							$sql[] = "WHERE `ccm_id`='{$__ccm_id}' LIMIT 1";
							// 恢复
							$sql_restore = array();
							$sql_restore[] = "REPLACE INTO `{$this->_tablepre}common_cpmenu`";
							$sql_restore[] = "(`".implode("`, `", array_keys($__menu))."`)";
							$sql_restore[] = "VALUES";
							$sql_restore[] = "('".implode("', '", array_values($__menu))."')";
							// 执行
							$this->_rquery($sql, $sql_restore);
						}
					}

				} else {

					// 不存在的，则添加
					$sql = array();
					$sql[] = "INSERT INTO `{$this->_tablepre}common_cpmenu`";
					$__data = array();
					$__data_restore = array();
					$_menu_setting['module'] = $_module;
					$_menu_setting['operation'] = $_operation;
					$_menu_setting['subop'] = $_subop;
					$_menu_setting['type'] = 'subop';
					foreach ($_menu_setting as $___key => $___value) {
						$__data['ccm_'.$___key] = $___value;
						$__data_restore[] = "`ccm_{$___key}`='{$___value}'";
					}
					$__data['cp_pluginid'] = $app_list[$identifier]['cp_pluginid'];
					$__data_restore[] = "`cp_pluginid`='{$app_list[$identifier]['cp_pluginid']}'";
					$sql[] = "(`".implode("`, `", array_keys($__data))."`)";
					$sql[] = "VALUES";
					$sql[] = "('".implode("', '", array_values($__data))."')";
					// 恢复
					$sql_restore = array();
					$sql_restore[] = "DELETE FROM `{$this->_tablepre}common_cpmenu`";
					$sql_restore[] = "WHERE ".implode(' AND ', $__data_restore);
					// 执行
					$this->_rquery($sql, $sql_restore);
				}
			}
			unset($data, $k, $v);
		}
		closedir($handle);
		unset($cpmenus, $app_list, $app_menus, $appgroups);

		// 更新旧的系统菜单，标记其不在子菜单显示
		$sys_menus = array(
			array('module' => 'manage', 'operation' => 'department', 'subop' => 'delete'),
			array('module' => 'manage', 'operation' => 'department', 'subop' => 'edit'),
			array('module' => 'manage', 'operation' => 'job', 'subop' => 'modify'),
			array('module' => 'manage', 'operation' => 'member', 'subop' => 'delete'),
			array('module' => 'manage', 'operation' => 'member', 'subop' => 'edit'),
			array('module' => 'setting', 'operation' => 'application', 'subop' => 'delete'),
			array('module' => 'setting', 'operation' => 'application', 'subop' => 'edit'),
			array('module' => 'setting', 'operation' => 'servicetype', 'subop' => 'modify'),
			array('module' => 'system', 'operation' => 'adminer', 'subop' => 'delete'),
			array('module' => 'system', 'operation' => 'adminer', 'subop' => 'edit'),
			array('module' => 'system', 'operation' => 'adminergroup', 'subop' => 'delete'),
			array('module' => 'system', 'operation' => 'adminergroup', 'subop' => 'edit'),
			array('module' => 'system', 'operation' => 'setting', 'subop' => 'modify'),
		);
		foreach ($sys_menus as $m) {
			$sql = array();
			$sql[] = "UPDATE `{$this->_tablepre}common_cpmenu` SET `ccm_subnavdisplay`=0";
			$sql[] = "WHERE `ccm_module`='{$m['module']}'";
			$sql[] = "AND `ccm_operation`='{$m['operation']}'";
			$sql[] = "AND `ccm_subop`='{$m['subop']}'";
			$sql[] = "AND `ccm_type`='subop'";
			// 由于这些字段并不影响整体，因此不需要进行恢复操作
			$this->_rquery($sql, '');
		}



		// setting/common/modify/ => system/setting/modify/
		// 旧的应用管理下的“环境设置”移动到系统设置下
		// 由于数据不影响整理，因此不需要进行恢复操作
		$sql = array();
		$sql[] = "UPDATE `{$this->_tablepre}common_cpmenu`";
		$sql[] = "SET `ccm_module`='system', `ccm_operation`='setting'";
		$sql[] = "WHERE `ccm_module`='setting'";
		$sql[] = "AND `ccm_operation`='common'";
		$this->_rquery($sql, '');

		// 升级内置菜单
		$this->_update_old_icon_data();

	}

	/**
	 * 清理cpmenu缓存
	 */
	protected function _cpmenu_cache_clear() {

		// 当前站点的缓存目录
		$cachedir = $this->_params['cachedir'];

		// 读取缓存目录下的文件
		$handle = opendir($cachedir);
		if ($handle) {
			while (false !== ($file = readdir($handle))) {

				// 判断是否是有效的菜单缓存文件
				if ($file == 'cpmenu.php' || preg_match('/^adminergroupcpmenu\.\d+/', $file)) {
					// 删除
					unlink($cachedir.DIRECTORY_SEPARATOR.$file);
				}
			}
		}

	}

	/**
	 * 带储存执行语句记录的数据query方法
	 * 执行非查询类的操作使用，查询类操作使用$this->_db->query()
	 * @param string $sql 需要实际执行的语句
	 * @param string $sql_restore 恢复执行状态的语句
	 */
	protected function _rquery($sql = '', $sql_restore = '') {

		if (is_array($sql)) {
			$sql = implode(' ', $sql);
		}
		if (is_array($sql_restore)) {
			$sql_restore = implode(' ', $sql_restore);
		}

		$sql = trim($sql);

		// 执行
		$ret = $this->_db->query($sql);

		// 非查询类的操作则忽略写日志
		$write_cmd = array('UPDATE', 'INSERT', 'DELETE', 'REPLACE');
		if (!preg_match('/^['.implode('|', $write_cmd).']/i', $sql)) {
			return $ret;
		}

		// 整理sql语句，加入;和换行
		if (substr($sql, -1) != ';') {
			$sql .= ';';
		}
		$sql .= "\r\n";
		// 写入已执行SQL
		file_put_contents($this->_sql_logfile, $sql, FILE_APPEND);

		// 整理sql语句，加入;和换行
		$sql_restore = trim($sql_restore);
		if ($sql_restore) {
			if (substr($sql_restore, -1) != ';') {
				$sql_restore .= ';';
			}
			$sql_restore .= "\r\n";
			// 写入当前执行SQL的恢复语句
			file_put_contents($this->_sql_restore_logfile, $sql_restore, FILE_APPEND);
		}

		return $ret;
	}

	/**
	 * 升级旧的内置菜单数据
	 */
	protected function _update_old_icon_data() {
		$data = array();
		$data['manage']['']['']['module'] = array('ccm_name' => '人员管理', 'ccm_icon' => 'fa-group', 'ccm_subnavdisplay' => '1');
		$data['manage']['department']['']['operation'] = array('ccm_name' => '部门管理', 'ccm_icon' => '', 'ccm_subnavdisplay' => '1');
		$data['manage']['department']['add']['subop'] = array('ccm_name' => '添加新部门', 'ccm_icon' => 'fa-plus', 'ccm_subnavdisplay' => '1');
		$data['manage']['department']['delete']['subop'] = array('ccm_name' => '删除部门', 'ccm_icon' => 'fa-times', 'ccm_subnavdisplay' => '0');
		$data['manage']['department']['edit']['subop'] = array('ccm_name' => '修改部门', 'ccm_icon' => 'fa-edit', 'ccm_subnavdisplay' => '0');
		$data['manage']['department']['list']['subop'] = array('ccm_name' => '部门列表', 'ccm_icon' => 'fa-list', 'ccm_subnavdisplay' => '1');
		$data['manage']['job']['']['operation'] = array('ccm_name' => '职位管理', 'ccm_icon' => '', 'ccm_subnavdisplay' => '1');
		$data['manage']['job']['add']['subop'] = array('ccm_name' => '添加新职位', 'ccm_icon' => 'fa-plus', 'ccm_subnavdisplay' => '1');
		$data['manage']['job']['list']['subop'] = array('ccm_name' => '职位列表', 'ccm_icon' => 'fa-list', 'ccm_subnavdisplay' => '1');
		$data['manage']['job']['modify']['subop'] = array('ccm_name' => '删除修改', 'ccm_icon' => 'fa-edit', 'ccm_subnavdisplay' => '0');
		$data['manage']['member']['']['operation'] = array('ccm_name' => '员工管理', 'ccm_icon' => '', 'ccm_subnavdisplay' => '1');
		$data['manage']['member']['delete']['subop'] = array('ccm_name' => '删除员工', 'ccm_icon' => 'fa-times', 'ccm_subnavdisplay' => '0');
		$data['manage']['member']['edit']['subop'] = array('ccm_name' => '编辑员工', 'ccm_icon' => 'fa-edit', 'ccm_subnavdisplay' => '0');
		$data['manage']['member']['list']['subop'] = array('ccm_name' => '员工列表', 'ccm_icon' => 'fa-list', 'ccm_subnavdisplay' => '1');
		$data['manage']['member']['search']['subop'] = array('ccm_name' => '搜索员工', 'ccm_icon' => 'fa-search', 'ccm_subnavdisplay' => '1');
		$data['office']['']['']['module'] = array('ccm_name' => '应用数据', 'ccm_icon' => 'fa-tachometer', 'ccm_subnavdisplay' => '1');
		$data['setting']['']['']['module'] = array('ccm_name' => '应用中心', 'ccm_icon' => 'fa-cloud', 'ccm_subnavdisplay' => '1');
		$data['setting']['application']['']['operation'] = array('ccm_name' => '应用中心', 'ccm_icon' => '', 'ccm_subnavdisplay' => '1');
		$data['setting']['application']['delete']['subop'] = array('ccm_name' => '删除应用及其数据', 'ccm_icon' => 'fa-times', 'ccm_subnavdisplay' => '0');
		$data['setting']['application']['edit']['subop'] = array('ccm_name' => '启用/关闭应用', 'ccm_icon' => 'fa-laptop', 'ccm_subnavdisplay' => '0');
		$data['setting']['application']['list']['subop'] = array('ccm_name' => '应用中心', 'ccm_icon' => 'fa-gear', 'ccm_subnavdisplay' => '1');
		$data['system']['setting']['']['operation'] = array('ccm_name' => '环境设置', 'ccm_icon' => '', 'ccm_subnavdisplay' => '1');
		$data['system']['setting']['modify']['subop'] = array('ccm_name' => '更改设置', 'ccm_icon' => 'fa-gear', 'ccm_subnavdisplay' => '0');
		$data['setting']['servicetype']['modify']['subop'] = array('ccm_name' => '服务类型设置', 'ccm_icon' => 'fa-gear', 'ccm_subnavdisplay' => '0');
		$data['setting']['servicetype']['']['operation'] = array('ccm_name' => '服务类型设置', 'ccm_icon' => '', 'ccm_subnavdisplay' => '1');
		$data['system']['']['']['module'] = array('ccm_name' => '系统设置', 'ccm_icon' => 'fa-cogs', 'ccm_subnavdisplay' => '1');
		$data['system']['adminer']['']['operation'] = array('ccm_name' => '管理员', 'ccm_icon' => '', 'ccm_subnavdisplay' => '1');
		$data['system']['adminer']['add']['subop'] = array('ccm_name' => '添加管理员', 'ccm_icon' => 'fa-plus', 'ccm_subnavdisplay' => '1');
		$data['system']['adminer']['delete']['subop'] = array('ccm_name' => '删除管理员', 'ccm_icon' => 'fa-times', 'ccm_subnavdisplay' => '0');
		$data['system']['adminer']['edit']['subop'] = array('ccm_name' => '编辑管理员', 'ccm_icon' => 'fa-edit', 'ccm_subnavdisplay' => '0');
		$data['system']['adminer']['list']['subop'] = array('ccm_name' => '管理员列表', 'ccm_icon' => 'fa-list', 'ccm_subnavdisplay' => '1');
		$data['system']['adminergroup']['']['operation'] = array('ccm_name' => '权限管理', 'ccm_icon' => '', 'ccm_subnavdisplay' => '1');
		$data['system']['adminergroup']['add']['subop'] = array('ccm_name' => '新增管理组', 'ccm_icon' => 'fa-plus', 'ccm_subnavdisplay' => '1');
		$data['system']['adminergroup']['delete']['subop'] = array('ccm_name' => '删除管理组', 'ccm_icon' => 'fa-times', 'ccm_subnavdisplay' => '0');
		$data['system']['adminergroup']['edit']['subop'] = array('ccm_name' => '编辑管理组', 'ccm_icon' => 'fa-edit', 'ccm_subnavdisplay' => '0');
		$data['system']['adminergroup']['list']['subop'] = array('ccm_name' => '管理组列表', 'ccm_icon' => 'fa-list', 'ccm_subnavdisplay' => '1');
		$data['system']['cache']['']['operation'] = array('ccm_name' => '缓存更新', 'ccm_icon' => '', 'ccm_subnavdisplay' => '1');
		$data['system']['cache']['refresh']['subop'] = array('ccm_name' => '更新缓存', 'ccm_icon' => 'fa-refresh', 'ccm_subnavdisplay' => '1');
		$data['manage']['member']['add']['subop'] = array('ccm_name' => '添加新员工', 'ccm_icon' => 'fa-plus', 'ccm_subnavdisplay' => '1');
		$data['manage']['member']['import']['subop'] = array('ccm_name' => '导入通讯录', 'ccm_icon' => 'fa-cloud-upload', 'ccm_subnavdisplay' => '1');
		$data['manage']['member']['impqywx']['subop'] = array('ccm_name' => '同步通讯录', 'ccm_icon' => 'fa-exchange', 'ccm_subnavdisplay' => '1');


		foreach ($data as $_module => $a1) {
			foreach ($a1 as $_operation => $a2) {
				foreach ($a2 as $_subop => $a3) {
					foreach ($a3 as $_type => $_data) {
						$sql = "UPDATE `{$this->_tablepre}common_cpmenu` SET `ccm_name`='{$_data['ccm_name']}', `ccm_icon`='{$_data['ccm_icon']}', `ccm_subnavdisplay`='{$_data['ccm_subnavdisplay']}'  WHERE `ccm_module`='{$_module}' AND `ccm_operation`='{$_operation}' AND `ccm_subop`='{$_subop}' AND `ccm_type`='{$_type}'";
						$this->_rquery($sql, '');
					}
				}
			}
		}

	}


}
