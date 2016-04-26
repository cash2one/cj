CREATE TABLE IF NOT EXISTS `{$prefix}workorder{$suffix}` (
  `woid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID,工单编号',
  `wostate` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '工单状态:1=待执行,2=已拒绝;3=已确认;4=已完成;99=派单人已撤销',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '派单人UID',
  `operator_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行人UID',
  `ordertime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '派单时间',
  `canceltime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '撤单时间',
  `confirmtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行人确认时间',
  `completetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行人完成时间',
  `contacter` varchar(32) NOT NULL DEFAULT '' COMMENT '联系人',
  `phone` varchar(32) NOT NULL DEFAULT '' COMMENT '联系电话',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '联系地址',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '工单备注',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态:1=新创建; 2=已更新; 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`woid`),
  KEY `status` (`status`),
  KEY `uid_wo_state_ordertime` (`uid`,`wostate`,`ordertime`),
  KEY `operatorid` (`operator_uid`),
  KEY `ordertime` (`ordertime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='派单-工单主表' AUTO_INCREMENT=10001;


CREATE TABLE IF NOT EXISTS `{$prefix}workorder_attachment{$suffix}` (
  `woatid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `woid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '工单ID',
  `at_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行人UID',
  `role` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '附件上传人角色:1=派单人;2=执行人;3=接收人',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态:1=新创建; 2=已更新; 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`woatid`),
  KEY `status` (`status`),
  KEY `woid` (`woid`),
  KEY `at_id` (`at_id`),
  KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='派单-附件表';


CREATE TABLE IF NOT EXISTS `{$prefix}workorder_detail{$suffix}` (
  `woid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '工单ID',
  `caption` varchar(255) NOT NULL DEFAULT '' COMMENT '执行说明',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态:1=新创建; 2=已更新; 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`woid`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='派单-工单执行详情';


CREATE TABLE IF NOT EXISTS `{$prefix}workorder_log{$suffix}` (
  `wologid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `woid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '工单ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作人UID',
  `action` enum('send','refuse','confirm','complete','cancel','mycancel','unknown') NOT NULL DEFAULT 'unknown' COMMENT '动作类型',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
  `ip` varchar(15) NOT NULL DEFAULT '0.0.0.0' COMMENT '操作时IP',
  `long` float NOT NULL DEFAULT '0' COMMENT '操作时经度',
  `lat` float NOT NULL DEFAULT '0' COMMENT '操作时纬度',
  `location` varchar(100) NOT NULL DEFAULT '' COMMENT '操作时所在位置',
  `reason` varchar(255) NOT NULL DEFAULT '' COMMENT '操作说明or原因',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态:1=新创建; 2=已更新; 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`wologid`),
  KEY `status` (`status`),
  KEY `woid` (`woid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='派单-工单操作历史记录表';


CREATE TABLE IF NOT EXISTS `{$prefix}workorder_receiver{$suffix}` (
  `worid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `worstate` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '接单状态:1=待确认,2=已拒绝,3=已确认,4=已完成,5=被抢单,6=已撤单,7=别人已完成,99=已撤销',
  `woid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '工单ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接单人UID',
  `ordertime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '派单时间',
  `actiontime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次操作时间，由自己触发',
  `completetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态:1=新创建; 2=已更新; 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`worid`),
  KEY `status` (`status`),
  KEY `operator_uid_woostate_ordertime` (`uid`,`worstate`,`ordertime`),
  KEY `woid` (`woid`),
  KEY `ordertime` (`ordertime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='派单-工单接收人表';


CREATE TABLE IF NOT EXISTS `{$prefix}workorder_setting{$suffix}` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型:0=非数组; 1=数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态:1=新创建; 2=已更新; 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='派单 - 设置表';
