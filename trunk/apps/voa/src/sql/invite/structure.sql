CREATE TABLE IF NOT EXISTS `{$prefix}invite_personnel{$suffix}` (
  `per_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键自增ID',
  `name` varchar(10) NOT NULL DEFAULT ' ' COMMENT '姓名',
  `email` varchar(80) NOT NULL DEFAULT '' COMMENT '邮箱',
  `phone` char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `position` char(11) NOT NULL DEFAULT '' COMMENT '职位',
  `weixin_id` varchar(64) NOT NULL DEFAULT ' ' COMMENT '微信号',
  `gender` tinyint(3) NOT NULL DEFAULT '0' COMMENT '性别,0,未知;1,男;2,女',
  `custom` text NOT NULL COMMENT '自定义字段',
  `approval_state` tinyint(3) NOT NULL DEFAULT '0' COMMENT '审批状态：0,审批中;1,已通过;2,未通过;3,不审批',
  `invite_uid` int(10) NOT NULL DEFAULT '0' COMMENT '邀请人',
  `status` int(3) NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`per_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邀请人员填写信息';

CREATE TABLE IF NOT EXISTS `{$prefix}invite_setting{$suffix}` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邀请人员设置表';