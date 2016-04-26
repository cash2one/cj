SET NAMES UTF8;

TRUNCATE `uc_dbhost`;
INSERT INTO `uc_dbhost` (`db_id`, `db_title`, `db_host`, `db_user`, `db_pw`, `db_count`, `db_maximum`, `db_lanip`, `db_status`, `db_created`, `db_updated`, `db_deleted`) VALUES
(1,	'1号服务器',	'127.0.0.1',	'root',	'password',	19,	500,	'',	1,	1404182521,	0,	0);

TRUNCATE `uc_webhost`;
INSERT INTO `uc_webhost` (`web_id`, `web_title`, `web_ip`, `web_count`, `web_maximum`, `web_lanip`, `web_status`, `web_created`, `web_updated`, `web_deleted`) VALUES
(1,	'',	'127.0.0.1',	19,	500,	'',	1,	1404182521,	0,	0);

-- 2014-07-06 20:45:59

INSERT INTO `uc_common_plugin_group` (`cpg_id`, `cpg_suiteid`, `cpg_name`, `cpg_icon`, `cpg_ordernum`, `cpg_status`, `cpg_created`, `cpg_updated`, `cpg_deleted`) VALUES
(1, 'tj0129f84436fb3a58', '微信OA', 'fa-group', 0, 1, 0, 0, 0),
(2, 'tjddb742f3f8c2e73d', '销售管理', 'fa-group', 0, 1, 0, 0, 0),
(3, 'tj59546543529912af', '门店管理', 'fa-group', 0, 1, 0, 0, 0),
(4, 'tjaf008b85e2a55916', '团队协作', 'fa-group', 0, 1, 0, 0, 0),
(5, 'tj407a156836450616', '企业文化', 'fa-group', 0, 1, 0, 0, 0),
(6, 'tj3562f4e669a24045', '销售管理', 'fa-group', 0, 1, 0, 0, 0),
(7, 'tj706e8d913b31c376', '企业消息', 'fa-group', 0, 1, 0, 0, 0);

--2015年10月28日23:02:13