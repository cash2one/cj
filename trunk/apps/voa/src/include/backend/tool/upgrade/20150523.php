<?php
/**
 * 20150523.php
 * 人员管理后台打开同步菜单
 * php -q tool.php -n upgrade -version 20150523 -epid vchangyi_oa_upgrade
 * Create By luck
 * $Author$
 * $Id$
 */
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

	/** 当前升级的应用信息 */
	private $__plugin = array();

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
	}

	/**
	 * 脚本执行的主方法，不同的升级脚本具体操作动作不相同
	 * @return void
	 */
	public function run() {
		error_reporting(E_ALL);

		$this->_plugin_table();
		// 公共表结构
		$this->_common_table();
		// 清理缓存
		$this->_cache_clear();
	}

	/**
	 * 后台菜单升级
	 */
	protected function _plugin_cpmenu() {
		return true;
	}

	/**
	 * 应用表结构升级
	 */
	protected function _plugin_table() {

		$menu = $this->_db->fetch_first("SELECT * FROM `oa_common_cpmenu` WHERE `ccm_module`='manage' AND `ccm_operation`='member' AND `ccm_subop`='impqywx' LIMIT 1");
		if (!empty($menu)) {
			$this->_db->query("UPDATE `oa_common_cpmenu` SET `ccm_status`=2 WHERE `ccm_module`='manage' AND `ccm_operation`='member' AND `ccm_subop`='impqywx' LIMIT 1");
		} else {
			$this->_db->query("INSERT INTO `oa_common_cpmenu` (`cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_subnavdisplay`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`) VALUES
(0,	1,	'manage',	'member',	'impqywx',	'subop',	0,	'同步通讯录',	'fa-exchange',	1,	232,	1,	2,	0,	0,	0);");
		}

		return true;
	}

	/**
	 * 公共表结构升级
	 */
	protected function _common_table() {
		return true;
	}

	/**
	 * 清理缓存
	 */
	protected function _cache_clear() {

		// 当前站点的缓存目录
		$cachedir = $this->_params['cachedir'];

		// 读取缓存目录下的文件
		$handle = opendir($cachedir);
		// 清理后台菜单缓存文件
		if ($handle) {
			while (false !== ($file = readdir($handle))) {

				// 判断是否是有效的菜单缓存文件
				if ($file == 'cpmenu.php' || preg_match('/^adminergroupcpmenu\.\d+/', $file)) {
					// 删除
					unlink($cachedir . DIRECTORY_SEPARATOR . $file);
				}
			}
		}
	}
}
