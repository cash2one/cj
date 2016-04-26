<?php
/**
 * voa_upgrade_ver15081219
 * $Author$
 * $Id$
 */

class voa_upgrade_ver15081219 extends voa_upgrade_base {

	public function __construct() {

		parent::__construct();
		$this->_ver = '15081219';
	}

	// 升级
	public function upgrade() {

		$q = $this->_db->query("SHOW TABLES LIKE 'oa_member_loginqrcode'");
		if (! $row = $this->_db->fetch_row($q)) {
			$this->_db->query("CREATE TABLE `oa_member_loginqrcode` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PC auth登录'");
		}

		$q = $this->_db->query("SHOW TABLES LIKE 'oa_dailyreport'");
		if ($row = $this->_db->fetch_row($q)) {
			$q = $this->_db->query("SHOW TABLES LIKE 'oa_dailyreport_read'");
			if (! $row = $this->_db->fetch_row($q)) {
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
) ENGINE=InnoDBDEFAULT CHARSET=utf8COMMENT='工作日报读取状态'");
			}
		}

		// 升级
		$q = $this->_db->query("SHOW FIELDS FROM oa_common_plugin_display LIKE 'cpd_lastusetime'");
		if (! $row = $this->_db->fetch_row($q)) {
			$this->_db->query("ALTER TABLE oa_common_plugin_display ADD `cpd_lastusetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次使用时间' AFTER `cpd_ordernum`");
		}

		// 升级附件
		$q = $this->_db->query("SHOW FIELDS FROM oa_common_attachment LIKE 'at_isattach'");
		if (! $row = $this->_db->fetch_row($q)) {
			$this->_db->query("ALTER TABLE oa_common_attachment ADD at_isattach TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0:不是附件,1:是附件' AFTER at_isimage");
		}

		// 升级投票表
		$q = $this->_db->query("SHOW TABLES LIKE 'oa_nvote'");
		if ($row = $this->_db->fetch_row($q)) {
			$q = $this->_db->query("SHOW FIELDS FROM oa_nvote LIKE 'is_repeat'");
			if (! $row = $this->_db->fetch_row($q)) {
				$this->_db->query("ALTER TABLE `oa_nvote` ADD COLUMN is_repeat TINYINT(3) NOT NULL DEFAULT 2 COMMENT '是否允许重复投票' AFTER `is_show_result`");
			}
		}

		// 插件分组表
		$q = $this->_db->query("SELECT * FROM oa_common_plugin_group WHERE cpg_suiteid='tj706e8d913b31c376'");
		if (! $row = $this->_db->fetch_row($q)) {
			$this->_db->query("INSERT INTO `oa_common_plugin_group` (`cpg_id`, `cpg_suiteid`, `cpg_name`, `cpg_icon`, `cpg_ordernum`, `cpg_status`, `cpg_created`, `cpg_updated`, `cpg_deleted`) VALUES(7, 'tj706e8d913b31c376', '企业消息', 'fa-group', 0, 1, 0, 0, 0)");
		}

		// 插件信息
		$q = $this->_db->query("SELECT * FROM oa_common_plugin WHERE cp_identifier='chatgroup'");
		if (! $row = $this->_db->fetch_row($q)) {
			$this->_db->query("INSERT INTO `oa_common_plugin` (`cp_pluginid`, `cp_identifier`, `cmg_id`, `cpg_id`, `cp_suiteid`, `cp_agentid`, `cp_displayorder`, `cp_available`, `cp_adminid`, `cp_name`, `cp_icon`, `cp_description`, `cp_datatables`, `cp_directory`, `cp_url`, `cp_version`, `cp_lastavailable`, `cp_lastopen`, `cyea_id`, `cp_status`, `cp_created`, `cp_updated`, `cp_deleted`) VALUES(36,	'chatgroup',	1,	7,	'',	'',	1025,	0,	0,	'同事聊天',	'chatgroup.png',	'同事交流最好的平台；无需添加好友，同事之间可以直接发起会话，PC端和微信手机端消息实时互通。',	'chatgroup*',	'chatgroup',	'chatgroup.php',	'0.1',	0,	0,	0,	1,	1417145069,	0,	0)");
		}

		return true;
	}

}
