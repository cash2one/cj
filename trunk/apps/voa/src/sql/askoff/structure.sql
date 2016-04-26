CREATE TABLE IF NOT EXISTS `{$prefix}askoff{$suffix}` (
  `ao_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `ao_subject` varchar(81) NOT NULL COMMENT '请假主题',
  `ao_type` varchar(16) NOT NULL COMMENT '请假分类',
  `ao_begintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '请假开始时间',
  `ao_endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '请假结束时间',
  `aopc_id` int(11) NOT NULL COMMENT '当前进度ID',
  `ao_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=审批申请中，2=审批通过, 3=转审批, 4=审批不通过, 5=草稿，6=已删除',
  `ao_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `ao_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `ao_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`ao_id`),
  KEY `m_uid` (`m_uid`,`ao_status`),
  KEY `aopc_id` (`aopc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='请假申请表';


CREATE TABLE IF NOT EXISTS `{$prefix}askoff_attachment{$suffix}` (
  `aoat_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `ao_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '请假id',
  `aopt_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论id，如果为0则是请假主题附件，否则为对应评论附件',
  `at_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件id',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名称',
  `aoat_status` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常; 2=更新; 99=已删除',
  `aoat_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `aoat_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `aoat_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`aoat_id`),
  KEY `aoat_status` (`aoat_status`),
  KEY `ao_id` (`ao_id`),
  KEY `aopt_id` (`aopt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='请假的附件表';


CREATE TABLE IF NOT EXISTS `{$prefix}askoff_draft{$suffix}` (
  `aod_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_openid` varchar(32) NOT NULL COMMENT '用户名称',
  `aod_subject` varchar(81) NOT NULL COMMENT '主题',
  `aod_message` text NOT NULL COMMENT '内容',
  `aod_a_uid` int(10) unsigned NOT NULL COMMENT '审批人uid',
  `aod_cc_uid` text NOT NULL COMMENT '抄送人uid, 以","分隔',
  `aod_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已删除',
  `aod_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `aod_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `aod_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`aod_id`),
  KEY `m_uid` (`aod_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='请假草稿表';


CREATE TABLE IF NOT EXISTS `{$prefix}askoff_post{$suffix}` (
  `aopt_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ao_id` int(10) unsigned NOT NULL COMMENT '请假id',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `aopt_subject` varchar(81) NOT NULL COMMENT '主题',
  `aopt_message` text NOT NULL COMMENT '内容',
  `aopt_first` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否主题，0=不是，1=是',
  `aopt_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=已删除',
  `aopt_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `aopt_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `aopt_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`aopt_id`),
  KEY `m_uid` (`m_uid`,`aopt_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='请假回复信息表';


CREATE TABLE IF NOT EXISTS `{$prefix}askoff_proc{$suffix}` (
  `aopc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ao_id` int(10) unsigned NOT NULL COMMENT '请假ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `aopc_remark` varchar(255) NOT NULL COMMENT '备注进度',
  `aopc_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=审核中，2=审核通过, 3=转审批, 4=审核不通过, 5=抄送，6=已删除',
  `aopc_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `aopc_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `aopc_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`aopc_id`),
  KEY `ao_id` (`ao_id`,`aopc_status`),
  KEY `m_uid` (`m_uid`,`aopc_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='审批进度表';


CREATE TABLE IF NOT EXISTS `{$prefix}askoff_setting{$suffix}` (
  `aos_key` varchar(50) NOT NULL COMMENT '变量名',
  `aos_value` text NOT NULL COMMENT '值',
  `aos_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `aos_comment` text NOT NULL COMMENT '说明',
  `aos_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `aos_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `aos_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `aos_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`aos_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='审批设置表';

