CREATE TABLE IF NOT EXISTS `{$prefix}minutes{$suffix}` (
  `mi_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名称',
  `mi_subject` varchar(81) NOT NULL DEFAULT '' COMMENT '会议记录主题',
  `mi_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已更新, 3=已删除',
  `mi_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mi_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `mi_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`mi_id`),
  KEY `m_uid` (`m_uid`,`mi_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会议记录主题表';


CREATE TABLE IF NOT EXISTS `{$prefix}minutes_attachment{$suffix}` (
  `miat_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `mi_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会议记录id',
  `mip_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论ID，如果为0则是会议记录附件，否则为对应评论附件',
  `at_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件id',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名称',
  `miat_status` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常; 2=更新; 99=已删除',
  `miat_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `miat_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `miat_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`miat_id`),
  KEY `miat_status` (`miat_status`),
  KEY `mi_id` (`mi_id`),
  KEY `mip_id` (`mip_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会议记录的附件表';


CREATE TABLE IF NOT EXISTS `{$prefix}minutes_draft{$suffix}` (
  `mid_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_openid` varchar(32) NOT NULL COMMENT '用户名称',
  `mid_subject` varchar(81) NOT NULL COMMENT '主题',
  `mid_message` text NOT NULL COMMENT '内容',
  `mid_a_uid` text NOT NULL COMMENT '参会人uid, 以","分隔',
  `mid_cc_uid` text NOT NULL COMMENT '抄送人uid, 以","分隔',
  `mid_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已删除',
  `mid_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mid_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `mid_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`mid_id`),
  KEY `m_uid` (`mid_status`),
  KEY `m_openid` (`m_openid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='会议记录草稿表';

CREATE TABLE IF NOT EXISTS `{$prefix}minutes_mem{$suffix}` (
  `mim_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `mi_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会议记录ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID, 为 0 时, 全体成员可看',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名',
  `mim_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=抄送人，4=已删除',
  `mim_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mim_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `mim_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`mim_id`),
  KEY `mim_id` (`mim_id`,`mim_status`),
  KEY `m_uid` (`m_uid`,`mim_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='可查看会议记录的人员列表';

CREATE TABLE IF NOT EXISTS `{$prefix}minutes_post{$suffix}` (
  `mip_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名称',
  `mi_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `mip_subject` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `mip_message` text NOT NULL COMMENT '内容',
  `mip_first` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否主题，0=不是，1=是',
  `mip_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `mip_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mip_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `mip_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`mip_id`),
  KEY `st_id` (`mi_id`,`mip_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会议记录评论/回复信息';

CREATE TABLE IF NOT EXISTS `{$prefix}minutes_setting{$suffix}` (
  `mis_key` varchar(50) NOT NULL COMMENT '变量名',
  `mis_value` text NOT NULL COMMENT '值',
  `mis_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `mis_comment` text NOT NULL COMMENT '说明',
  `mis_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `mis_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mis_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `mis_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`mis_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会议记录设置表';

