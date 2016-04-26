
CREATE TABLE `{$prefix}score_award{$suffix}` (
  `award_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '奖品名称',
  `limit` int(10) DEFAULT NULL COMMENT '奖品限制：0不限制',
  `stock` int(10) DEFAULT 0 COMMENT '库存',
  `score` int(10) DEFAULT NULL COMMENT '分数变化',
  `cd_ids` varchar(255) DEFAULT NULL COMMENT '部门分组id',
  `uids` varchar(255) DEFAULT NULL COMMENT '权限用户ID组',
  `award_pic` varchar(255) DEFAULT NULL COMMENT '奖品图片IDS',
  `desc` text COMMENT '奖品介绍',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `status` tinyint(1) DEFAULT '1' COMMENT '0:禁用 1:可用',
  `update_time` int(11) DEFAULT NULL COMMENT '最后修改时间',
  PRIMARY KEY (`award_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分奖品表';

CREATE TABLE `{$prefix}score_award_exchange{$suffix}` (
  `order_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) DEFAULT NULL COMMENT '订单编号',
  `award_id` int(10) DEFAULT NULL COMMENT '奖品ID',
  `m_uid` int(10) DEFAULT NULL COMMENT '申请用户ID',
  `op_uid` int(10) DEFAULT NULL COMMENT '处理人ID',
  `award_num` int(10) DEFAULT NULL COMMENT '兑换数量',
  `score` int(10) DEFAULT NULL COMMENT '消耗积分',
  `member_info` text COMMENT '用户填写的信息',
  `refuse_reason` varchar(255) DEFAULT NULL COMMENT '拒绝理由',
  `status` tinyint(2) DEFAULT NULL COMMENT '1:处理中 2:已处理(同意) 3:已处理(拒绝)',
  `create_time` int(11) DEFAULT NULL COMMENT '兑换时间',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='奖品兑换记录表';

CREATE TABLE `{$prefix}score_config{$suffix}` (
  `key` varchar(255) NOT NULL COMMENT '键名',
  `value` text COMMENT '值'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分配置表';

CREATE TABLE `{$prefix}score_log{$suffix}` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `m_uid` int(10) DEFAULT NULL COMMENT '用户ID',
  `op_uid` int(10) DEFAULT NULL COMMENT '操作人ID',
  `rule_id` int(10) DEFAULT NULL COMMENT '规则ID',
  `num` int(10) DEFAULT NULL COMMENT '积分变化值',
  `desc` varchar(255) DEFAULT NULL COMMENT '描述',
  `app_type` int(10) DEFAULT NULL COMMENT '所属应用/变更原因',
  `order_id` int(10) DEFAULT '0' COMMENT '对应的兑换记录ID',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分变化记录表';

CREATE TABLE `{$prefix}score_rule{$suffix}` (
  `rule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `score` int(10) DEFAULT NULL COMMENT '获得/扣除 积分数',
  `title` varchar(255) DEFAULT NULL COMMENT '规则名称',
  `loop` tinyint(1) DEFAULT NULL COMMENT '循环周期：1不限制2每天3每周4每月5每年',
  `limit` int(10) DEFAULT NULL COMMENT '限制次数：0不限制',
  `app_type` tinyint(2) DEFAULT NULL COMMENT '所属应用',
  `status` tinyint(1) DEFAULT NULL COMMENT '规则状态：0禁用 1启用',
  `update_time` int(11) DEFAULT NULL COMMENT '编辑时间',
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分规则表';

CREATE TABLE `{$prefix}score_value{$suffix}` (
  `m_uid` int(10) NOT NULL,
  `score` int(10) DEFAULT NULL COMMENT '用户当前积分'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户当前积分表';