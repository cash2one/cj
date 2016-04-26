-- Adminer 4.2.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `cy_enterprise_alog`;
CREATE TABLE `cy_enterprise_alog` (
  `loid` int(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `record` varchar(255) DEFAULT NULL COMMENT '记录',
  `uid` int(10) unsigned DEFAULT NULL COMMENT '操作人id',
  `epid` int(10) unsigned DEFAULT NULL COMMENT '修改公司id',
  PRIMARY KEY (`loid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代理操作日志表';

INSERT INTO `cy_enterprise_alog` (`loid`, `status`, `created`, `updated`, `deleted`, `record`, `uid`, `epid`) VALUES
(7, 1,  1434622151, 1434622151, 0,  'a:1:{s:8:\"ep_space\";s:1:\"5\";}',  1,  24),
(8, 1,  1434622311, 1434622311, 0,  'a:4:{s:11:\"ep_deadline\";s:1:\"4\";s:8:\"ep_space\";s:1:\"8\";s:6:\"ep_end\";i:1435449600;s:12:\"ep_paystatus\";s:1:\"1\";}', 1,  23);

-- 2015-06-19 02:12:16