CREATE TABLE IF NOT EXISTS `{$prefix}questionnaire{$suffix}` (
  `qu_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `share_id` char(32) NOT NULL DEFAULT '' COMMENT '分享ID',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `body` text NOT NULL COMMENT '描述',
  `qc_id` int(10) NOT NULL DEFAULT '0' COMMENT '分类ID 默认:0(未分类)',
  `deadline` int(10) NOT NULL DEFAULT '0' COMMENT '截止时间',
  `share` tinyint(3) NOT NULL DEFAULT '1' COMMENT '是否可分享,1:可分享;2:不可分享',
  `is_all` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否所有人可见:-1',
  `anonymous` tinyint(3) NOT NULL DEFAULT '1' COMMENT '匿名:1:匿名;2:实名',
  `repeat` tinyint(3) NOT NULL DEFAULT '1' COMMENT '是否允许重复:1:允许;2:不允许',
  `remind` tinyint(4) NOT NULL DEFAULT '0' COMMENT '问卷结束前多少分钟进行消息提醒',
  `release` int(10) NOT NULL DEFAULT '0' COMMENT '定时发布时间',
  `release_time` int(10) NOT NULL DEFAULT '0' COMMENT '发布时间',
  `release_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '问卷发布状态:1.预发布;2.草稿;3.发布;',
  `field` text NOT NULL COMMENT '题目设置',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`qu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='问卷表';

CREATE TABLE IF NOT EXISTS `{$prefix}questionnaire_classify{$suffix}` (
  `qc_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '分类名称',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`qc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='问卷分类表';

CREATE TABLE IF NOT EXISTS `{$prefix}questionnaire_record{$suffix}` (
  `qr_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `qu_id` int(10) NOT NULL COMMENT '问卷ID',
  `answer` text NOT NULL COMMENT '回答',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '回答人姓名',
  `uid` int(10) NOT NULL DEFAULT '0' COMMENT '填写人员ID',
  `openid` char(64) NOT NULL DEFAULT '' COMMENT '外部人员openid',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`qr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='问卷回答表';

CREATE TABLE IF NOT EXISTS `{$prefix}questionnaire_setting{$suffix}` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='问卷设置表';

CREATE TABLE IF NOT EXISTS `{$prefix}questionnaire_viewrange{$suffix}` (
  `qv_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `qu_id` int(10) NOT NULL DEFAULT '0' COMMENT '问卷ID',
  `view_range_uid` int(10) NOT NULL DEFAULT '0' COMMENT '可见范围人员',
  `view_range_cdid` int(10) NOT NULL DEFAULT '0' COMMENT '可见范围部门',
  `view_range_label` int(10) NOT NULL DEFAULT '0' COMMENT '可见范围标签',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`qv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='问卷可见范围表';