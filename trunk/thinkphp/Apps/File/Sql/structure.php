CREATE TABLE IF NOT EXISTS `oa_file` (
  `f_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID，分组/文件夹/文件id',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分组id,方便解散分组',
  `f_name` char(20) NOT NULL COMMENT '分组/文件夹/文件名称',
  `f_description` varchar(255) NOT NULL COMMENT '分组介绍',
  `f_level` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分级: 1=分组; 2=文件夹; 3=文件',
  `f_parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级id，即f_id',
  `t_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件类型id,oa_file_type外键',
  `at_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件id，oa_common_attachment外键',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者id，oa_member外键',
  `m_username` char(54) NOT NULL COMMENT '创建人名称',
  `f_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '记录状态: 1=正常; 2=已更新; 3=已删除',
  `f_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `f_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `f_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`f_id`),
  KEY `f_name` (`f_name`),
  KEY `f_level` (`f_level`),
  KEY `f_parent_id` (`f_parent_id`),
  KEY `t_id` (`t_id`),
  KEY `m_uid` (`m_uid`),
  KEY `file_type_id` (`m_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文件表';

CREATE TABLE IF NOT EXISTS `oa_file_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `f_id` int(10) NOT NULL COMMENT '记录id',
  `m_uid` int(10) unsigned NOT NULL COMMENT '操作人id',
  `m_username` int(10) unsigned NOT NULL COMMENT '操作人名称',
  `temp_id` int(10) unsigned NOT NULL COMMENT '模板id，oa_file_log_template外键',
  `log_record` text NOT NULL COMMENT '模板替换字符串',
  `log_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态: 1=正常; 2=已更新; 3=已删除',
  `log_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `log_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `log_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`log_id`),
  KEY `f_id` (`f_id`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文件操作日志表';

CREATE TABLE IF NOT EXISTS `oa_file_log_template` (
  `temp_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `temp_operation` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作类型: 1=新增; 2=删除; 3=编辑; 4=移动',
  `temp_content` text NOT NULL COMMENT '日志模板',
  `temp_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态: 1=正常; 2=已更新; 3=已删除',
  `temp_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `temp_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `temp_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`temp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文件操作日志模板表';

CREATE TABLE IF NOT EXISTS `oa_file_permission` (
  `p_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `f_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分组id,oa_file表外键',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户id（m_uid和cd_id都为0时，为全公司）',
  `m_username` char(54) NOT NULL COMMENT '用户名',
  `p_sel_mark` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '成员标识: 1=用户选择; 2=逻辑添加',
  `p_mark_cd_id` int(10) unsigned NOT NULL COMMENT '主部门id，与m_sel_mark对应，方便删除部门权限',
  `cd_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '部门id，oa_common_department外键（m_uid和cd_id都为0时，为全公司）',
  `cd_name` varchar(255) NOT NULL COMMENT '部门名称',
  `p_m_type` int(4) NOT NULL COMMENT '成员类型: 15=组长; 12=协作者; 8=浏览者; 4=其他, 数据库存储二进制数',
  `p_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '记录状态: 1=正常; 2=已更新; 3=已删除',
  `p_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `p_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `p_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`p_id`),
  KEY `m_uid` (`m_uid`),
  KEY `f_id` (`f_id`),
  KEY `cd_id` (`cd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分组权限表';

CREATE TABLE IF NOT EXISTS `oa_file_setting` (
  `is_key` varchar(50) NOT NULL COMMENT '变量名',
  `is_value` text NOT NULL COMMENT '值',
  `is_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型: 0=非数组; 1:数组',
  `is_comment` text NOT NULL COMMENT '说明',
  `is_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态: 1=正常; 2=已更新; 3=已删除',
  `is_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `is_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `is_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`is_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文件设置表';

CREATE TABLE IF NOT EXISTS `oa_file_type` (
  `t_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `t_name` char(30) NOT NULL COMMENT '类型名称',
  `t_icon` varchar(255) NOT NULL COMMENT '类型图标地址',
  `t_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '记录状态: 1=正常; 2=已更新; 3=已删除',
  `t_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `t_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `t_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`t_id`),
  KEY `t_name` (`t_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文件类型表';

CREATE TABLE IF NOT EXISTS `oa_comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `obj_id` char(10) NOT NULL COMMENT '评论对象id',
  `plugin_id` int(10) unsigned NOT NULL COMMENT '应用id',
  `m_uid` int(10) unsigned NOT NULL COMMENT '评论用户id',
  `m_username` char(54) NOT NULL COMMENT '评论用户名',
  `reply_id` int(10) unsigned NOT NULL COMMENT '被回复评论id',
  `reply_m_uid` varchar(255) NOT NULL COMMENT '评论@的用户，多个id以逗号分隔',
  `content` varchar(255) NOT NULL COMMENT '评论内容',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '记录状态: 1=正常; 2=已更新; 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `obj_id` (`obj_id`),
  KEY `plugin_id` (`plugin_id`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='评论表';
