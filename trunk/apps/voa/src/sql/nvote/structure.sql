
CREATE TABLE IF NOT EXISTS `{$prefix}nvote{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `subject` varchar(2000)  NOT NULL COMMENT '投票主题',
  `submit_id` int(10) unsigned  NOT NULL COMMENT '投票发起者uid|0标示后台管理员发起',
  `submit_ca_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票发起者|后台管理员id',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `is_show_name` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示姓名, 1=显示,2=不显示',
  `is_single` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否单选,1=单选,2=多选',
  `is_show_result` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否查看结果',
  `is_repeat` TINYINT(3) NOT NULL DEFAULT 2 COMMENT '是否允许重复投票',
  `voted_mem_count` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '已投用户数',
  `close_status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '关闭状态, 1=未关闭, 2=已关闭',
  `close_uid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '关闭投票用户',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化, 2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `time` (`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票调研';

CREATE TABLE IF NOT EXISTS `{$prefix}nvote_option{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `nvote_id` int(10) unsigned  NOT NULL COMMENT '投票子项id',
  `option` varchar(255) NOT NULL COMMENT '选项',
  `nvotes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票数',
  `priority` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '显示优先级,值越大越靠前',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `nvote_id` (`nvote_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票调研选项';

CREATE TABLE IF NOT EXISTS `{$prefix}nvote_mem{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `nvote_id` int(10) unsigned  NOT NULL COMMENT '投票主题id',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `is_nvote` tinyint(3) unsigned  NOT NULL DEFAULT '1' COMMENT '是否已投, 1=未投票,2=已投票',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票调研参与用户';

CREATE TABLE IF NOT EXISTS `{$prefix}nvote_department{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `nvote_id` int(10) unsigned  NOT NULL COMMENT '投票主题id',
  `cd_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '部门id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `cd_id` (`cd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票调研参与部门';

CREATE TABLE IF NOT EXISTS `{$prefix}nvote_mem_option{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `nvote_id` int(10) unsigned  NOT NULL COMMENT '投票调研主题id',
  `nvote_option_id` int(10) unsigned NOT NULL COMMENT '投票调研选项id',
  `ip` VARCHAR(128) NOT NULL COMMENT '用户投票ip',
  `note` varchar(500) NOT NULL DEFAULT '' COMMENT '备注|其他',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票用户|外部用户可为空',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE `nvote` (`nvote_id`, `nvote_option_id`, `m_uid`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票调研用户选项';

CREATE TABLE IF NOT EXISTS `{$prefix}nvote_attachment{$suffix}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `nvote_id` int(10) unsigned  NOT NULL COMMENT '投票主题id',
  `nvote_option_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '投票选项id(可为空)',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `at_id` INT(10) UNSIGNED NOT NULL COMMENT '附件id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票调研附件';

CREATE TABLE IF NOT EXISTS `{$prefix}nvote_setting{$suffix}` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='工作台设置表';
