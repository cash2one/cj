CREATE TABLE IF NOT EXISTS `{$prefix}superreport_attachment{$suffix}` (
  `sa_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `dr_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '报表ID',
  `at_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件ID',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`sa_id`),
  KEY `s_id` (`dr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='超级报表-附件表';


CREATE TABLE IF NOT EXISTS `{$prefix}superreport_comment{$suffix}` (
  `sc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `dr_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '报表ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `comment` text NOT NULL COMMENT '评论内容',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`sc_id`),
  KEY `s_id` (`dr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='超级报表-评论表';


CREATE TABLE IF NOT EXISTS `{$prefix}superreport_detail{$suffix}` (
  `sd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `dr_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '报表ID',
  `csp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '门店ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '汇报人ID',
  `cdate` date NOT NULL DEFAULT '0000-00-00' COMMENT '报表日期',
  `area` text NOT NULL COMMENT '存储区域信息，系列化数组',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`sd_id`),
  KEY `s_id` (`dr_id`),
  KEY `csp_id` (`csp_id`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报表详情表';


CREATE TABLE IF NOT EXISTS `{$prefix}superreport_monthlyreport{$suffix}` (
  `sm_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `csp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '门店ID',
  `year` year(4) NOT NULL DEFAULT '0000' COMMENT '年份',
  `month` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '月份',
  `fieldname` char(20) NOT NULL DEFAULT '' COMMENT '字段名',
  `fieldvalue` float NOT NULL DEFAULT '0' COMMENT '字段值',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`sm_id`),
  KEY `sp_id` (`csp_id`),
  KEY `year` (`year`),
  KEY `month` (`month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='超级报表-月报表';


CREATE TABLE IF NOT EXISTS `{$prefix}superreport_setting{$suffix}` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型:0=非数组; 1=数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态:1=新创建; 2=已更新; 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='超级报表 - 设置表';


CREATE TABLE IF NOT EXISTS `{$prefix}superreport_template{$suffix}` (
  `st_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `stc_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模板类别ID',
  `title` char(50) NOT NULL DEFAULT '' COMMENT '模板名',
  `content` text NOT NULL COMMENT '模板内容（系列化数组）',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`st_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='模板相关表';


CREATE TABLE IF NOT EXISTS `{$prefix}superreport_template_category{$suffix}` (
  `stc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(20) NOT NULL DEFAULT '',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`stc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报表模板分类表';
