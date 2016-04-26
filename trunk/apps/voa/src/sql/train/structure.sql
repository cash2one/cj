CREATE TABLE IF NOT EXISTS `{$prefix}train_article{$suffix}` (
  `ta_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章ID',
  `ca_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '后台管理员ID',
  `tc_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章目录ID',
  `title` char(80) NOT NULL DEFAULT '' COMMENT '文章标题',
  `author` char(40) NOT NULL DEFAULT '' COMMENT '文章作者',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ta_id`),
  KEY `m_uid` (`ca_id`,`status`),
  KEY `tc_id` (`tc_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章表';

CREATE TABLE IF NOT EXISTS `{$prefix}train_article_content{$suffix}` (
  `tac_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ta_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章ID',
  `content` mediumtext NOT NULL COMMENT '文章内容',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tac_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章内容表';

CREATE TABLE IF NOT EXISTS `{$prefix}train_article_member{$suffix}` (
  `tam_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ta_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `read_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '阅读时间',
  `ip_address` char(15) NOT NULL DEFAULT '0.0.0.0' COMMENT 'IP地址',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tam_id`),
  KEY `ta_id` (`ta_id`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户文章阅读情况表';

CREATE TABLE IF NOT EXISTS `{$prefix}train_article_right{$suffix}` (
  `tar_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章权限ID',
  `ta_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章ID',
  `tc_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章目录ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `cd_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '部门ID',
  `is_all` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否全部人员可查看：0,不可；1,可',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tar_id`),
  KEY `ta_id` (`ta_id`),
  KEY `tc_id` (`tc_id`),
  KEY `m_uid` (`m_uid`),
  KEY `cd_id` (`cd_id`),
  KEY `tc_id_2` (`tc_id`,`m_uid`,`cd_id`,`is_all`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章查看权限表';

CREATE TABLE IF NOT EXISTS `{$prefix}train_article_search{$suffix}` (
  `tas_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ta_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章ID',
  `tc_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章目录ID',
  `title` char(80) NOT NULL DEFAULT '' COMMENT '文章标题',
  `content` mediumtext NOT NULL COMMENT '纯文本：文章标题+内容',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tas_id`),
  KEY `ta_id` (`ta_id`,`status`),
  KEY `tc_id` (`tc_id`,`status`),
  KEY `title` (`title`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章搜索表';

CREATE TABLE IF NOT EXISTS `{$prefix}train_category{$suffix}` (
  `tc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '目录ID',
  `title` char(30) NOT NULL DEFAULT '' COMMENT '目录标题',
  `article_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章数量',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章目录表';

CREATE TABLE IF NOT EXISTS `{$prefix}train_category_right{$suffix}` (
  `tcr_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '目录权限ID',
  `tc_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '目录ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `cd_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '部门ID',
  `is_all` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否全部人员可查看：0，不可；1，可',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tcr_id`),
  KEY `tc_id` (`tc_id`),
  KEY `m_uid` (`m_uid`),
  KEY `cd_id` (`cd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章查看权限表';

CREATE TABLE IF NOT EXISTS `{$prefix}train_setting{$suffix}` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型:0=非数组; 1=数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态:1=新创建; 2=已更新; 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='培训 - 设置表';
