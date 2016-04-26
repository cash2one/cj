CREATE TABLE IF NOT EXISTS `{$prefix}thread{$suffix}` (
`tid`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID' ,
`uid`  int(10) UNSIGNED NOT NULL COMMENT '用户UID' ,
`username`  varchar(54) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名' ,
`subject`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '主题' ,
`friend`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分享状态, 0=只自己可见, 1=指定用户可见, 2=分享给所有人' ,
`remindtime`  int(10) UNSIGNED NOT NULL COMMENT '提醒时间' ,
`replies`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT '回复数' ,
`displayorder`  int(10) UNSIGNED NOT NULL COMMENT '排序值, 越大越靠前' ,
`likes`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '点赞数' ,
`attach_id`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`status`  tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '记录状态, 1=初始化，2=已更新，3=已删除' ,
`created`  int(10) UNSIGNED NOT NULL COMMENT '创建时间' ,
`updated`  int(10) UNSIGNED NOT NULL COMMENT '更新时间' ,
`deleted`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间' ,
PRIMARY KEY (`tid`),
INDEX `t_status` (`status`) USING BTREE ,
INDEX `m_uid` (`uid`) USING BTREE ,
INDEX `t_displayorder` (`displayorder`) USING BTREE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='日志/记录主题';

CREATE TABLE IF NOT EXISTS `{$prefix}thread_permit_user{$suffix}` (
  `puid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tid` int(10) unsigned NOT NULL COMMENT '主题ID',
  `uid` int(10) unsigned NOT NULL COMMENT '允许浏览的用户UID, 为 0 时, 则表示分享给所有人',
  `username` varchar(54) NOT NULL COMMENT '用户名称',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`puid`),
  KEY `t_id` (`tid`),
  KEY `m_uid` (`uid`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='允许查看日志/记录的用户表';

CREATE TABLE IF NOT EXISTS `{$prefix}thread_post{$suffix}` (
`pid`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID' ,
`uid`  int(10) UNSIGNED NOT NULL COMMENT '用户UID' ,
`username`  varchar(54) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名称' ,
`tid`  int(10) UNSIGNED NOT NULL COMMENT '主题ID' ,
`ppid`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '回复的目标pid' ,
`p_uid`  int(10) NOT NULL ,
`p_username`  varchar(54) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`subject`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题' ,
`message`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容' ,
`first`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否主题，0=不是，1=是' ,
`replies`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '对评论的回复数' ,
`status`  tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '记录状态, 1=初始化，2=已更新，3=已删除' ,
`created`  int(10) UNSIGNED NOT NULL COMMENT '创建时间' ,
`updated`  int(10) UNSIGNED NOT NULL COMMENT '更新时间' ,
`deleted`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间' ,
PRIMARY KEY (`pid`),
INDEX `t_id` (`tid`, `status`) USING BTREE ,
INDEX `tp_first` (`tid`, `first`) USING BTREE ,
INDEX `ppid` (`ppid`) USING BTREE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='日志/记录详细信息和评论信息';

CREATE TABLE IF NOT EXISTS `{$prefix}thread_post_reply{$suffix}` (
  `prid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `username` varchar(54) NOT NULL COMMENT '用户名称',
  `tid` int(10) unsigned NOT NULL COMMENT '主题ID',
  `pid` int(10) unsigned NOT NULL COMMENT '评论id',
  `subject` varchar(255) NOT NULL COMMENT '标题',
  `message` text NOT NULL COMMENT '内容',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`prid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='对评论的回复信息';

CREATE TABLE IF NOT EXISTS `{$prefix}thread_setting{$suffix}` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='工作台设置表';


CREATE TABLE IF NOT EXISTS `{$prefix}thread_likes{$suffix}` (
`lid`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '点赞id' ,
`tid`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '主题id' ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '点赞用户id' ,
`username`  varchar(54) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '点赞用户' ,
`status`  tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '记录状态, 1=初始化，2=已更新，3=已删除' ,
`created`  int(10) NOT NULL DEFAULT 0 COMMENT '创建时间' ,
`updated`  int(10) NOT NULL DEFAULT 0 COMMENT '更新时间' ,
`deleted`  int(10) NOT NULL DEFAULT 0 COMMENT '删除时间' ,
PRIMARY KEY (`lid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='点赞表';
