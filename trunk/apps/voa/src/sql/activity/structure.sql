CREATE TABLE IF NOT EXISTS `{$prefix}activity{$suffix}` (
  `acid` int(10) NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `title` varchar(30) NOT NULL DEFAULT '' COMMENT '活动主题',
  `content` text NOT NULL COMMENT '活动内容',
  `address` varchar(100) NOT NULL DEFAULT '' COMMENT '活动地点',
  `np` int(5) NOT NULL COMMENT '活动限制人数',
  `at_ids` varchar(50) NOT NULL DEFAULT '' COMMENT '图片：以逗号分隔。',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动发起用户UID',
  `uname` varchar(20) NOT NULL DEFAULT '' COMMENT '发起人名字',
  `start_time` int(10) NOT NULL DEFAULT '0' COMMENT '活动开始时间',
  `end_time` int(10) NOT NULL DEFAULT '0' COMMENT '活动结束时间',
  `cut_off_time` int(10) NOT NULL DEFAULT '0' COMMENT '活动报名截止时间',
  `outsider` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否允许外部人员参与，0.不允许；1.允许',
  `outfield` text NOT NULL COMMENT '序列化外部人员需要填写的列表项',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`acid`),
  KEY `idx_time` (`start_time`,`end_time`,`cut_off_time`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}activity_invite{$suffix}` (
  `aiid` int(10) NOT NULL AUTO_INCREMENT COMMENT '活动邀请表（与活动主表关联）',
  `primary_id` int(10) NOT NULL COMMENT '参与部门或者人员的主键',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态值：1=部门，2=人员',
  `acid` int(10) NOT NULL COMMENT '活动表主键',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`aiid`),
  KEY `idx_ac_id` (`acid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}activity_nopartake{$suffix}` (
  `anpid` int(10) NOT NULL AUTO_INCREMENT COMMENT '活动取消参与内容表',
  `apid` int(10) NOT NULL COMMENT '活动参与人员表主键',
  `apply` text NOT NULL COMMENT '申请取消报名理由',
  `reject` text NOT NULL COMMENT '驳回取消报名理由',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`anpid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}activity_partake{$suffix}` (
  `apid` int(10) NOT NULL AUTO_INCREMENT COMMENT '活动参与人员表主键',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `name` varchar(20) NOT NULL COMMENT '参与人员名称',
  `acid` int(10) NOT NULL COMMENT '活动ID',
  `type` tinyint(3) NOT NULL COMMENT '记录状态, 1=参与，2=申请取消，3=同意取消',
  `check` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否签到, 0=未签到， 1=已签到',
  `remark` varchar(64) NOT NULL DEFAULT '' COMMENT '人员报名备注',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`apid`),
  KEY `idx_m_uid` (`m_uid`,`acid`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}activity_setting{$suffix}` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='审批设置表';

CREATE TABLE IF NOT EXISTS `{$prefix}activity_outsider{$suffix}` (
  `oapid` int(10) NOT NULL AUTO_INCREMENT COMMENT '活动外部参与人员表主键',
  `acid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动ID',
  `outname` varchar(10) NOT NULL DEFAULT '' COMMENT '外部参与人员名称',
  `outphone` varchar(11) NOT NULL DEFAULT '' COMMENT '外部参与人员手机',
  `captcha` int(6) unsigned NOT NULL DEFAULT '0' COMMENT '手机验证码',
  `check` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否签到, 0=未签到， 1=已签到',
  `remark` varchar(64) NOT NULL DEFAULT '' COMMENT '备注',
  `other` text NOT NULL COMMENT '其它信息',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`oapid`),
  KEY `outphone` (`outphone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动外部参与人员表';