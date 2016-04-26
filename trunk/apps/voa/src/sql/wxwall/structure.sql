CREATE TABLE IF NOT EXISTS `{$prefix}wxwall{$suffix}` (
  `ww_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请人UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '申请人名称',
  `ww_subject` varchar(81) NOT NULL DEFAULT '' COMMENT '微信墙主题',
  `ww_message` text NOT NULL COMMENT '微信墙详情',
  `ww_begintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `ww_endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `ww_admin` varchar(16) NOT NULL DEFAULT '' COMMENT '管理员帐号',
  `ww_passwd` varchar(32) NOT NULL DEFAULT '' COMMENT '管理密码',
  `ww_salt` varchar(16) NOT NULL DEFAULT '' COMMENT '干扰码',
  `ww_isopen` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放, 0: 关闭; 1: 开放',
  `ww_postverify` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要验证, 0: 不需要验证; 1: 需要验证',
  `ww_maxpost` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '每个用户的最大回复数, 为 0 则不限制',
  `ww_sceneid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '微信墙临时二维码场景ID',
  `ww_qrcodeexpire` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '微信墙二维码过期时间',
  `ww_qrcodeurl` varchar(255) NOT NULL DEFAULT '' COMMENT '微信墙二维码地址url',
  `ww_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=申请中，2=已审核, 3=已拒绝, 4=已删除',
  `ww_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ww_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ww_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ww_id`),
  KEY `m_uid` (`m_uid`,`ww_status`),
  KEY `ww_admin` (`ww_admin`),
  KEY `ww_sceneid` (`ww_sceneid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信墙主题表';


CREATE TABLE IF NOT EXISTS `{$prefix}wxwall_online{$suffix}` (
  `wwo_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ww_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '微信墙ID',
  `m_openid` char(32) NOT NULL DEFAULT '' COMMENT '用户的微信openid',
  `wwo_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新, 4=已删除',
  `wwo_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `wwo_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `wwo_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`wwo_id`),
  KEY `ww_id` (`ww_id`),
  KEY `m_openid` (`m_openid`),
  KEY `wwo_status` (`wwo_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信墙在线用户表';


CREATE TABLE IF NOT EXISTS `{$prefix}wxwall_post{$suffix}` (
  `wwp_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名称',
  `ww_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `wwp_message` text NOT NULL COMMENT '内容',
  `wwp_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=申请中，2=已通过，3=已拒绝, 4=已删除',
  `wwp_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `wwp_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `wwp_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`wwp_id`),
  KEY `ww_id` (`ww_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信墙回复信息';


CREATE TABLE IF NOT EXISTS `{$prefix}wxwall_setting{$suffix}` (
  `wws_key` varchar(50) NOT NULL COMMENT '变量名',
  `wws_value` text NOT NULL COMMENT '值',
  `wws_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `wws_comment` text NOT NULL COMMENT '说明',
  `wws_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `wws_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `wws_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `wws_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`wws_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信墙设置表';
