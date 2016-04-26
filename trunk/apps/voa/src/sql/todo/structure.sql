CREATE TABLE IF NOT EXISTS `{$prefix}todo{$suffix}` (
  `td_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `td_subject` varchar(81) NOT NULL COMMENT '待办事项主题',
  `td_calltime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '通知时间',
  `td_exptime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '截止时间',
  `td_completed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '已经完成?0=否，1=是',
  `td_stared` tinyint(1) NOT NULL DEFAULT '0' COMMENT '星标?0=否，1=是',
  `td_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已更新, 3=已删除',
  `td_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `td_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `td_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`td_id`),
  KEY `m_uid` (`m_uid`,`td_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='待办事项主题表';

CREATE TABLE IF NOT EXISTS `{$prefix}todo_mem{$suffix}` (
  `tdm_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `td_id` int(10) unsigned NOT NULL COMMENT '待办事项ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID, 为 0 时, 全体成员可看',
  `m_username` varchar(54) NOT NULL COMMENT '用户名',
  `tdm_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=抄送人，4=已删除',
  `tdm_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `tdm_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `tdm_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tdm_id`),
  KEY `tdm_id` (`tdm_id`,`tdm_status`),
  KEY `m_uid` (`m_uid`,`tdm_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='可查看待办事项人员列表';

CREATE TABLE IF NOT EXISTS `{$prefix}todo_setting{$suffix}` (
  `tds_key` varchar(50) NOT NULL COMMENT '变量名',
  `tds_value` text NOT NULL COMMENT '值',
  `tds_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `tds_comment` text NOT NULL COMMENT '说明',
  `tds_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `tds_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `tds_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `tds_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tds_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='待办事项设置表';
