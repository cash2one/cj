CREATE TABLE `oa_material` (
  `material_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '素材id',
  `m_uid` int(11) NOT NULL DEFAULT '0' COMMENT '创建素材用户id',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '活动标题',
  `status` tinyint(1) DEFAULT '1' COMMENT '素材状态 1审核中，2 已通过，3以驳回',
  `last_modify_time` int(10) DEFAULT '0' COMMENT '最后修改时间',
  `c_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `desc` text NOT NULL COMMENT '素材内容',
  `file_ids` text COMMENT '素材附件路径，多个用逗号分开',
  PRIMARY KEY (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='素材活动表';

 CREATE TABLE `oa_material_log` (
  `material_log_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '素材日志id',
  `status` tinyint(2) DEFAULT '1' COMMENT '素材状态 1审核中，2 已通过，3以驳回',
  `material_id` int(11) DEFAULT NULL COMMENT '素材id',
  `desc` varchar(200) DEFAULT '' COMMENT '素材变更原因',
  `c_time` int(10) DEFAULT NULL COMMENT '日志创建时间',
  PRIMARY KEY (`material_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='素材变更日志表';





