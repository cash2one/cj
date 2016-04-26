<?php
/**
 * upstandard.php
 * 标准升级程序, 升级程序一定要做到反复执行, 结果正确
 * @uses php tool.php -n upstandard
 * $Author$
 * $Id$
 */

class voa_backend_tool_upstandard extends voa_backend_base {

	// 参数
	private $__opts = array();

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		// 连接数据库
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$db = &db::init($cfg);

		// 判断数据库是否存在
		for($i = 10002; $i < 29257; ++ $i) {
			startup_env::set('sitedir', null);
			try {
				$db->query('use ep_' . $i);
				echo "ep_{$i}\n";
				$q = $db->query("SHOW TABLES LIKE 'oa_member_loginqrcode'");
				if (! $row = $db->fetch_row($q)) {
					$db->query("CREATE TABLE `oa_member_loginqrcode` (
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
					echo "create oa_member_loginqrcode\n";
				}
				$q = $db->query("SHOW TABLES LIKE 'oa_dailyreport'");
				if ($row = $db->fetch_row($q)) {
					$q = $db->query("SHOW TABLES LIKE 'oa_dailyreport_read'");
					if (! $row = $db->fetch_row($q)) {
						$db->query("CREATE TABLE `oa_dailyreport_read` (
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
						echo "create oa_dailyreport_read\n";
					}
				}
				$q = $db->query("SHOW FIELDS FROM oa_common_plugin_display LIKE 'cpd_lastusetime'");
				if (! $row = $db->fetch_row($q)) {
					$db->query("ALTER TABLE oa_common_plugin_display ADD `cpd_lastusetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次使用
时间' AFTER `cpd_ordernum`");
					echo "add field cpd_lastusetime\n";
				}

				$q = $db->query("SHOW FIELDS FROM oa_common_attachment LIKE 'at_isattach'");
				if (! $row = $db->fetch_row($q)) {
					$db->query("ALTER TABLE oa_common_attachment ADD at_isattach TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0:不是附件,1:是附件
' AFTER at_isimage");
					echo "add field at_isattach\n";
				}

				$q = $db->query("SHOW TABLES LIKE 'oa_nvote'");
				if ($row = $db->fetch_row($q)) {
					$q = $db->query("SHOW FIELDS FROM oa_nvote LIKE 'is_repeat'");
					if (! $row = $db->fetch_row($q)) {
						$db->query("ALTER TABLE `oa_nvote` ADD COLUMN is_repeat TINYINT(3) NOT NULL DEFAULT 2 COMMENT '是否允许重复投票' A
FTER `is_show_result`");
						echo "add field is_repeat\n";
					}
				}
			} catch (Exception $e) {
				continue;
			}
		}
	}

}
