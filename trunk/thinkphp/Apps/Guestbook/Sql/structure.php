<?php
/**
 * 应用的数据表结构文件
 * structure.php
 * $Author$
 */

return "
CREATE TABLE IF NOT EXISTS `[PREFIX]guestbook[SUFFIX]` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '留言ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名',
  `message` varchar(255) NOT NULL DEFAULT '' COMMENT '留言详情',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '记录状态, 1: 已创建; 2: 已更新; 3: 已删除;',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='留言板应用表';

CREATE TABLE IF NOT EXISTS `[PREFIX]guestbook_setting[SUFFIX]` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='审批设置表';
";
