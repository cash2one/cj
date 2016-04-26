CREATE TABLE IF NOT EXISTS `{$prefix}addressbook_setting{$suffix}` (
  `abs_key` varchar(50) NOT NULL COMMENT '变量名',
  `abs_value` text NOT NULL COMMENT '值',
  `abs_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `abs_comment` text NOT NULL COMMENT '说明',
  `abs_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `abs_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `abs_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `abs_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`abs_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='审批设置表';