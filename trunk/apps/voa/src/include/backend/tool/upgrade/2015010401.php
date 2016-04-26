<?php
/**
 * 2015010401.php
 * 第一次迭代升级脚本
 * 思路：
 * 检查当前库开启的应用，使用升级语句对应进行升级。
 * 同时检查菜单，重写菜单，对于企业号有变动的执行接口更新
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
	/** 已开启应用列表 */
	protected $_app_list = array();
	/** 应用标识名 */
	protected $_plugin_name = '';

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

		// 遍历所有应用，找到已开启的应用
		// 读取全部应用
		$this->_app_list = array();
		$query = $this->_db->query("SELECT * FROM `{$this->_tablepre}common_plugin`");
		while ($row = $this->_db->fetch_array($query)) {
			if ($row['cp_available'] != voa_d_oa_common_plugin::AVAILABLE_OPEN) {
				// 未启用则忽略
				continue;
			}
			$this->_app_list[$row['cp_identifier']] = $row;
		}


	}

	/**
	 * 脚本执行的主方法，不同的升级脚本具体操作动作不相同
	 * @return void
	 */
	public function run() {

		error_reporting(E_ALL);

		// 升级用户关键信息表
		$this->__update_member();

		// 未启用应用，则跳过
		if (empty($this->_app_list)) {
			throw new Exception('no application', 1001);
			return true;
		}

		// 需要执行的动作 table=表结构；field=表字段；data=表数据；cpmenu后台菜单；wxqymenu=企业号自定义菜单
		$action_datas = array(
			'table', 'field', 'data', 'cpmenu', 'wxqymenu'
		);

		// 遍历已开启的应用
		foreach ($this->_app_list as $_cp) {

			// 遍历全部动作，并执行
			foreach ($action_datas as $_act) {
				$file_path = dirname(__FILE__).DIRECTORY_SEPARATOR.$this->_options['version'].DIRECTORY_SEPARATOR.$_cp['cp_identifier'].DIRECTORY_SEPARATOR.$_act.'.txt';
				if (!is_file($file_path)) {
					continue;
				}
				$file_data = file_get_contents($file_path);

				$_method = '__act_'.$_act;
				$this->$_method($_cp['cp_identifier'], $file_data, $_cp);
			}
			unset($_act);

			// 更新此应用的缓存
			@unlink($this->_params['cachedir'].DIRECTORY_SEPARATOR.'plugin.'.$_cp['cp_identifier'].'.setting.php');
		}

		// 更新菜单缓存
		$this->_cpmenu_cache_clear();

		return true;
	}

	/**
	 * 升级用户表
	 */
	private function __update_member() {

		$this->_db->query("ALTER TABLE `oa_common_department` CHANGE `cd_name` `cd_name` varchar(255)  COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '部门简称' after `cd_displayorder`;");
		$this->_db->query("ALTER TABLE `oa_common_job` CHANGE `cj_name` `cj_name` varchar(255)  COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '职位简称' after `cj_displayorder`;");

	}

	/**
	 * 升级数据表结构
	 */
	private function __act_table($plugin_identifier, $data, $plugin) {

		$this->_db->query($data);

	}

	/**
	 * 升级表字段
	 * @param unknown $data
	 */
	private function __act_field($plugin_identifier, $data, $plugin) {

		$this->_db->query($data);

	}

	/**
	 * 升级默认数据
	 */
	private function __act_data($plugin_identifier, $data, $plugin) {

		$this->_db->query($data);

	}

	/**
	 * 升级后台菜单
	 */
	private function __act_cpmenu($plugin_identifier, $data, $plugin) {

		// 读取一个子菜单，然后注入一条新的
		$menu = $this->_db->fetch_first("SELECT * FROM `oa_common_cpmenu` WHERE `ccm_module`!='' AND `ccm_operation`='{$plugin_identifier}' AND `ccm_subop`!='' AND `ccm_type`='subop' AND `ccm_status`<3");
		// 没有已知的该应用的后台菜单
		if (empty($menu)) {
			return;
		}

		$data = @unserialize($data);
		// 无法读取新增的后台菜单数据
		if (empty($data)) {
			return;
		}

		// 遍历新增的菜单数据
		foreach ($data as $_subop => $_data) {

			// 新增的菜单数据
			$new_data = array();
			foreach ($_data as $_k => $_v) {
				$new_data['ccm_'.$_k] = $_v;
			}
			unset($_k, $_v);

			// 填充已知的字段数据
			foreach ($menu as $_var => $_val) {
				if ($_var == 'ccm_id') {
					continue;
				}
				if (!isset($new_data[$_var])) {
					$new_data[$_var] = $_val;
				}
			}
			$new_data['ccm_subop'] = $_subop;
			$new_data['ccm_status'] = 1;
			$new_data['ccm_created'] = time();
			$new_data['ccm_updated'] = time();
			unset($_var, $_val);

			// 整理SQL
			$this->_db->query("INSERT INTO `oa_common_cpmenu` (`".implode("`, `", array_keys($new_data))."`) VALUES ('".implode("', '", array_values($new_data))."')");

			unset($new_data);
		}
		unset($_subop, $_data);

	}

	/**
	 * 升级微信企业号自定义菜单
	 */
	private function __act_wxqymenu($plugin_identifier, $data, $plugin) {

		// 暂时不更新此任务
		return;

		$data = @unserialize($data);
		if (empty($data)) {
			return;
		}

		//$agent_menu = config::get(startup_env::get('app_name').'.application.'.$plugin_identifier.'.menu.qywx');
		$agent_menu = $data;
		if (!empty($agent_menu)) {

			// 加载企业微信应用型代理菜单接口类
			$qywx_menu = new voa_wxqy_menu();
			// 创建菜单
			if (!$qywx_menu->create($plugin['cp_agentid'], $agent_menu, $plugin['cp_pluginid'])) {
				throw new Exception(empty($qywx_menu->menu_error) ? '创建应用菜单发生错误' : $qywx_menu->menu_error, 1100);
				return false;
			}
		}

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

}

