REPLACE INTO `{$prefix}news_setting{$suffix}` (`key`, `value`, `type`, `comment`, `status`, `created`, `updated`, `deleted`) VALUES
('perpage',	'10',	0,	'每页显示的新闻条数',	2,	1401755858,	1401761836,	0),
('pluginid', '28', 0, '插件id', 1, 0, 0, 0),
('cd_ids', '', '0', '', '2', '1438920552', '1442391374', '0'),
('m_uids', '', '0', '', '2', '1438920552', '1442391374', '0'),
('fixed', 'a:3:{s:4:\"name\";s:12:\"新建公告\";s:7:\"orderid\";s:1:\"1\";s:7:\"checked\";i:1;}', 1, '固定菜单', 1, '1438920552', '0', '0');
REPLACE INTO `{$prefix}news_category{$suffix}` (`nca_id`, `name`, `parent_id`, `orderid`, `status`, `created`, `updated`, `deleted`) VALUES
(1,	'公司动态',	0,	1,	1,	1427790759,	0,	0),
(2,	'通知公告',	0,	2,	1,	1427790759,	0,	0),
(3,	'员工动态',	0,	3,	1,	1427790759,	0,	0);
