CREATE TABLE IF NOT EXISTS `{$prefix}campaign{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL COMMENT '发起人UID',
  `username` varchar(54) NOT NULL COMMENT '发起人用户名',
  `typeid` int(10) unsigned NOT NULL COMMENT '分类',
  `subject` varchar(255) NOT NULL COMMENT '主题',
  `cover` int(10) unsigned DEFAULT NULL COMMENT '封面id',
  `content` text NOT NULL COMMENT '内容',
  `begintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动开始时间',
  `overtime` int(10) unsigned NOT NULL COMMENT '报名截止时间',
  `is_push` tinyint(1) unsigned DEFAULT '1' COMMENT '1=发布 0=草稿',
  `is_custom` tinyint(1) unsigned DEFAULT '1' COMMENT '是否可自定义字段',
  `needsign` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要报名, 0: 不需要; 1: 需要',
  `is_all` tinyint(1) DEFAULT '1' COMMENT '是否全部可见',
  `status` tinyint(3) unsigned DEFAULT '0' COMMENT '记录状态 1初始化，2=已更新, 3=已取消，4=已删除',
  `created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updated` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `typeid` (`typeid`),
  KEY `overtime` (`overtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动表';


CREATE TABLE IF NOT EXISTS `{$prefix}campaign_custom{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `saleid` int(10) unsigned NOT NULL COMMENT '销售id(member表)',
  `actid` int(10) unsigned NOT NULL COMMENT '活动id',
  `custom` varchar(255) DEFAULT '[]' COMMENT '自定义报名字段',
  `status` tinyint(3) unsigned DEFAULT '0' COMMENT '记录状态 1初始化，2=已更新, 3=已取消，4=已删除',
  `created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updated` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `saleid` (`saleid`,`actid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='自定义报名字段';


CREATE TABLE IF NOT EXISTS `{$prefix}campaign_customer{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(10) NOT NULL COMMENT '姓名',
  `mobile` char(20) NOT NULL COMMENT '手机',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已取消，4=已删除',
  `created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updated` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='客户表';


CREATE TABLE IF NOT EXISTS `{$prefix}campaign_reg{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `saleid` int(10) unsigned NOT NULL COMMENT '销售id',
  `actid` int(10) unsigned NOT NULL COMMENT '活动id',
  `customerid` int(10) unsigned NOT NULL COMMENT '客户id',
  `name` char(10) NOT NULL COMMENT '姓名',
  `mobile` char(20) NOT NULL COMMENT '手机',
  `custom` text COMMENT '扩展字段(json)',
  `is_sign` tinyint(1) unsigned DEFAULT '0' COMMENT '是否签到',
  `signtime` int(10) unsigned DEFAULT NULL COMMENT '签到时间',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已取消，4=已删除',
  `created` int(10) unsigned NOT NULL COMMENT '报名时间',
  `updated` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `saleid` (`saleid`,`actid`,`customerid`),
  KEY `signtime` (`signtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报名记录';


CREATE TABLE IF NOT EXISTS `{$prefix}campaign_right{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `depid` int(10) unsigned NOT NULL COMMENT '部门id',
  `actid` int(10) unsigned NOT NULL COMMENT '活动id',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已取消，4=已删除',
  `created` int(10) unsigned NOT NULL COMMENT '报名时间',
  `updated` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dep_id` (`depid`,`actid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动,部门关系表';


CREATE TABLE IF NOT EXISTS `{$prefix}campaign_setting{$suffix}` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL COMMENT '缓存类型, 0:非数组, 1:数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会议设置表';


CREATE TABLE IF NOT EXISTS `{$prefix}campaign_share{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `saleid` int(10) unsigned NOT NULL COMMENT '销售id',
  `sharetime` int(10) unsigned DEFAULT NULL COMMENT '分享时间',
  `actid` int(10) unsigned NOT NULL COMMENT '活动id',
  `date` date DEFAULT NULL COMMENT '分享日期',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已取消，4=已删除',
  `created` int(10) unsigned NOT NULL COMMENT '报名时间',
  `updated` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `saleid` (`saleid`,`sharetime`),
  KEY `saleid_2` (`saleid`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分享记录表';


CREATE TABLE IF NOT EXISTS `{$prefix}campaign_total{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `actid` int(10) unsigned NOT NULL COMMENT '活动id',
  `saleid` int(10) unsigned NOT NULL COMMENT '销售id(member表)',
  `typeid` int(10) unsigned NOT NULL COMMENT '活动分类id',
  `date` date NOT NULL COMMENT '日期',
  `share` int(10) unsigned DEFAULT '0' COMMENT '分享次数',
  `hits` int(10) unsigned DEFAULT '0' COMMENT '阅读次数',
  `regs` int(10) unsigned DEFAULT '0' COMMENT '报名人数',
  `signs` int(10) unsigned DEFAULT '0' COMMENT '签到人数',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已取消，4=已删除',
  `created` int(10) unsigned NOT NULL COMMENT '报名时间',
  `updated` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `actid` (`actid`,`saleid`,`date`),
  KEY `typeid` (`typeid`),
  KEY `saleid` (`saleid`,`date`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='销售业绩表(包含自定义报名字段配置)';


CREATE TABLE IF NOT EXISTS `{$prefix}campaign_type{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_sort` tinyint(3) unsigned DEFAULT '50' COMMENT '顺序(0-100)',
  `title` char(20) NOT NULL COMMENT '分类标题',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已取消，4=已删除',
  `created` int(10) unsigned NOT NULL COMMENT '报名时间',
  `updated` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动分类';
