CREATE TABLE IF NOT EXISTS `{$prefix}secret{$suffix}` (
  `st_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请人UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '申请人名称',
  `st_subject` varchar(81) NOT NULL DEFAULT '' COMMENT '秘密主题',
  `st_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已更新, 3=已删除',
  `st_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `st_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `st_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`st_id`),
  KEY `m_uid` (`m_uid`,`st_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='秘密主题表';


CREATE TABLE IF NOT EXISTS `{$prefix}secret_post{$suffix}` (
  `stp_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名称',
  `st_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `stp_subject` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `stp_message` text NOT NULL COMMENT '内容',
  `stp_first` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否主题，0=不是，1=是',
  `stp_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `stp_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `stp_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `stp_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`stp_id`),
  KEY `st_id` (`st_id`,`stp_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='密码的回复详细信息和评论信息';


CREATE TABLE IF NOT EXISTS `{$prefix}secret_setting{$suffix}` (
  `sts_key` varchar(50) NOT NULL COMMENT '变量名',
  `sts_value` text NOT NULL COMMENT '值',
  `sts_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `sts_comment` text NOT NULL COMMENT '说明',
  `sts_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `sts_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `sts_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sts_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`sts_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='秘密设置表';

