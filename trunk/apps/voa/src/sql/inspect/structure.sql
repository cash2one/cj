CREATE TABLE IF NOT EXISTS `{$prefix}inspect{$suffix}` (
  `ins_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `it_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '巡店任务id',
  `sponsor_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务发起者uid',
  `m_uid` int(10) NOT NULL DEFAULT '0' COMMENT '用户id',
  `m_username` varchar(54) NOT NULL COMMENT '用户名',
  `ins_score` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '巡店总分',
  `ins_lng` decimal(9,6) NOT NULL DEFAULT '0.000000' COMMENT '当前经度',
  `ins_lat` decimal(9,6) NOT NULL DEFAULT '0.000000' COMMENT '当前纬度',
  `csp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '门店id',
  `ins_note` varchar(255) NOT NULL DEFAULT '' COMMENT '巡店备注',
  `ins_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '巡店状态, 1=待巡, 2=进行中，3=已巡',
  `ins_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=新建, 2=已更新, 3=已删除',
  `ins_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ins_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ins_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ins_id`),
  KEY `ins_type` (`ins_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巡店详情表';


CREATE TABLE IF NOT EXISTS `{$prefix}inspect_attachment{$suffix}` (
  `insat_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `ins_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '巡店id',
  `insi_id` int(10) unsigned NOT NULL COMMENT '评分项id',
  `isr_id` int(10) unsigned NOT NULL COMMENT '打分id',
  `at_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件id',
  `insat_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常; 2=已更新; 3=已删除;',
  `insat_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `insat_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `insat_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`insat_id`),
  KEY `isr_id` (`isr_id`),
  KEY `ins_id` (`ins_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巡店的附件表';


CREATE TABLE IF NOT EXISTS `{$prefix}inspect_draft{$suffix}` (
  `insd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_openid` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名称',
  `insd_subject` varchar(81) NOT NULL DEFAULT '' COMMENT '主题',
  `insd_message` text NOT NULL COMMENT '内容',
  `insd_a_uid` text NOT NULL COMMENT '接收人uid, 以","分隔',
  `insd_cc_uid` text NOT NULL COMMENT '抄送人uid, 以","分隔',
  `insd_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常, 2=已更新，3=已删除',
  `insd_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `insd_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `insd_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`insd_id`),
  KEY `insd_status` (`insd_status`),
  KEY `m_openid` (`m_openid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='巡店草稿表';


CREATE TABLE IF NOT EXISTS `{$prefix}inspect_item{$suffix}` (
  `insi_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `insi_parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级打分项id',
  `insi_name` varchar(120) NOT NULL DEFAULT '' COMMENT '打分项名称',
  `insi_describe` varchar(255) NOT NULL DEFAULT '' COMMENT '打分项说明',
  `insi_rules_title` varchar(255) NOT NULL DEFAULT '' COMMENT '规则的标题',
  `insi_rules` text NOT NULL COMMENT '打分详细规则',
  `insi_score_title` varchar(255) NOT NULL DEFAULT '' COMMENT '打分的标题',
  `insi_score` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '该项分数',
  `insi_hasselect` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否有单项, 1: 有; 0: 没有',
  `insi_select_title` varchar(255) NOT NULL DEFAULT '' COMMENT '单项标题',
  `insi_hasatt` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否有附件, 1: 有; 0: 没有',
  `insi_att_title` varchar(255) NOT NULL DEFAULT '' COMMENT '附件标题',
  `insi_hasfeedback` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否有反馈, 1: 有; 0: 没有',
  `insi_feedback_title` varchar(255) NOT NULL DEFAULT '' COMMENT '反馈标题',
  `insi_ordernum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序值, 越大越靠前',
  `insi_state` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '使用状态, 1: 使用中; 2: 未使用',
  `insi_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `insi_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `insi_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `insi_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`insi_id`),
  KEY `insi_state` (`insi_state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巡店打分项';


CREATE TABLE IF NOT EXISTS `{$prefix}inspect_mem{$suffix}` (
  `insm_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `ins_id` int(10) NOT NULL DEFAULT '0' COMMENT '巡店id',
  `insm_src_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发起人uid',
  `m_uid` int(10) NOT NULL DEFAULT '0' COMMENT '被分享者uid',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名',
  `insm_type` tinyint(4) NOT NULL DEFAULT '2' COMMENT '状态, 1: 接收人; 2: 抄送人;',
  `insm_status` tinyint(3) NOT NULL COMMENT '记录状态, 1=初始化，2=已更新, 3=已删除',
  `insm_created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `insm_updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `insm_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`insm_id`),
  KEY `insm_type` (`insm_type`),
  KEY `insm_src_uid` (`insm_src_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巡店信息用户权限表';


CREATE TABLE IF NOT EXISTS `{$prefix}inspect_option{$suffix}` (
  `inso_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `insi_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '打分项id',
  `inso_optvalue` varchar(255) NOT NULL DEFAULT '' COMMENT '选项',
  `inso_state` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '选项状态, 1: 使用中, 2: 被弃用',
  `inso_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '记录状态, 1: 入库; 2: 更新; 3: 删除',
  `inso_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '入库时间',
  `inso_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `inso_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`inso_id`),
  KEY `inso_status` (`inso_status`),
  KEY `insi_id` (`insi_id`),
  KEY `inso_state` (`inso_state`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}inspect_score{$suffix}` (
  `isr_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `m_uid` int(10) NOT NULL DEFAULT '0' COMMENT '巡视者uid',
  `cr_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '所属地区id',
  `csp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '店铺id',
  `ins_id` int(10) NOT NULL DEFAULT '0' COMMENT '巡店id',
  `insi_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '打分项id, 为0时, 是总分',
  `isr_message` text NOT NULL COMMENT '对该项的评论/问题',
  `isr_score` int(3) NOT NULL DEFAULT '0' COMMENT '分数',
  `isr_option` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '选项值',
  `isr_date` int(10) NOT NULL DEFAULT '0' COMMENT '日期',
  `isr_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1：日，2：周，3：月',
  `isr_state` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '巡店状态, 1=待评，2=已评',
  `isr_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '记录状态, 1=新建，2=已更新, 3=已删除',
  `isr_created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `isr_updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `isr_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`isr_id`),
  KEY `cr_id` (`cr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='店铺打分';

CREATE TABLE IF NOT EXISTS `{$prefix}inspect_setting{$suffix}` (
  `is_key` varchar(50) NOT NULL COMMENT '变量名',
  `is_value` text NOT NULL COMMENT '值',
  `is_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `is_comment` text NOT NULL COMMENT '说明',
  `is_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `is_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `is_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `is_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`is_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巡店设置表';

CREATE TABLE IF NOT EXISTS `{$prefix}inspect_tasks{$suffix}` (
  `it_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `it_title` varchar(255) NOT NULL DEFAULT '' COMMENT '任务标题',
  `it_description` text NOT NULL COMMENT '任务描述',
  `it_submit_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务发起者uid',
  `it_assign_uid` int(10) NOT NULL DEFAULT '0' COMMENT '用户id',
  `it_csp_id_list` text COMMENT '门店id列表,以逗号(,)分隔',
  `it_finished_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已完成巡店数',
  `it_start_date` int(10) NOT NULL DEFAULT '0' COMMENT '开始日期',
  `it_end_date` int(10) NOT NULL DEFAULT '0' COMMENT '结束日期',
  `it_repeat_frequency` varchar(10) NOT NULL DEFAULT '' COMMENT '重复频率,no=不重复, 每天=day_(1-365), 每周=week_(1-7), 每月=mon_(1-31)',
  `it_execution_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '执行状态, 1=未开始，2=执行, 3=已撤销',
  `it_parent_id` int(10) NOT NULL DEFAULT '0' COMMENT '任务父id',
  `it_last_execution_time` int(10) NOT NULL DEFAULT '0' COMMENT '最后一次执行时间',
  `it_alert_time` time NOT NULL DEFAULT '00:00:00' COMMENT '提醒的时间点',
  `it_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `it_created` int(10) DEFAULT '0' COMMENT '创建时间',
  `it_updated` int(10) DEFAULT '0' COMMENT '更新时间',
  `it_deleted` int(10) DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`it_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巡店任务表';

CREATE TABLE IF NOT EXISTS `{$prefix}inspect_tasks_log{$suffix}` (
  `itl_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `it_id` int(10) unsigned NOT NULL COMMENT '巡店任务表id',
  `it_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `it_created` int(10) DEFAULT NULL COMMENT '创建时间',
  `it_updated` int(10) DEFAULT NULL COMMENT '更新时间',
  `it_deleted` int(10) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`itl_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巡店任务表执行日志表';
