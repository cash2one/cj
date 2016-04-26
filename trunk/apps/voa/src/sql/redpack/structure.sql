CREATE TABLE IF NOT EXISTS `{$prefix}redpack{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发红包的用户uid',
  `m_username` varchar(54) NOT NULL COMMENT '发红包的用户名称',
  `actname` varchar(100) NOT NULL DEFAULT '' COMMENT '红包活动名称',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '红包分配方式：1=排队分区随机; 2=平均分配; 3=定点红包; 4=自由红包;',
  `total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '红包活动总金额, 单位: 分',
  `left` int(11) NOT NULL DEFAULT '0' COMMENT '剩余红包总额, 单位: 分',
  `redpacks` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '红包总数',
  `times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '红包已被领取个数',
  `starttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '红包开始时间',
  `endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动结束时间',
  `nickname` varchar(32) NOT NULL DEFAULT '' COMMENT '提供方名称',
  `sendname` varchar(32) NOT NULL DEFAULT '' COMMENT '红包发送者名称',
  `wishing` varchar(128) NOT NULL DEFAULT '' COMMENT '红包祝福语',
  `logoimgurl` varchar(128) NOT NULL DEFAULT '' COMMENT '商户logo的url',
  `sharecontent` varchar(255) NOT NULL DEFAULT '' COMMENT '分享文案',
  `shareurl` varchar(128) NOT NULL DEFAULT '' COMMENT '分享链接',
  `shareimgurl` varchar(128) NOT NULL DEFAULT '' COMMENT '分享的图片url',
  `min` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最小红包',
  `max` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最大红包',
  `highest` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当前最高红包',
  `rule` text NOT NULL COMMENT '红包分配规则, 序列化的字符串,每个红包都已经分配好了',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新建;2=已更新;3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='红包活动主表';


CREATE TABLE IF NOT EXISTS `{$prefix}redpack_department{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `redpack_id` int(10) unsigned NOT NULL COMMENT '红包ID',
  `cd_id` int(10) unsigned NOT NULL COMMENT '部门cd_id, 为 0 时, 全体成员可看',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`status`),
  KEY `cd_id` (`cd_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='可领取红包部门列表';


CREATE TABLE IF NOT EXISTS `{$prefix}redpack_log{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `redpack_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属的红包活动ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID，如果是外部人员领取其为0',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `openid` varchar(32) NOT NULL DEFAULT '' COMMENT '用户的微信openid',
  `money` mediumint(5) unsigned NOT NULL DEFAULT '0' COMMENT '领到的钱数。单位：分',
  `ip` varchar(15) NOT NULL DEFAULT '0.0.0.0' COMMENT 'IP地址',
  `appid` varchar(32) NOT NULL COMMENT '红包应用appid',
  `mch_billno` varchar(28) NOT NULL COMMENT '红包订单号',
  `result` text NOT NULL COMMENT '发放结果, 微信返回的结果',
  `sendst` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否发送, 0: 未发送; 1: 已发送',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新建;2=已更新;3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `rpid` (`redpack_id`),
  KEY `m_uid` (`m_uid`),
  KEY `openid` (`openid`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='红包领取分配记录';


CREATE TABLE IF NOT EXISTS `{$prefix}redpack_mem{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `redpack_id` int(10) unsigned NOT NULL COMMENT '红包ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID, 为 0 时, 全体成员可看',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`status`),
  KEY `m_uid` (`m_uid`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='可领取红包人员列表';


CREATE TABLE IF NOT EXISTS `{$prefix}redpack_setting{$suffix}` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='红包设置表';


CREATE TABLE IF NOT EXISTS `{$prefix}redpack_total{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `year` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '年份, 为 0 时, 记录的是总数;',
  `money` mediumint(5) unsigned NOT NULL DEFAULT '0' COMMENT '红包金额总数。单位：分',
  `rp_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收到红包总个数',
  `highest_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '手气最佳次数(只限于随机红包)',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新建;2=已更新;3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `m_uid` (`m_uid`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='红包领取统计记录';
