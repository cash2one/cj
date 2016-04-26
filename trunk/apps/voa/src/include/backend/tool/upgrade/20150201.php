<?php
/**
 * 20150201.php
 * 1.公共附件表新增at_mediatype字段标记文件类型
 * 2.使用微信jsapi的公共的图片上传组件
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
			'upgrade_sql',// 升级表结构
		);

		foreach ($steps as $_step) {
			$classname = '_'.$_step;
			$this->$classname();
		}

	}

	/**
	 * 升级表结构
	 */
	protected function _upgrade_sql() {

		// 新增mediatype字段
		$this->_db->query("ALTER TABLE `oa_common_attachment`
ADD `at_mediatype` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '文件类型标记：0=未知，99=普通文件，1=图片，2=音频，3=视频' AFTER `at_filesize`");
		// 更新之前数据全部为图片类型
		$this->_db->query("UPDATE `oa_common_attachment` SET `at_mediatype`='1'");

		return true;
	}

}
