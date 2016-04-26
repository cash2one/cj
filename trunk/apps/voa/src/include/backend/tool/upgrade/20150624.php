<?php
/**
 * 20150624.php
 * 新闻公告点赞应用
 * cd C:\wamp\www\h5\trunk\apps\voa\backend\     ?
 * php -q tool.php -n upgrade -version 20150624 -epid vchangyi_oa
 * Create By huangzhongzheng
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
	 *
	 * @return void
	 */
	public function run() {
		error_reporting(E_ALL);

		$identifier = 'news';

		// 公共表结构
		$this->_common_table();

		// 判断应用表是否存在
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_{$identifier}'");
		if ($this->_db->fetch_row($query)) {;
			// 应用表结构
			$this->_plugin_table();
		}

		// 清理缓存
		$this->_cache_clear();
	}

	/**
	 * 应用表结构升级
	 */
	protected function _plugin_table() {

		$this->_db->query("ALTER TABLE `oa_news`
			ADD `is_like` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启赞：0=不开启；1=开启' AFTER `check_summary`,
			ADD `num_like` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞次数' AFTER `is_like`, COMMENT='新闻公告表';");

		$this->_db->query("CREATE TABLE `oa_news_like` (
  			`like_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 			`m_uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '点赞用户uid',
  			`description` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '点赞操作;1=> 次数 -1,  2=> 次数+1，默认2',
  			`ne_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新闻公告ID',
  			`ip` varchar(150) NOT NULL DEFAULT '0.0.0.0' COMMENT 'ip地址',
  			`status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  			`created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  			`updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  			`deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  			PRIMARY KEY (`like_id`),
  			KEY `ne_id` (`ne_id`,`m_uid`) USING BTREE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='新闻公告点赞表';");

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
