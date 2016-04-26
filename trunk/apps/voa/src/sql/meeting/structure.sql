CREATE TABLE IF NOT EXISTS `{$prefix}meeting{$suffix}` (
  `mt_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '会议发起人UID',
  `m_username` varchar(54) NOT NULL COMMENT '会议发起人用户名',
  `mt_uid` int(10) unsigned NOT NULL COMMENT '会议预定人UID',
  `mt_username` varchar(54) NOT NULL COMMENT '会议预定人',
  `mr_id` int(10) unsigned NOT NULL COMMENT '会议室ID',
  `mt_address` varchar(255) NOT NULL COMMENT '会议详细地址(如果有值, 则忽略会议室代码)',
  `mt_subject` varchar(255) NOT NULL COMMENT '会议主题',
  `mt_message` text NOT NULL COMMENT '会议内容',
  `mt_noticetime` int(10) unsigned NOT NULL COMMENT '会议提前通知时间',
  `mt_invitenum` mediumint(8) unsigned NOT NULL COMMENT '邀请参会人数',
  `mt_agreenum` mediumint(8) unsigned NOT NULL COMMENT '确认参会人数',
  `mt_refusenum` mediumint(8) unsigned NOT NULL COMMENT '拒绝参会人数',
  `mt_begintime` int(10) unsigned NOT NULL COMMENT '会议开始时间',
  `mt_endtime` int(10) unsigned NOT NULL COMMENT '会议结束时间',
  `mt_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已取消，4=已删除',
  `mt_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `mt_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `mt_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`mt_id`),
  KEY `mr_id` (`mr_id`,`mt_status`),
  KEY `mt_begintime` (`mt_begintime`),
  KEY `mt_endtime` (`mt_endtime`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='会议表';


CREATE TABLE IF NOT EXISTS `{$prefix}meeting_draft{$suffix}` (
  `mtd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_openid` varchar(32) NOT NULL COMMENT '用户名称',
  `mtd_subject` varchar(81) NOT NULL COMMENT '主题',
  `mtd_message` text NOT NULL COMMENT '内容',
  `mtd_cc_uid` text NOT NULL COMMENT '抄送人uid, 以","分隔',
  `mtd_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已删除',
  `mtd_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mtd_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `mtd_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`mtd_id`),
  KEY `m_uid` (`mtd_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会议草稿表';


CREATE TABLE IF NOT EXISTS `{$prefix}meeting_mem{$suffix}` (
  `mm_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `mt_id` int(10) unsigned NOT NULL COMMENT '会议ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '参会用户UID, 为 0 时, 全体成员参会',
  `m_username` varchar(54) NOT NULL COMMENT '参会用户名',
  `mm_reason` varchar(255) NOT NULL COMMENT '不能参会的原因',
  `mm_confirm` tinyint(1) DEFAULT '0' COMMENT '签到',
  `mm_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化, 2=确认参加, 3=不参加, 4=已取消, 5=已删除',
  `mm_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `mm_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `mm_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`mm_id`),
  KEY `mt_id` (`mt_id`,`mm_status`),
  KEY `m_uid` (`m_uid`,`mm_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='参加会议人员列表';


CREATE TABLE IF NOT EXISTS `{$prefix}meeting_room{$suffix}` (
  `mr_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `mr_name` varchar(30) NOT NULL DEFAULT '' COMMENT '会议室简称',
  `mr_address` varchar(255) NOT NULL DEFAULT '' COMMENT '会议室地址',
  `mr_floor` tinyint(3) unsigned DEFAULT '1' COMMENT '楼层',
  `mr_galleryful` varchar(255) NOT NULL DEFAULT '' COMMENT '容纳人数',
  `mr_device` varchar(255) NOT NULL DEFAULT '' COMMENT '设备',
  `mr_volume` tinyint(3) unsigned NOT NULL DEFAULT '2' COMMENT '会议室容积, 1:小; 2:中; 3:大',
  `mr_timestart` time NOT NULL DEFAULT '09:00:00' COMMENT '可预定时间，开始时间',
  `mr_timeend` time NOT NULL DEFAULT '18:00:00' COMMENT '可预定时间，结束时间',
  `mr_code` tinyint(3) DEFAULT '0' COMMENT '微信二维码code,小于100',
  `mr_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `mr_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mr_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `mr_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`mr_id`),
  KEY `mr_status` (`mr_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='会议室列表';


CREATE TABLE IF NOT EXISTS `{$prefix}meeting_setting{$suffix}` (
  `ms_key` varchar(50) NOT NULL COMMENT '变量名',
  `ms_value` text NOT NULL COMMENT '值',
  `ms_type` tinyint(3) unsigned NOT NULL COMMENT '缓存类型, 0:非数组, 1:数组',
  `ms_comment` text NOT NULL COMMENT '说明',
  `ms_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `ms_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `ms_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `ms_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`ms_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会议设置表';