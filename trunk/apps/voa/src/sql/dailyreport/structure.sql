CREATE TABLE IF NOT EXISTS `{$prefix}dailyreport{$suffix}` (
  `dr_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名称',
  `dr_subject` varchar(81) NOT NULL DEFAULT '' COMMENT '报告主题',
  `dr_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '日报类型',
  `dr_reporttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '报告时间戳',
  `dr_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已更新, 3=已删除',
  `dr_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `dr_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `dr_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `dr_is_new` tinyint(255) NOT NULL DEFAULT '0' COMMENT '是否是新版报告1是0不是',
  `dr_from_dr_id` int(10) NOT NULL DEFAULT '0' COMMENT '转发来源报告id 0没有来源 或者为旧版',
  `dr_remark` varchar(300) DEFAULT '' COMMENT '转发备注',
    `dr_forword_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '转发人 id',
  `dr_forword_uname` varchar(255) NOT NULL DEFAULT '' COMMENT '转发人姓名',
  PRIMARY KEY (`dr_id`),
  KEY `m_uid` (`m_uid`,`dr_status`),
  KEY `dr_type` (`dr_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报告主题表';


CREATE TABLE IF NOT EXISTS `{$prefix}dailyreport_attachment{$suffix}` (
  `drat_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `dr_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '日报id',
  `drp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论id，如果为0则是日报附件，否则为对应评论附件',
  `at_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件id',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名称',
  `drat_status` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常; 2=更新; 99=已删除',
  `drat_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `drat_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `drat_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`drat_id`),
  KEY `drat_status` (`drat_status`),
  KEY `dr_id` (`dr_id`),
  KEY `drp_id` (`drp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='日报的附件表';


CREATE TABLE IF NOT EXISTS `{$prefix}dailyreport_draft{$suffix}` (
  `drd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_openid` varchar(32) NOT NULL COMMENT '用户名称',
  `drd_subject` varchar(81) NOT NULL COMMENT '报告主题',
  `drd_message` text NOT NULL COMMENT '报告内容',
  `drd_a_uid` int(10) unsigned NOT NULL COMMENT '审批人uid',
  `drd_cc_uid` text NOT NULL COMMENT '抄送人uid, 以","分隔',
  `drd_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已删除',
  `drd_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `drd_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `drd_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`drd_id`),
  KEY `m_uid` (`drd_status`),
  KEY `m_openid` (`m_openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报告草稿表';

CREATE TABLE IF NOT EXISTS `{$prefix}dailyreport_draftx{$suffix}` (
  `drd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(32) unsigned NOT NULL DEFAULT '0' COMMENT '草稿所属人的id',
  `drd_title` varchar(81) NOT NULL COMMENT '报告主题',
  `drt_module` text NOT NULL COMMENT '报告内容',
  `drd_a_uid` text NOT NULL COMMENT '接收人',
  `drd_cc_uid` text NOT NULL COMMENT '抄送人uid, 以","分隔',
  `drd_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已删除',
  `drd_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `drd_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `drd_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `drt_id` int(11) NOT NULL DEFAULT '0' COMMENT '模板类型',
  PRIMARY KEY (`drd_id`),
  KEY `m_uid` (`drd_status`),
  KEY `m_openid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报告草稿表';



CREATE TABLE IF NOT EXISTS `{$prefix}dailyreport_mem{$suffix}` (
  `drm_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `dr_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '报告ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID, 为 0 时, 全体成员可看',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名',
  `drm_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=抄送人(发送报告的人)，4=已删除',
  `drm_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `drm_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `drm_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `get_level` tinyint(255) NOT NULL DEFAULT '1' COMMENT '1接收人0抄送人',
  PRIMARY KEY (`drm_id`),
  KEY `drm_id` (`drm_id`,`drm_status`),
  KEY `m_uid` (`m_uid`,`drm_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='可查看报告人员列表';


CREATE TABLE IF NOT EXISTS `{$prefix}dailyreport_post{$suffix}` (
  `drp_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名称',
  `dr_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `drp_subject` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `drp_message` text NOT NULL COMMENT '内容',
  `drp_first` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否主题，0=不是，1=是',
  `drp_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `drp_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `drp_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `drp_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `drp_comment_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论回复给谁',
   `drp_comment_user_name` varchar(255) NOT NULL DEFAULT '' COMMENT '回复人的姓名',
`drp_comment_content`  varchar(255) NOT NULL DEFAULT '' COMMENT '上一层评论的内容',
  `drp_is_new` tinyint(255) NOT NULL DEFAULT '0' COMMENT '1是新的0不是',
  `drp_new_message` text NOT NULL COMMENT '新版本的报告数据',
`drp_forword_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '转发人 id',
  `drp_forword_uname` varchar(255) NOT NULL DEFAULT '' COMMENT '转发人姓名',
  PRIMARY KEY (`drp_id`),
  KEY `st_id` (`dr_id`,`drp_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报告评论/回复信息';

CREATE TABLE IF NOT EXISTS `{$prefix}dailyreport_read{$suffix}` (
  `rid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `is_read` tinyint(1) unsigned NOT NULL COMMENT '1:未读,2已读',
  `dr_id` int(10) unsigned NOT NULL COMMENT '日报id',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户uid',
  `status` int(10) unsigned NOT NULL COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`rid`),
  KEY `read_index` (`status`,`created`,`updated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='工作日报读取状态';


CREATE TABLE IF NOT EXISTS `{$prefix}dailyreport_setting{$suffix}` (
  `drs_key` varchar(50) NOT NULL COMMENT '变量名',
  `drs_value` text NOT NULL COMMENT '值',
  `drs_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `drs_comment` text NOT NULL COMMENT '说明',
  `drs_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `drs_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `drs_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `drs_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`drs_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报告设置表';


CREATE TABLE IF NOT EXISTS `{$prefix}dailyreport_tpl{$suffix}` (
  `drt_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '模板类型Id',
  `drt_name` varchar(255) NOT NULL DEFAULT '' COMMENT '模板名称(用于显示到前端)',
  `drt_switch` tinyint(255) NOT NULL DEFAULT '1' COMMENT '1启用0禁用',
  `drt_departments` text NOT NULL COMMENT '存储可见部门id 逗号分开, 为空则为全公司',
  `drt_status` tinyint(255) NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已更新, 3=已删除',
  `drt_module` text COMMENT '组件配置',
  `drt_sort` smallint(255) unsigned NOT NULL DEFAULT '0' COMMENT '序号(排序)',
  `drt_created` int(255) unsigned NOT NULL DEFAULT '0' COMMENT '模板创建的时间',
  `drt_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `drt_deleted` int(255) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`drt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='工作日报模板表';


CREATE TABLE IF NOT EXISTS `{$prefix}dailyreport_tpl_department{$suffix}` (
  `drt_id` int(11) NOT NULL DEFAULT '0' COMMENT '模板id',
  `dp_id` int(11) NOT NULL COMMENT '部门id',
  `dp_is_show` tinyint(255) NOT NULL DEFAULT '1' COMMENT '1启用0隐藏',
  KEY `drt_id_dp_id` (`drt_id`,`dp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='部门模板关系表';
