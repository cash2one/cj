CREATE TABLE IF NOT EXISTS `{$prefix}reimburse{$suffix}` (
  `rb_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `rb_subject` varchar(81) NOT NULL COMMENT '报销主题',
  `rb_type` varchar(16) NOT NULL COMMENT '报销分类',
  `rb_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '报销申请时间',
  `rb_expend` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '花费',
  `rbpc_id` int(10) unsigned NOT NULL COMMENT '当前进度ID',
  `rb_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后处理用户uid',
  `rb_username` varchar(54) NOT NULL COMMENT '最后处理用户名',
  `rb_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=审批申请中，2=转审批, 3=审批通过, 4=审批不通过, 5=已删除',
  `rb_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `rb_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `rb_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`rb_id`),
  KEY `m_uid` (`m_uid`,`rb_status`),
  KEY `rbpc_id` (`rbpc_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='报销申请表';


CREATE TABLE IF NOT EXISTS `{$prefix}reimburse_bill{$suffix}` (
  `rbb_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `rbb_type` varchar(16) NOT NULL COMMENT '账单类型',
  `rbb_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支出时间',
  `rbb_expend` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '金额(单位:分)',
  `rbb_reason` varchar(254) NOT NULL COMMENT '原因',
  `at_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发票图片附件id',
  `rbb_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '记录状态, 1=正常; 2=已使用; 3=已删除;',
  `rbb_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `rbb_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `rbb_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`rbb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报销清单';


CREATE TABLE IF NOT EXISTS `{$prefix}reimburse_bill_attachment{$suffix}` (
  `rbbat_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `rbb_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '清单ID',
  `at_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件id',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名称',
  `rbbat_status` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常; 2=更新; 99=已删除',
  `rbbat_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `rbbat_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `rbbat_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`rbbat_id`),
  KEY `rbbat_status` (`rbbat_status`),
  KEY `rbb_id` (`rbb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报销清单的附件表';


CREATE TABLE IF NOT EXISTS `{$prefix}reimburse_bill_submit{$suffix}` (
  `rbbs_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `rb_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '报销ID',
  `rbb_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '清单id',
  `rbbs_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '记录状态, 1=正常; 2=已更新; 3=已删除;',
  `rbbs_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `rbbs_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `rbbs_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`rbbs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='已提交的报销清单';


CREATE TABLE IF NOT EXISTS `{$prefix}reimburse_draft{$suffix}` (
  `rbd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_openid` varchar(32) NOT NULL COMMENT '用户名称',
  `rbd_subject` varchar(81) NOT NULL COMMENT '主题',
  `rbd_message` text NOT NULL COMMENT '内容',
  `rbd_a_uid` int(10) unsigned NOT NULL COMMENT '审批人uid',
  `rbd_cc_uid` text NOT NULL COMMENT '抄送人uid, 以","分隔',
  `rbd_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已删除',
  `rbd_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `rbd_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `rbd_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`rbd_id`),
  KEY `m_uid` (`rbd_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报销草稿表';


CREATE TABLE IF NOT EXISTS `{$prefix}reimburse_post{$suffix}` (
  `rbpt_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `rb_id` int(10) unsigned NOT NULL COMMENT '报销id',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `rbpt_subject` varchar(81) NOT NULL COMMENT '主题',
  `rbpt_message` text NOT NULL COMMENT '内容',
  `rbpt_first` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否主题，0=不是，1=是',
  `rbpt_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=已删除',
  `rbpt_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `rbpt_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `rbpt_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`rbpt_id`),
  KEY `m_uid` (`m_uid`,`rbpt_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='报销回复信息表';


CREATE TABLE IF NOT EXISTS `{$prefix}reimburse_proc{$suffix}` (
  `rbpc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `rb_id` int(10) unsigned NOT NULL COMMENT '报销ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `rbpc_remark` varchar(255) NOT NULL COMMENT '备注进度',
  `rbpc_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=审批申请中, 2=转审批，3=审核通过, 4=审核不通过, 5=抄送，6=已删除',
  `rbpc_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `rbpc_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `rbpc_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`rbpc_id`),
  KEY `rb_id` (`rb_id`,`rbpc_status`),
  KEY `m_uid` (`m_uid`,`rbpc_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='审批进度表';


CREATE TABLE IF NOT EXISTS `{$prefix}reimburse_setting{$suffix}` (
  `rbs_key` varchar(50) NOT NULL COMMENT '变量名',
  `rbs_value` text NOT NULL COMMENT '值',
  `rbs_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `rbs_comment` text NOT NULL COMMENT '说明',
  `rbs_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `rbs_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `rbs_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `rbs_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`rbs_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报销设置表';
