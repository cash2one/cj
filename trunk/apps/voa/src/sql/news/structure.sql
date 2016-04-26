CREATE TABLE IF NOT EXISTS `{$prefix}news{$suffix}` (
  `ne_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nca_id` int(11) unsigned NOT NULL DEFAULT '0',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `title` varchar(64) NOT NULL DEFAULT '',
  `summary` varchar(120) NOT NULL DEFAULT '' COMMENT '摘要',
  `cover_id` int(11) NOT NULL DEFAULT '0',
  `read_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '已阅读人数',
  `is_secret` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否保密：0=不保密；1=保密',
  `is_comment` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否评论：0=不评论；1=评论',
  `is_publish` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否发布：0=草稿；1=发布',
  `is_all` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否全部人员可见：0=不是；1=是',
  `is_check` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否审批:0-无审批  1-有审批',
  `send_no_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未读人员发送时间',
  `check_summary` varchar(120) NOT NULL DEFAULT '' COMMENT '预览说明',
  `num_like` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞次数',
  `is_like` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否点赞：0=不点赞；1=点赞',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `published` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
  `is_all2` tinyint(1) NOT NULL DEFAULT '0' COMMENT '评论权限',
  `multiple` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '多条时间戳',
  `is_message` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否推送消息',
  PRIMARY KEY (`ne_id`),
  KEY `nca_id` (`nca_id`),
  KEY `m_uid` (`m_uid`),
  KEY `is_publish` (`is_publish`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='新闻公告表';

CREATE TABLE IF NOT EXISTS `{$prefix}news_category{$suffix}` (
  `nca_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT '' COMMENT '类型名',
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '父类型ID',
  `orderid` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '排序',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`nca_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='新闻公告分类表';

CREATE TABLE IF NOT EXISTS `{$prefix}news_comment{$suffix}` (
  `ncomm_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ne_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新闻公告ID',
  `m_uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '人员ID',
  `p_username` varchar(100) NOT NULL DEFAULT '' COMMENT '父级姓名',
  `m_username` varchar(100) NOT NULL DEFAULT '' COMMENT '回复者姓名',
  `content` text COMMENT '评论内容',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ncomm_id`),
  KEY `ne_id` (`ne_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='新闻公告评论表';

CREATE TABLE IF NOT EXISTS `{$prefix}news_content{$suffix}` (
  `nco_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ne_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '公告ID',
  `content` text COMMENT '新闻公告内容',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`nco_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='新闻公告内容表';

CREATE TABLE IF NOT EXISTS `{$prefix}news_read{$suffix}` (
  `nre_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ne_id` int(11) unsigned NOT NULL DEFAULT '0',
  `m_uid` int(11) unsigned NOT NULL DEFAULT '0',
  `m_username` varchar(30) NOT NULL DEFAULT '',
  `department` varchar(30) NOT NULL DEFAULT '',
  `job` varchar(30) NOT NULL DEFAULT '',
  `mobilephone` char(11) NOT NULL DEFAULT '',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`nre_id`),
  KEY `ne_id` (`ne_id`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='新闻公告已读表';

CREATE TABLE IF NOT EXISTS `{$prefix}news_right{$suffix}` (
  `nri_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ne_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新闻公告ID',
  `nca_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新闻公告类别ID',
  `is_all` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否全部人员可查看：0=不是；1=是',
  `m_uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '人员Id',
  `cd_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '部门ID',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`nri_id`),
  KEY `ne_id` (`ne_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='新闻公告权限表';

CREATE TABLE IF NOT EXISTS `{$prefix}news_setting{$suffix}` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型:0=非数组; 1=数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态:1=新创建; 2=已更新; 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='新闻公告 - 设置表';

CREATE TABLE IF NOT EXISTS `{$prefix}news_check{$suffix}` (
  `nec_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `news_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新闻id',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核人id',
  `is_check` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '审核状态:1-审核中 2-审核通过 3-未通过',
  `check_note` varchar(140) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '理由',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态:1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`nec_id`),
  KEY `news_id` (`news_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  COMMENT='新闻公告审核表';

CREATE TABLE IF NOT EXISTS `{$prefix}news_like{$suffix}` (
  `like_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞用户uid',
  `description` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '点赞操作;1=> 次数-1,  2=> 次数+1，默认2',
  `ne_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新闻公告ID',
  `ip` varchar(150) NOT NULL DEFAULT '0.0.0.0' COMMENT 'ip地址',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`like_id`),
  KEY `ne_id_muid` (`ne_id`,`m_uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='新闻公告点赞表';
