
CREATE TABLE IF NOT EXISTS `{$prefix}common_signature{$suffix}` (
  `sig_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `sig_m_uid` int(10) NOT NULL COMMENT '用户id',
  `sig_code` char(40) NOT NULL COMMENT '签名',
  `sig_login_status` enum('0','1') NOT NULL COMMENT '登录状态：0-未登录，1-登录',
  `sig_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录时间',
  `sig_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=新建，2=已修改，3=已删除',
  `sig_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `sig_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sig_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`sig_id`),
  KEY `sig_code` (`sig_code`),
  KEY `sig_m_uid` (`sig_m_uid`),
  KEY `sig_login_status` (`sig_login_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='签名信息表';

CREATE TABLE IF NOT EXISTS `{$prefix}xdf_setting{$suffix}` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='备忘设置表';
