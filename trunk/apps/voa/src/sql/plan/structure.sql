CREATE TABLE IF NOT EXISTS `{$prefix}plan{$suffix}` (
  `pl_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `pl_type` int(10) NOT NULL COMMENT '日程类型',
  `pl_subject` varchar(255) NOT NULL COMMENT '日程主题',
  `pl_address` varchar(255) NOT NULL DEFAULT '' COMMENT '日程地址',
  `pl_begin_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `pl_finish_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `pl_alarm_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提醒时间',
  `pl_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已更新, 3=已删除',
  `pl_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `pl_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `pl_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`pl_id`),
  KEY `m_uid` (`m_uid`,`pl_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='日程主题表';


CREATE TABLE IF NOT EXISTS `{$prefix}plan_mem{$suffix}` (
  `plm_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `pl_id` int(10) unsigned NOT NULL COMMENT '日程ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID, 为 0 时, 全体成员可看',
  `m_username` varchar(54) NOT NULL COMMENT '用户名',
  `plm_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=抄送人，4=已删除',
  `plm_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `plm_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `plm_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`plm_id`),
  KEY `plm_id` (`plm_id`,`plm_status`),
  KEY `m_uid` (`m_uid`,`plm_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='可查看日程人员列表';


CREATE TABLE IF NOT EXISTS `{$prefix}plan_setting{$suffix}` (
  `pls_key` varchar(50) NOT NULL COMMENT '变量名',
  `pls_value` text NOT NULL COMMENT '值',
  `pls_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `pls_comment` text NOT NULL COMMENT '说明',
  `pls_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `pls_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `pls_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `pls_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`pls_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='日程设置表';
