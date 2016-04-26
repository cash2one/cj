<?php
/**
 * 20150806.php
 * 审批迭代
 * php -q APP_PATH/backend/tool.php -n upgrade -version 20150311 -epid vchangyi_oa_upgrade
 * Create By Muzhitao
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

		// 获取应用信息
		$query = $this->_db->query("SELECT * FROM `oa_common_plugin` WHERE `cp_identifier`='askfor' LIMIT 1");
		$this->__plugin = $this->_db->fetch_array($query);

		// 判断应用表是否存在
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_askfor'");
		if ($this->_db->fetch_row($query)) {
			$file = APP_PATH."/data/upgrade/".$this->_options['version'].".log";
			$this->_params['upgrade']->rfwrite($file, "{$this->_params['dbname']}\t{$this->_settings['domain']}\n", 'a+');
			// 应用表结构
			$this->_plugin_table();
		}

		// 公共表结构
		if (empty($this->_options['common_table_no_run'])) {
			$this->_common_table();
		}

		// 清理缓存
		$this->_cache_clear();
	}

	/**
	 * 数据库表更新
	 */
	protected function _plugin_table() {
		//修复2015-08-06升级兼容问题(1、单行文本   2、多行文本   3数字 修复为 1、文本   2 数字)  1423821111转日期 2015-08-06 00:00:00
		$this->_db->query("UPDATE `oa_askfor_customcols` SET type = 1 where type = 2  and afcc_created < 1438790400;");
		$this->_db->query("UPDATE `oa_askfor_customcols` SET type = 2 where type = 3  and afcc_created < 1438790400;");
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
					unlink($cachedir.DIRECTORY_SEPARATOR.$file);
				}
			}
		}

	}

	/**
	 * 公共表结构
	 */
	protected function _common_table() {
		return true;
	}

}
