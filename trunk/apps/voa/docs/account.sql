-- Adminer 4.2.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `cy_enterprise_account`;
CREATE TABLE `cy_enterprise_account` (
  `acid` int(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `province` char(15) NOT NULL DEFAULT '' COMMENT '所在省份',
  `city` char(32) NOT NULL DEFAULT '' COMMENT '市',
  `county` char(54) NOT NULL DEFAULT '' COMMENT '所在县',
  `co_name` char(11) NOT NULL DEFAULT '' COMMENT '公司名称',
  `intro` varchar(255) NOT NULL COMMENT '公司简介',
  `link_name` varchar(255) NOT NULL DEFAULT '' COMMENT '联系人姓名',
  `link_phone` char(15) NOT NULL COMMENT '联系人手机',
  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '代理期限',
  `count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '代理数量',
  `created_day` varchar(255) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `created_hour` varchar(255) NOT NULL DEFAULT '0' COMMENT '注册时间时',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`acid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代理主表';

INSERT INTO `cy_enterprise_account` (`acid`, `province`, `city`, `county`, `co_name`, `intro`, `link_name`, `link_phone`, `deadline`, `count`, `created_day`, `created_hour`, `status`, `created`, `updated`, `deleted`) VALUES
(2, '江苏省',  '上海市',  '桂林路',  '畅移', '畅移科技是一家会联网公司,撒发生的发生撒发顺丰哀伤ASF阿士大夫啊神烦大叔发士大夫大师飞洒发顺丰ASF阿萨德发撒旦富士达佛挡杀佛哀伤范德萨发士大夫暗室逢灯是撒富士达飞洒地方阿士大夫收到发送的发阿萨德发的萨芬ASFASF按时发顺丰是',  '李雪', '18118162194',  1,  2,  '2015-06-17', '00:59',  2,  0,  1434597341, 0),
(7, '秦莞尔',  '发顺丰',  '哀伤', '测试', '阿萨德发顺丰', '阿士大夫', '12341234', 2,  0,  '2015-06-26', '01:57',  2,  1434438892, 1434596762, 0),
(9, 'asdf', 'asdf', 'asdf', 'asdf', 'sadfsaf',  'sfad', '23414',  0,  1,  '2015-06-20', '22:00',  3,  1434455614, 1434596647, 1434596669),
(10,  '北京', '内容吗',  '河北路',  '上海分公司',  '上海分公司是畅移', '李先明',  '18181881882',  1,  1,  '2015-06-18', '23:58',  3,  1434595459, 1434618440, 1434627979),
(11,  'a',  'a',  'a',  'a',  'a',  'a',  '23423',  1,  2,  '2015-06-11', '23:59',  3,  1434596705, 1434596750, 1434596786),
(12,  'b',  'b',  'b',  'b',  'b',  'b',  '2123', 3,  1,  '2015-06-18', '23:00',  3,  1434596725, 1434596762, 1434596786),
(13,  'd',  'a',  'a',  'a',  'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',  'a',  '18118162194',  0,  0,  '2015-06-17', '23:00',  3,  1434618481, 1434618481, 1434626281),
(14,  '', '', '', 'c',  'c',  'c',  '11111111111',  0,  0,  '', '', 3,  1434618896, 1434618999, 1434626248),
(15,  '', '', '', '撒地方',  '阿范德萨', '阿萨德',  '18118162192',  2,  0,  '', '', 3,  1434626401, 1434626401, 1434626470),
(16,  '', '', '', '阿道夫',  '阿萨德',  '阿萨德',  '18118162194',  1,  0,  '', '', 3,  1434626578, 1434626578, 1434626584),
(17,  '', '', '', 'aaa',  'ads',  'asdas',  '18118162194',  0,  0,  '', '', 3,  1434626945, 1434626945, 1434626955),
(18,  '', '', '', 'asf',  'asdfas', 'asdf', '18118162194',  0,  0,  '', '', 3,  1434626999, 1434626999, 1434627006),
(19,  '', '', '', 'a',  'a',  'a',  '18118162194',  0,  0,  '', '', 3,  1434627058, 1434627186, 1434627597),
(20,  '上海市',  '上海市',  'asdf', '畅移科技', 'asdf', 'a',  '18181881882',  1,  0,  '', '', 3,  1434627696, 1434627696, 1434627798),
(21,  'asdf', 'asd',  'a',  'aaa',  'a',  'a',  '18118162194',  0,  0,  '', '', 3,  1434628056, 1434628056, 1434628608),
(22,  'b',  'b',  'b',  'b',  'b',  'b',  '18118162194',  0,  0,  '', '', 3,  1434628088, 1434628088, 1434628581),
(23,  'c',  'c',  'c',  'c',  'c',  'c',  '18118162194',  1,  0,  '', '', 3,  1434628111, 1434628111, 1434628576),
(24,  'd',  'd',  'd',  'd',  'd',  'd',  '18118162194',  0,  0,  '', '', 3,  1434628124, 1434628124, 1434628410),
(25,  '', '', '', 'a',  'a',  'a',  '18118162194',  0,  0,  '', '', 3,  1434628651, 1434628651, 1434678012);

-- 2015-06-19 02:10:03

