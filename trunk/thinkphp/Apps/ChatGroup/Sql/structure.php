CREATE TABLE IF NOT EXISTS `oa_chatgroup` (
  `cg_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '聊天组ID',
  `cg_name` varchar(150) NOT NULL COMMENT '聊天组名称',
  `cg_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '聊天组类别 1:群聊 2:单聊',
  `m_uid` int(10) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `m_username` char(54) NOT NULL COMMENT '创建人名称',
  `cg_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '聊天组状态 1:新建 2：更新 3：删除',
  `cg_created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `cg_updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `cg_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cg_id`),
  UNIQUE KEY `UNIQUE_CG_ID` (`cg_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oa_chatgroup_member` (
  `cgm_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键id，自增',
  `cg_id` int(10) NOT NULL COMMENT '聊天组ID',
  `m_uid` int(10) NOT NULL COMMENT '聊天组成员ID',
  `m_username` char(54) NOT NULL COMMENT '聊天组成员名称',
  `cgm_count` int(10) NOT NULL COMMENT '未读消息数',
  `cgm_lasted` int(10) NOT NULL DEFAULT '0' COMMENT '最后读取时间',
  `cgm_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '成员状态 1:新建 2：修改 3：删除',
  `cgm_created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `cgm_updated` int(10) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `cgm_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cgm_id`),
  UNIQUE KEY `unique_cgm_id` (`cgm_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oa_chatgroup_record` (
  `cgr_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '聊天记录ID',
  `cgr_content` text COMMENT '聊天记录内容',
  `cgr_attachment` tinyint(3) DEFAULT '0' COMMENT '是否是附件 1：是；2：不是',
  `at_id` int(10) DEFAULT NULL COMMENT '附件ID',
  `cgr_send_uid` int(10) NOT NULL COMMENT '发送人ID',
  `cgr_send_username` char(54) DEFAULT NULL COMMENT '发送人名称',
  `cg_id` int(10) NOT NULL COMMENT '聊天组ID',
  `cgr_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '聊天记录状态 1：创建 2:更新 3：删除',
  `cgr_created` int(10) NOT NULL DEFAULT '0' COMMENT '聊天记录创建时间',
  `cgr_updated` int(10) NOT NULL DEFAULT '0' COMMENT '聊天记录更新时间',
  `cgr_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '聊天记录删除时间',
  PRIMARY KEY (`cgr_id`),
  UNIQUE KEY `unique_cgr_id` (`cgr_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oa_chatgroup_setting` (
  `cgs_key` varchar(50) NOT NULL COMMENT '变量名',
  `cgs_value` text NOT NULL COMMENT '值',
  `cgs_type` tinyint(3) unsigned NOT NULL COMMENT '缓存类型, 0:非数组, 1:数组',
  `cgs_comment` text NOT NULL COMMENT '说明',
  `cgs_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1：新建 2：更新 3:删除',
  `cgs_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `cgs_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `cgs_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cgs_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

