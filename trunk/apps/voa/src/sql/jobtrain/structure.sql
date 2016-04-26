CREATE TABLE IF NOT EXISTS `{$prefix}jobtrain_category{$suffix}` (
  `id` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID,内容分类ID',
  `title` varchar(30) DEFAULT '' COMMENT '类型名',
  `pid` smallint(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父类型ID',
  `orderid` smallint(6) UNSIGNED NOT NULL DEFAULT '1' COMMENT '排序',
  `is_open` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否启用, 1=启用，0=不启用',
  `is_all` tinyint(1) UNSIGNED NOT NULL COMMENT '是否全部用户',
  `cd_ids` varchar(255) NOT NULL COMMENT '部门ids',
  `m_uids` varchar(255) NOT NULL COMMENT '用户ids',
  `article_num` smallint(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '统计文章数',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='培训 - 文章分类表';

CREATE TABLE IF NOT EXISTS `{$prefix}jobtrain_article{$suffix}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID,内容ID',
  `cid` smallint(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类ID',
  `type` tinyint(2) UNSIGNED NOT NULL DEFAULT '0' COMMENT '类型0=文章，1=音图，2=视频',
  `title` varchar(200) DEFAULT '' COMMENT '标题',
  `author` char(54) DEFAULT '' COMMENT '作者',
  `m_uid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `m_username` char(54) NOT NULL DEFAULT '' COMMENT '创建人用户名',
  `summary` varchar(255) NOT NULL DEFAULT '' COMMENT '摘要',
  `preview_summary` varchar(255) NOT NULL DEFAULT '' COMMENT '预览说明',
  `content` text NOT NULL COMMENT '内容',
  `attachments` text NOT NULL COMMENT '附件：音图和附件序列化数组',
  `video_id` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '视频id',
  `is_secret` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否保密：0=不保密；1=保密',
  `cover_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '封面图片ID',
  `is_comment` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否评论：0=不评论；1=评论',
  `is_publish` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否发布：0=草稿；1=发布',
  `publish_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发布时间',
  `is_loop` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否循环播放音图：0=否；1=是',
  `study_num` smallint(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '学习人数',
  `study_sum` smallint(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '学习总人数',
  `coll_num` smallint(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '收藏人数',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`),
  KEY `type` (`type`),
  KEY `is_publish` (`is_publish`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='培训 - 文章表';


CREATE TABLE IF NOT EXISTS `{$prefix}jobtrain_study{$suffix}` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `aid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '文章ID',
  `m_uid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `m_username` char(54) NOT NULL DEFAULT '' COMMENT '用户名',
  `department` varchar(64) NOT NULL DEFAULT '' COMMENT '部门',
  `job` varchar(64) NOT NULL DEFAULT '' COMMENT '职务',
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `study_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '学习时间',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='培训 - 学习表';

CREATE TABLE IF NOT EXISTS `{$prefix}jobtrain_right{$suffix}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `aid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '内容ID',
  `cid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '内容类别ID',
  `is_all` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否全部人员可查看：0=不是；1=是',
  `m_uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '人员Id',
  `cd_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '部门ID',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`),
  KEY `cid` (`cid`),
  KEY `is_all` (`is_all`),
  KEY `m_uid` (`m_uid`),
  KEY `cd_id` (`cd_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='培训 - 权限表';

CREATE TABLE IF NOT EXISTS `{$prefix}jobtrain_coll{$suffix}` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `aid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '文章ID',
  `m_uid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `m_username` char(54) NOT NULL DEFAULT '' COMMENT '用户名',
  `department` varchar(64) NOT NULL DEFAULT '' COMMENT '部门',
  `job` varchar(64) NOT NULL DEFAULT '' COMMENT '职务',
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='培训 - 收藏表';

CREATE TABLE IF NOT EXISTS `{$prefix}jobtrain_comment{$suffix}` (
  `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID,评论ID' ,
  `aid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复的文章主题ID',
  `toid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复id',
  `m_uid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `m_username` char(54) NOT NULL COMMENT '用户名称' ,
  `to_uid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复用户ID',
  `to_username` char(54) NOT NULL COMMENT '回复用户名' ,
  `content` text COMMENT '评论内容',
  `zan_num` smallint(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞数',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`),
  KEY `toid` (`toid`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='培训 - 评论表';

CREATE TABLE IF NOT EXISTS `{$prefix}jobtrain_comment_zan{$suffix}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `m_uid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞用户uid',
  `comment_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '培训评论ID',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `comment_id` (`comment_id`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='培训 - 评论点赞表';

CREATE TABLE IF NOT EXISTS `{$prefix}jobtrain_setting{$suffix}` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '缓存类型:0=非数组; 1=数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '数据状态:1=新创建; 2=已更新; 3=已删除',
  `created` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='培训 - 设置表';
