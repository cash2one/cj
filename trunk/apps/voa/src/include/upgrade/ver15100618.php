<?php
/**
 * voa_upgrade_ver15100618
 * $Author$
 * $Id$
 */

class voa_upgrade_ver15100618 extends voa_upgrade_base {

	public function __construct() {

		parent::__construct();
		$this->_ver = '15100618';
	}

	// 升级
	public function upgrade() {

		$q = $this->_db->query("SHOW TABLES LIKE 'oa_member_userlog'");
		if (! $row = $this->_db->fetch_row($q)) {
			$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_common_userlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'uid',
  `year` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '年份',
  `month` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '月份',
  `day` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '日期',
  `week` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '星期值',
  `status` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态, 1: 创建; 2: 更新; 3: 删除;',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户活跃统计'");
		}

		return true;
	}

}
