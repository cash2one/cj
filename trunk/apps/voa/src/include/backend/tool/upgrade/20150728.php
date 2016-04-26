<?php

/**
 * 20150728.php
 * 统一登录
 * cd C:\wamp\www\h5\trunk\apps\voa\backend\
 * php -q tool.php -n upgrade -version 20150728 -epid vchangyi_oa
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
	 *
	 * @param object $db 数据库操作对象
	 * @param string $tablepre 表前缀
	 * @param array  $settings 当前站点的setting
	 * @param array  $options 传输进来的外部参数
	 * @param array  $params 一些环境参数，来自触发执行本脚本
	 *
	 * @see voa_backend_tool_upgrade::main()
	 */
	public function init( $db, $tablepre, $settings, $options, $params ) {
		$this->_db       = $db;
		$this->_tablepre = $tablepre;
		$this->_settings = $settings;
		$this->_options  = $options;
		$this->_params   = $params;
	}

	/**
	 * 脚本执行的主方法，不同的升级脚本具体操作动作不相同
	 * @return void
	 */
	public function run() {
		error_reporting( E_ALL );

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

		$query = "
CREATE TABLE `oa_member_loginqrcode` (
  `auth_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `m_uid` int(10) NOT NULL DEFAULT '0' COMMENT '登录人的ID',
  `authcode` char(32) NOT NULL DEFAULT '' COMMENT 'authcode密钥',
  `errmsg` char(64) NOT NULL DEFAULT '' COMMENT '错误信息',
  `state` int(3) NOT NULL DEFAULT '0' COMMENT '登录状态：0,已获取密钥;1,已扫描; 2,已登录',
  `ip` char(15) NOT NULL DEFAULT '0' COMMENT '登录的IP地址',
  `status` int(3) NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常; 2=更新; 3=已删除',
  `created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`auth_id`),
  UNIQUE KEY `authcode` (`authcode`),
  KEY `state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PC auth登录'";

		$this->_db->query( $query );

		$this->_db->query( "ALTER TABLE oa_common_plugin_display ADD `cpd_lastusetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次使用时间' AFTER `cpd_ordernum`;" );
		$this->_db->query("ALTER TABLE oa_common_attachment ADD at_isattach TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0:不是附件,1:是附件' AFTER at_isimage;");

		$query = $this->_db->query("SHOW TABLES LIKE 'oa_dailyreport'");
		if ($this->_db->fetch_row($query)) {
			$this->_db->query("CREATE TABLE `oa_dailyreport_read` (
						  `rid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
						  `is_read` tinyint(1) unsigned NOT NULL COMMENT '1:未读,2已读',
						  `dr_id` int(10) unsigned NOT NULL COMMENT '日报id',
						  `m_uid` int(10) unsigned NOT NULL COMMENT '用户uid',
						  `status` int(10) unsigned NOT NULL COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
						  `created` int(10) unsigned NOT NULL COMMENT '创建时间',
						  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
						  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
						  PRIMARY KEY (`rid`),
						  KEY `read_index` (`status`,`created`,`updated`) USING BTREE
						) ENGINE=InnoDBDEFAULT CHARSET=utf8  COMMENT='工作日报读取状态';");
		}

		$query = $this->_db->query("SHOW TABLES LIKE 'oa_nvote'");
		if ($this->_db->fetch_row($query)) {
			$this->_db->query("ALTER TABLE `oa_nvote` ADD COLUMN is_repeat TINYINT(3) NOT NULL DEFAULT 2 COMMENT '是否允许重复投票' AFTER `is_show_result`;");
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
		$handle = opendir( $cachedir );
		// 清理后台菜单缓存文件
		if( $handle ) {
			while( false !== ( $file = readdir( $handle ) ) ) {

				// 判断是否是有效的菜单缓存文件
				if( $file == 'cpmenu.php' || preg_match( '/^adminergroupcpmenu\.\d+/', $file ) ) {
					// 删除
					unlink( $cachedir . DIRECTORY_SEPARATOR . $file );
				}
			}
		}
	}
}
