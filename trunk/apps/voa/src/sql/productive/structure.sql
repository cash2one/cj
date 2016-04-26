CREATE TABLE IF NOT EXISTS `{$prefix}productive{$suffix}` (
  `pt_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `ptt_id` int(10) unsigned NOT NULL COMMENT '活动/产品任务id',
  `sponsor_uid` int(10) unsigned NOT NULL COMMENT '任务发起者uid',
  `m_uid` int(10) NOT NULL COMMENT '用户id',
  `m_username` varchar(54) NOT NULL COMMENT '用户名',
  `pt_lng` decimal(9,6) NOT NULL COMMENT '当前经度',
  `pt_lat` decimal(9,6) NOT NULL COMMENT '当前纬度',
  `csp_id` int(10) unsigned NOT NULL COMMENT '门店id',
  `pt_note` varchar(255) NOT NULL COMMENT '活动/产品备注',
  `pt_status` tinyint(3) unsigned NOT NULL COMMENT '记录状态, 1=待巡, 2=进行中，3=已巡, 4=已删除',
  `pt_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `pt_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `pt_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`pt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动/产品详情表';


CREATE TABLE IF NOT EXISTS `{$prefix}productive_attachment{$suffix}` (
  `ptat_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `pt_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动/产品id',
  `pti_id` int(10) unsigned NOT NULL COMMENT '评分项id',
  `ptsr_id` int(10) unsigned NOT NULL COMMENT '打分id',
  `at_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件id',
  `ptat_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '记录状态, 1=正常; 2=已删除;',
  `ptat_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ptat_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ptat_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ptat_id`),
  KEY `ptsr_id` (`ptsr_id`),
  KEY `pt_id` (`pt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动/产品的附件表';


CREATE TABLE IF NOT EXISTS `{$prefix}productive_draft{$suffix}` (
  `ptd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_openid` varchar(32) NOT NULL COMMENT '用户名称',
  `ptd_subject` varchar(81) NOT NULL COMMENT '主题',
  `ptd_message` text NOT NULL COMMENT '内容',
  `ptd_a_uid` text NOT NULL COMMENT '接收人uid, 以","分隔',
  `ptd_cc_uid` text NOT NULL COMMENT '抄送人uid, 以","分隔',
  `ptd_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已删除',
  `ptd_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ptd_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ptd_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ptd_id`),
  KEY `ptd_status` (`ptd_status`),
  KEY `m_openid` (`m_openid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='活动反馈草稿表';


CREATE TABLE IF NOT EXISTS `{$prefix}productive_item{$suffix}` (
  `pti_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `pti_parent_id` int(10) unsigned NOT NULL COMMENT '上级打分项id',
  `pti_name` varchar(120) NOT NULL DEFAULT '' COMMENT '打分项名称',
  `pti_describe` varchar(255) NOT NULL COMMENT '打分项说明',
  `pti_rules` text NOT NULL COMMENT '打分详细规则',
  `pti_score` int(11) NOT NULL COMMENT '该项分数',
  `pti_fix_score` int(10) unsigned NOT NULL COMMENT '固定得分, 用户不能选择',
  `pti_ordernum` int(10) unsigned NOT NULL COMMENT '排序值, 越大越靠前',
  `pti_status` tinyint(3) NOT NULL COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `pti_created` int(10) NOT NULL COMMENT '创建时间',
  `pti_updated` int(10) NOT NULL COMMENT '更新时间',
  `pti_deleted` int(10) NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`pti_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动/产品打分项';


CREATE TABLE IF NOT EXISTS `{$prefix}productive_mem{$suffix}` (
  `ptm_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `pt_id` int(10) NOT NULL COMMENT '活动/产品id',
  `m_uid` int(10) NOT NULL COMMENT '被分享者uid',
  `m_username` varchar(54) NOT NULL COMMENT '用户名',
  `ptm_status` tinyint(3) NOT NULL COMMENT '记录状态, 1=初始化，2=已更新, 3=抄送者，4=已删除',
  `ptm_created` int(10) NOT NULL COMMENT '创建时间',
  `ptm_updated` int(10) NOT NULL COMMENT '更新时间',
  `ptm_deleted` int(10) NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`ptm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动/产品信息用户权限表';


CREATE TABLE IF NOT EXISTS `{$prefix}productive_score{$suffix}` (
  `ptsr_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `m_uid` int(10) NOT NULL COMMENT '巡视者uid',
  `cr_id` mediumint(8) unsigned NOT NULL COMMENT '所属地区id',
  `csp_id` int(10) unsigned NOT NULL COMMENT '店铺id',
  `pt_id` int(10) NOT NULL COMMENT '活动/产品id',
  `pti_id` int(3) NOT NULL COMMENT '打分项id, 为0时, 是总分',
  `ptsr_message` text NOT NULL COMMENT '对该项的评论/问题',
  `ptsr_score` int(3) NOT NULL COMMENT '分数',
  `ptsr_date` int(10) NOT NULL COMMENT '日期',
  `ptsr_type` tinyint(1) NOT NULL COMMENT '1：日，2：周，3：月',
  `ptsr_status` tinyint(3) NOT NULL COMMENT '记录状态, 1=待评，2=已评, 3=已删除',
  `ptsr_created` int(10) NOT NULL COMMENT '创建时间',
  `ptsr_updated` int(10) NOT NULL COMMENT '更新时间',
  `ptsr_deleted` int(10) NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`ptsr_id`),
  KEY `cr_id` (`cr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='店铺打分';

CREATE TABLE IF NOT EXISTS `{$prefix}productive_setting{$suffix}` (
  `pts_key` varchar(50) NOT NULL COMMENT '变量名',
  `pts_value` text NOT NULL COMMENT '值',
  `pts_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `pts_comment` text NOT NULL COMMENT '说明',
  `pts_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `pts_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `pts_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `pts_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`pts_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动/产品设置表';

CREATE TABLE IF NOT EXISTS `{$prefix}productive_tasks{$suffix}` (
  `ptt_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `ptt_title` varchar(255) NOT NULL COMMENT '任务标题',
  `ptt_description` text NOT NULL COMMENT '任务描述',
  `ptt_submit_uid` int(10) unsigned NOT NULL COMMENT '任务发起者uid',
  `ptt_assign_uid` int(10) NOT NULL COMMENT '用户id',
  `ptt_csp_id_list` text COMMENT '门店id列表,以逗号(,)分隔',
  `ptt_finished_total` int(10) unsigned NOT NULL COMMENT '已完成活动/产品数',
  `ptt_start_date` int(10) NOT NULL COMMENT '开始日期',
  `ptt_end_date` int(10) NOT NULL COMMENT '结束日期',
  `ptt_repeat_frequency` varchar(10) NOT NULL COMMENT '重复频率,no=不重复, 每天=day_(1-365), 每周=week_(1-7), 每月=mon_(1-31)',
  `ptt_execution_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '执行状态, 1=未开始，2=执行, 3=已撤销',
  `ptt_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `ptt_created` int(10) DEFAULT NULL COMMENT '创建时间',
  `ptt_updated` int(10) DEFAULT NULL COMMENT '更新时间',
  `ptt_deleted` int(10) DEFAULT NULL COMMENT '删除时间',
  `ptt_alert_time` time NOT NULL COMMENT '提醒的时间点',
  `ptt_last_execution_time` int(10) NOT NULL COMMENT '最后一次执行时间',
  `ptt_parent_id` int(10) NOT NULL COMMENT '任务父id',
  PRIMARY KEY (`ptt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动/产品任务表';

CREATE TABLE IF NOT EXISTS `{$prefix}productive_tasks_log{$suffix}` (
  `pttl_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `ptt_id` int(10) unsigned NOT NULL COMMENT '活动/产品任务表id',
  `ptt_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `ptt_created` int(10) DEFAULT NULL COMMENT '创建时间',
  `ptt_updated` int(10) DEFAULT NULL COMMENT '更新时间',
  `ptt_deleted` int(10) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`pttl_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动/产品任务表执行日志表';
