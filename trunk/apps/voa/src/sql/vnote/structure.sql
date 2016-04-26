CREATE TABLE IF NOT EXISTS `{$prefix}vnote{$suffix}` (
  `vn_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `vn_subject` varchar(81) NOT NULL COMMENT '备忘主题',
  `vn_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已更新, 3=已删除',
  `vn_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `vn_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `vn_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`vn_id`),
  KEY `m_uid` (`m_uid`,`vn_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='备忘主题表';


CREATE TABLE IF NOT EXISTS `{$prefix}vnote_draft{$suffix}` (
  `vnd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_openid` varchar(32) NOT NULL COMMENT '用户名称',
  `vnd_subject` varchar(81) NOT NULL COMMENT '主题',
  `vnd_message` text NOT NULL COMMENT '内容',
  `vnd_cc_uid` text NOT NULL COMMENT '抄送人uid, 以","分隔',
  `vnd_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已删除',
  `vnd_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `vnd_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `vnd_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`vnd_id`),
  KEY `m_uid` (`vnd_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='备忘录草稿表';


CREATE TABLE IF NOT EXISTS `{$prefix}vnote_mem{$suffix}` (
  `vnm_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `vn_id` int(10) unsigned NOT NULL COMMENT '备忘ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID, 为 0 时, 全体成员可看',
  `m_username` varchar(54) NOT NULL COMMENT '用户名',
  `vnm_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=抄送人，4=已删除',
  `vnm_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `vnm_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `vnm_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`vnm_id`),
  KEY `vnm_id` (`vnm_id`,`vnm_status`),
  KEY `m_uid` (`m_uid`,`vnm_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='可查看备忘人员列表';


CREATE TABLE IF NOT EXISTS `{$prefix}vnote_post{$suffix}` (
  `vnp_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `vn_id` int(10) unsigned NOT NULL COMMENT '备忘ID',
  `vnp_subject` varchar(255) NOT NULL COMMENT '标题',
  `vnp_message` text NOT NULL COMMENT '内容',
  `vnp_first` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否主题，0=不是，1=是',
  `vnp_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `vnp_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `vnp_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `vnp_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`vnp_id`),
  KEY `st_id` (`vn_id`,`vnp_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='备忘评论/回复信息';


CREATE TABLE IF NOT EXISTS `{$prefix}vnote_setting{$suffix}` (
  `vns_key` varchar(50) NOT NULL COMMENT '变量名',
  `vns_value` text NOT NULL COMMENT '值',
  `vns_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `vns_comment` text NOT NULL COMMENT '说明',
  `vns_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `vns_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `vns_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `vns_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`vns_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='备忘设置表';