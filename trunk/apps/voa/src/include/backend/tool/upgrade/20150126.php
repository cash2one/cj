<?php
/**
 * 20150125.php
 * 1.套件表内新增jsapi ticket数据缓存相关字段
 * 2.培训应用上线
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

	}

	/**
	 * 脚本执行的主方法，不同的升级脚本具体操作动作不相同
	 * @return void
	 */
	public function run() {

		error_reporting(E_ALL);

		$steps = array(
			'upgrade_sql',// 升级套件表结构
			'open_showroom',// 启用陈列应用
			'cache_clear', // 清理cpmenu菜单缓存
		);

		foreach ($steps as $_step) {
			$classname = '_'.$_step;
			$this->$classname();
		}

	}

	/**
	 * 升级套件表结构
	 */
	protected function _upgrade_sql() {

		$this->_db->query("ALTER TABLE `oa_suite`
ADD `jsapi_ticket` varchar(255) NOT NULL DEFAULT '' COMMENT 'jsapi ticket缓存' AFTER `expires`,
ADD `jsapi_ticket_expire` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'jsapi ticket过期时间' AFTER `jsapi_ticket`");

		return true;
	}

	/**
	 * 启用应用
	 */
	protected function _open_showroom() {

		/**
		 * 思路，先尝试删除，再进行添加，避免某些站点不存在此应用记录的情况
		 */
		$timestamp = time();
		// 先尝试删除
		$this->_db->query("DELETE FROM `oa_common_plugin` WHERE `cp_identifier`='showroom'");
		// 新增
		$this->_db->query("INSERT INTO `oa_common_plugin` (`cp_identifier`,
				`cmg_id`, `cpg_id`, `cp_suiteid`, `cp_agentid`, `cp_displayorder`,
				`cp_available`, `cp_adminid`, `cp_name`, `cp_icon`, `cp_description`,
				`cp_datatables`, `cp_directory`, `cp_url`, `cp_version`,
				`cp_lastavailable`, `cp_lastopen`, `cyea_id`, `cp_status`,
				`cp_created`, `cp_updated`, `cp_deleted`) VALUES (
				'showroom', 1, 3, '', '', 1026, 0, 0, '陈列', 'showroom.png',
				'门店陈列摆设', 'showroom*', 'showroom', 'showroom.php', '0.1',
				0, 0, 0, 1, {$timestamp}, 0, 0)");

		return true;
	}

	/**
	 * 清理缓存
	 */
	protected function _cache_clear() {

		// 当前站点的缓存目录
		$cachedir = $this->_params['cachedir'];

		// 清理应用信息缓存
		@unlink($cachedir.DIRECTORY_SEPARATOR.'plugin.php');
		// 试图清理培训应用的设置缓存
		@unlink($cachedir.DIRECTORY_SEPARATOR.'plugin.train.setting.php');

		// 读取缓存目录下的文件
		$handle = opendir($cachedir);
		// 清理后台菜单缓存文件
		if ($handle) {
			while (false !== ($file = readdir($handle))) {

				// 判断是否是有效的菜单缓存文件
				if ($file == 'cpmenu.php' || preg_match('/^adminergroupcpmenu\.\d+/', $file)) {
					// 删除
					unlink($cachedir.DIRECTORY_SEPARATOR.$file);
					break;
				}
			}
		}

	}

}
