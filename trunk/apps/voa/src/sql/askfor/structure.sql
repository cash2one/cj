CREATE TABLE IF NOT EXISTS `{$prefix}askfor{$suffix}` (
  `af_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请人UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '申请人名称',
  `af_subject` varchar(81) NOT NULL DEFAULT '' COMMENT '审批申请主题',
  `af_message` text NOT NULL COMMENT '申请内容',
  `aft_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审批模板ID',
  `afp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当前进度ID',
  `af_condition` tinyint(3) NOT NULL DEFAULT '0' COMMENT '记录状态, 1=审批申请中，2=审批通过, 3=转审批, 4=审批不通过, 5=草稿，6=已催办，7=已撤销',
  `af_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=审批申请中，2=审批通过, 3=转审批, 4=审批不通过, 5=草稿，6=已催办，7=已撤销，8=已删除',
  `af_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `af_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `af_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`af_id`),
  KEY `m_uid` (`m_uid`,`af_status`),
  KEY `afp_id` (`afp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='审批申请表';


CREATE TABLE IF NOT EXISTS `{$prefix}askfor_attachment{$suffix}` (
  `afat_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `af_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审批id',
  `afc_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论id，如果为0则是审批主题附件，否则为对应评论附件',
  `at_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件id',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名称',
  `afat_status` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常; 2=更新; 99=已删除',
  `afat_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `afat_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `afat_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`afat_id`),
  KEY `afat_status` (`afat_status`),
  KEY `af_id` (`af_id`),
  KEY `afc_id` (`afc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='审批的附件表';


CREATE TABLE IF NOT EXISTS `{$prefix}askfor_comment{$suffix}` (
  `afc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `af_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审批id',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论人UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '评论人名称',
  `afc_subject` varchar(81) NOT NULL DEFAULT '' COMMENT '评论主题',
  `afc_message` text NOT NULL COMMENT '评论内容',
  `afc_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=已删除',
  `afc_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `afc_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `afc_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`afc_id`),
  KEY `m_uid` (`m_uid`,`afc_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='审批评论信息表';


CREATE TABLE IF NOT EXISTS `{$prefix}askfor_customcols{$suffix}` (
  `afcc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `aft_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审批模板ID',
  `field` varchar(30) NOT NULL DEFAULT '' COMMENT '字段',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '字段名',
  `required` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否必填：0，不是；1：是',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '字段类型：1，单行文本；3，数字；2，多行文本；4，图片',
  `orderid` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `afcc_status` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常; 2=更新; 99=已删除',
  `afcc_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `afcc_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `afcc_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`afcc_id`),
  KEY `aft_id` (`aft_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='审批模板自定义字段表';


CREATE TABLE IF NOT EXISTS `{$prefix}askfor_customdata{$suffix}` (
  `afcd_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `af_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审批ID',
  `field` varchar(30) NOT NULL DEFAULT '' COMMENT '字段',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '字段名',
  `value` text NOT NULL COMMENT '字段值',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '字段类型：1文本 2数字 3日期 4时间 5日期时间',
  `afcd_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常; 2=更新; 99=已删除',
  `afcd_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `afcd_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `afcd_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`afcd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}askfor_draft{$suffix}` (
  `afd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) NOT NULL COMMENT '用户id',
  `last_afid` text NOT NULL COMMENT '默认审批人id',
  `last_csid` text NOT NULL COMMENT '默认抄送人id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`afd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='记录上次审批人抄送人表';


CREATE TABLE IF NOT EXISTS `{$prefix}askfor_proc{$suffix}` (
  `afp_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `af_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请主题ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审批人UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '审批人名称',
  `mp_id` int(10) unsigned NOT NULL COMMENT '职务ID',
  `mp_name` varchar(100) NOT NULL COMMENT '职务名称',
  `afp_note` varchar(255) NOT NULL DEFAULT '' COMMENT '备注进度',
  `afp_level` tinyint(3) NOT NULL DEFAULT '0' COMMENT '几级审批人',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '用于转审批（是否审批到达此人）：0，未到达；1，到达',
  `afp_condition` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '记录状态, 1=审核中，2=审核通过, 3=转审批, 4=审核不通过, 5=抄送，6=已催办，7=已撤销',
  `re_m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '转审批人ID',
  `re_m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '转审批人名称',
  `afp_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=审核中，2=审核通过, 3=转审批, 4=审核不通过, 5=抄送，6=已催办，7=已撤销，8=已删除',
  `afp_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `afp_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `afp_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`afp_id`),
  KEY `af_id` (`af_id`,`afp_status`),
  KEY `m_uid` (`m_uid`,`afp_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='审批进度表';


CREATE TABLE IF NOT EXISTS `{$prefix}askfor_proc_record{$suffix}` (
  `rafp_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `af_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请主题ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审批人UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '审批人名称',
  `rafp_note` varchar(255) NOT NULL DEFAULT '' COMMENT '备注进度',
  `rafp_condition` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '操作状态, 1=审核中，2=审核通过, 3=转审批, 4=审核不通过, 5=抄送，6=已催办，7=已撤销',
  `re_m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '转审批人ID',
  `re_m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '转审批人名称',
  `rafp_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1:初始化2:已更新3:已删除',
  `rafp_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `rafp_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `rafp_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`rafp_id`),
  KEY `af_id` (`af_id`,`rafp_status`),
  KEY `m_uid` (`m_uid`,`rafp_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='审批进度记录表';


CREATE TABLE IF NOT EXISTS `{$prefix}askfor_reply{$suffix}` (
  `afr_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `afc_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审批评论id',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复人UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '回复人名称',
  `afr_subject` varchar(81) NOT NULL DEFAULT '' COMMENT '评论主题',
  `afr_message` text NOT NULL COMMENT '评论内容',
  `afr_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=已删除',
  `afr_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `afr_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `afr_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`afr_id`),
  KEY `m_uid` (`m_uid`,`afr_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='评论的回复信息表';


CREATE TABLE IF NOT EXISTS `{$prefix}askfor_setting{$suffix}` (
  `afs_key` varchar(50) NOT NULL COMMENT '变量名',
  `afs_value` text NOT NULL COMMENT '值',
  `afs_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `afs_comment` text NOT NULL COMMENT '说明',
  `afs_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `afs_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `afs_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `afs_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`afs_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='审批设置表';


CREATE TABLE IF NOT EXISTS `{$prefix}askfor_template{$suffix}` (
  `aft_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '审批模板名',
  `creator` varchar(30) NOT NULL DEFAULT '' COMMENT '创建人',
  `bu_id` varchar(255) NOT NULL DEFAULT '-1' COMMENT '适用部门ID, -1:全公司',
  `sbu_id` text NOT NULL COMMENT '使用部门 序列化',
  `approvers` text NOT NULL COMMENT '系列化数组，审批人列表',
  `positions` text NOT NULL COMMENT '职务json',
  `is_use` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用：0，不启用；1，启用',
  `upload_image` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否上传图片：0，不上传；1，上传',
  `orderid` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '排序',
  `aft_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常; 2=更新; 99=已删除',
  `aft_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `aft_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `aft_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `custom` text NOT NULL COMMENT '自定义字段',
  `copy` text NOT NULL COMMENT '系列化数组, 抄送人列表',
  `create_id` int(10) NOT NULL COMMENT '创建者uid',
  PRIMARY KEY (`aft_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='审批模板表';

