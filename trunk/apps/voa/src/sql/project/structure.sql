CREATE TABLE IF NOT EXISTS `{$prefix}project{$suffix}` (
  `p_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '项目发起人UID',
  `m_username` varchar(54) NOT NULL COMMENT '项目发起人用户名',
  `p_subject` varchar(255) NOT NULL COMMENT '项目主题',
  `p_message` text NOT NULL COMMENT '项目内容',
  `p_begintime` int(10) unsigned NOT NULL COMMENT '项目开始时间',
  `p_endtime` int(10) unsigned NOT NULL COMMENT '项目结束时间',
  `p_progress` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '项目进度, 范围:0 - 100',
  `p_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=已完成, 4=已关闭, 5=已删除',
  `p_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `p_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `p_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`p_id`),
  KEY `p_begintime` (`p_begintime`),
  KEY `p_endtime` (`p_endtime`),
  KEY `p_updated` (`p_updated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='项目表';


CREATE TABLE IF NOT EXISTS `{$prefix}project_attachment{$suffix}` (
  `pat_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `p_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务id',
  `pp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '进度id，如果为0则是任务附件，否则为对应进度附件',
  `at_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件id',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `pat_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常; 2=更新; 99=已删除',
  `pat_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `pat_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `pat_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`pat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='任务的附件表';


CREATE TABLE IF NOT EXISTS `{$prefix}project_draft{$suffix}` (
  `pd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_openid` varchar(32) NOT NULL COMMENT '用户名称',
  `pd_subject` varchar(81) NOT NULL COMMENT '主题',
  `pd_message` text NOT NULL COMMENT '内容',
  `pd_a_uid` text NOT NULL COMMENT '参与人uid, 以","分隔',
  `pd_cc_uid` text NOT NULL COMMENT '抄送人uid, 以","分隔',
  `pd_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已删除',
  `pd_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `pd_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `pd_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`pd_id`),
  KEY `m_uid` (`pd_status`),
  KEY `m_openid` (`m_openid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目草稿表';


CREATE TABLE IF NOT EXISTS `{$prefix}project_mem{$suffix}` (
  `pm_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `p_id` int(10) unsigned NOT NULL COMMENT '项目ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '项目用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '项目用户名',
  `pm_progress` tinyint(3) unsigned NOT NULL COMMENT '个人项目进度, 范围:0 - 100',
  `pm_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=发起者但不参加, 4=抄送, 5=已退出, 6=已删除',
  `pm_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `pm_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `pm_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`pm_id`),
  KEY `p_id` (`p_id`,`pm_status`),
  KEY `m_uid` (`m_uid`,`pm_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='参加项目人员列表';


CREATE TABLE IF NOT EXISTS `{$prefix}project_proc{$suffix}` (
  `pp_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `p_id` int(10) unsigned NOT NULL COMMENT '项目ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '项目参加人UID',
  `m_username` varchar(54) NOT NULL COMMENT '项目参加人用户名',
  `pp_progress` tinyint(3) unsigned NOT NULL COMMENT '进度值',
  `pp_message` varchar(255) NOT NULL COMMENT '备注进度',
  `pp_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=更新, 3=已删除',
  `pp_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `pp_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `pp_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`pp_id`),
  KEY `pm_id` (`pp_status`),
  KEY `m_uid` (`m_uid`,`pp_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='项目进度详情表';


CREATE TABLE IF NOT EXISTS `{$prefix}project_setting{$suffix}` (
  `ps_key` varchar(50) NOT NULL COMMENT '变量名',
  `ps_value` text NOT NULL COMMENT '值',
  `ps_type` tinyint(3) unsigned NOT NULL COMMENT '缓存类型, 0:非数组, 1:数组',
  `ps_comment` text NOT NULL COMMENT '说明',
  `ps_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `ps_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `ps_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `ps_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`ps_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='项目设置表';