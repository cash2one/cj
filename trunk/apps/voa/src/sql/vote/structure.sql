CREATE TABLE IF NOT EXISTS `{$prefix}vote{$suffix}` (
  `v_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '申请人UID',
  `m_username` varchar(54) NOT NULL COMMENT '申请人名称',
  `v_subject` varchar(81) NOT NULL COMMENT '投票主题',
  `v_message` text NOT NULL COMMENT '投票详情',
  `v_begintime` int(10) unsigned NOT NULL COMMENT '开始时间',
  `v_endtime` int(10) unsigned NOT NULL COMMENT '结束时间',
  `v_friend` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '分享状态, 0=所有人, 1=指定投票用户',
  `v_ismulti` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '是否多选, 0: 单选, 1: 多选',
  `v_minchoices` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '最少选项数',
  `v_maxchoices` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '最多选项数',
  `v_isopen` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放, 0: 关闭; 1: 开放',
  `v_inout` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '范围标识, 0: 只对内部, 1: 对外开放;',
  `v_voters` mediumint(8) unsigned NOT NULL COMMENT '投票人次',
  `v_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=申请中，2=已审核, 3=已拒绝, 4=已删除',
  `v_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `v_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `v_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`v_id`),
  KEY `m_uid` (`m_uid`,`v_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票主题表';

CREATE TABLE IF NOT EXISTS `{$prefix}vote_mem{$suffix}` (
  `vm_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `v_id` int(10) unsigned NOT NULL COMMENT '投票ID',
  `vo_id` int(10) unsigned NOT NULL COMMENT '投票选项id',
  `vm_ip` varchar(15) NOT NULL COMMENT '投票ip',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名',
  `vm_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `vm_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `vm_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `vm_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`vm_id`),
  KEY `v_id` (`v_id`,`vm_status`),
  KEY `m_uid` (`m_uid`,`vm_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='有权限投票用户列表';

CREATE TABLE IF NOT EXISTS `{$prefix}vote_option{$suffix}` (
  `vo_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `v_id` int(10) unsigned NOT NULL COMMENT '投票id',
  `vo_option` varchar(81) NOT NULL COMMENT '选项',
  `vo_votes` int(10) unsigned NOT NULL COMMENT '得票数',
  `vo_displayorder` int(10) unsigned NOT NULL COMMENT '排序号, 值越大越靠前',
  `vo_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=已删除',
  `vo_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `vo_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `vo_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`vo_id`),
  KEY `v_id` (`v_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票选项表';

CREATE TABLE IF NOT EXISTS `{$prefix}vote_permit_user{$suffix}` (
  `vpu_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `v_id` int(10) unsigned NOT NULL COMMENT '投票ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '允许浏览的用户UID, 为 0 时, 则表示分享给所有人',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `vpu_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `vpu_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `vpu_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `vpu_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`vpu_id`),
  KEY `v_id` (`v_id`),
  KEY `m_uid` (`m_uid`,`vpu_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='允许进行投票的用户表';

CREATE TABLE IF NOT EXISTS `{$prefix}vote_setting{$suffix}` (
  `vs_key` varchar(50) NOT NULL COMMENT '变量名',
  `vs_value` text NOT NULL COMMENT '值',
  `vs_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `vs_comment` text NOT NULL COMMENT '说明',
  `vs_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `vs_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `vs_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `vs_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`vs_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票设置表';