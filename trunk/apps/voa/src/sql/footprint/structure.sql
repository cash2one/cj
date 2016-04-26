CREATE TABLE IF NOT EXISTS `{$prefix}footprint{$suffix}` (
  `fp_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `fp_subject` varchar(81) NOT NULL COMMENT '客户名称',
  `fp_visittime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '拜访时间',
  `fp_visitweek` tinyint(3) unsigned NOT NULL COMMENT '拜访时间的周序号',
  `fp_visitynd` varchar(10) NOT NULL COMMENT '拜访时间的年/月/日',
  `fp_type` varchar(16) NOT NULL COMMENT '轨迹分类',
  `fp_latitude` float NOT NULL COMMENT '地理位置经度',
  `fp_longitude` float NOT NULL COMMENT '地理位置纬度',
  `fp_precision` float NOT NULL COMMENT '地理位置精度',
  `fp_address` varchar(255) NOT NULL COMMENT '地理位置信息',
  `fp_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已更新, 3=已删除',
  `fp_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `fp_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `fp_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`fp_id`),
  KEY `m_uid` (`m_uid`,`fp_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='销售轨迹表';


CREATE TABLE IF NOT EXISTS `{$prefix}footprint_attachment{$suffix}` (
  `fpat_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `fp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '足迹id',
  `at_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件id',
  `fpat_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '记录状态, 1=正常; 2=已删除;',
  `fpat_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `fpat_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `fpat_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`fpat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='轨迹的附件表';


CREATE TABLE IF NOT EXISTS `{$prefix}footprint_mem{$suffix}` (
  `fpm_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `fp_id` int(10) unsigned NOT NULL COMMENT '轨迹ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `fpm_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常, 2=已更新, 3=抄送人, 4=已删除',
  `fpm_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `fpm_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `fpm_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`fpm_id`),
  KEY `fp_id` (`fp_id`,`fpm_status`),
  KEY `m_uid` (`m_uid`,`fpm_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='轨迹权限表';


CREATE TABLE IF NOT EXISTS `{$prefix}footprint_post{$suffix}` (
  `fppt_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `fp_id` int(10) unsigned NOT NULL COMMENT '轨迹id',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `fppt_subject` varchar(81) NOT NULL COMMENT '主题',
  `fppt_message` text NOT NULL COMMENT '内容',
  `fppt_first` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否主题，0=不是，1=是',
  `fppt_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=已删除',
  `fppt_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `fppt_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `fppt_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`fppt_id`),
  KEY `m_uid` (`m_uid`,`fppt_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='轨迹回复信息表';


CREATE TABLE IF NOT EXISTS `{$prefix}footprint_setting{$suffix}` (
  `fps_key` varchar(50) NOT NULL COMMENT '变量名',
  `fps_value` text NOT NULL COMMENT '值',
  `fps_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `fps_comment` text NOT NULL COMMENT '说明',
  `fps_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `fps_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `fps_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `fps_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`fps_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='轨迹设置表';


CREATE TABLE IF NOT EXISTS `{$prefix}footprint_team{$suffix}` (
  `fpmt_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `fp_id` int(10) unsigned NOT NULL COMMENT '轨迹ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `fpmt_to_uid` int(10) unsigned NOT NULL COMMENT '轨迹接收用户uid',
  `fpmt_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常, 2=已更新, 3=已删除',
  `fpmt_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `fpmt_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `fpmt_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`fpmt_id`),
  KEY `fp_id` (`fp_id`,`fpmt_status`),
  KEY `m_uid` (`m_uid`,`fpmt_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='轨迹小组关系表';

