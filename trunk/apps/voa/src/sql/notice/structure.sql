CREATE TABLE IF NOT EXISTS `{$prefix}notice` (
  `nt_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名称',
  `nt_author` varchar(54) NOT NULL DEFAULT '' COMMENT '公告发布人',
  `nt_tag` varchar(54) NOT NULL DEFAULT '' COMMENT '公告标签、类别',
  `nt_subject` varchar(81) NOT NULL DEFAULT '' COMMENT '公告主题',
  `nt_message` mediumtext NOT NULL COMMENT '公告内容',
  `nt_repeattimestamp` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未读时的重复提醒间隔，0不重复提醒，单位：秒',
  `nt_receiver` text NOT NULL COMMENT '接受者数组序列化字符串，array(m_uid=>阅读时间, .....)',
  `nt_remindtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下轮触发提醒的时间戳，0为不再提醒',
  `nt_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已更新, 3=草稿, 4=已删除',
  `nt_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `nt_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `nt_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`nt_id`),
  KEY `m_uid` (`m_uid`,`nt_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公告主题表';


CREATE TABLE IF NOT EXISTS `{$prefix}notice_attachment` (
  `ntat_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名称',
  `nt_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公告id',
  `at_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件id',
  `ntat_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '记录状态, 1=正常; 2=已删除;',
  `ntat_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ntat_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ntat_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ntat_id`),
  KEY `nt_id` (`nt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公告的附件表';


CREATE TABLE IF NOT EXISTS `{$prefix}notice_read` (
  `ntr_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `nt_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '通知公告ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '阅读人ID',
  `ntr_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化, 2=已更新, 3=已删除',
  `ntr_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间(首次阅读时间)',
  `ntr_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ntr_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ntr_id`),
  KEY `nt_id` (`nt_id`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='通知公告已读表';


CREATE TABLE IF NOT EXISTS `{$prefix}notice_setting` (
  `nts_key` varchar(50) NOT NULL COMMENT '变量名',
  `nts_value` text NOT NULL COMMENT '值',
  `nts_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `nts_comment` text NOT NULL COMMENT '说明',
  `nts_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `nts_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `nts_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `nts_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`nts_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公告设置表';


CREATE TABLE IF NOT EXISTS `{$prefix}notice_to` (
  `ntt_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `nt_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '通知公告ID',
  `cd_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接收部门ID(=0所有部门)',
  `ntt_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化, 2=已更新, 3=已删除',
  `ntt_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ntt_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ntt_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ntt_id`),
  KEY `nt_id` (`nt_id`),
  KEY `m_uid` (`cd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='通知公告接收部门表';
