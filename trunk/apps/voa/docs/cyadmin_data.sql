-- 默认帐号：admin
-- 默认密码：password@2014

SET NAMES UTF8;

TRUNCATE `cy_common_adminer`;
INSERT INTO `cy_common_adminer` (`ca_id`, `ca_username`, `ca_password`, `cag_id`, `ca_locked`, `cd_id`, `ca_realname`, `ca_mobilephone`, `ca_lastlogin`, `ca_lastloginip`, `ca_salt`, `ca_status`, `ca_created`, `ca_updated`, `ca_deleted`) VALUES
(1,	'admin',	'8ea336bd42f7cf1dfdd040ce0c5e8ac2',	1,	2,	0,	'abc',	'11111111111',	1402919004,	'127.0.0.1',	'KYZ2lR',	2,	1393314345,	1402919004,	0);

TRUNCATE `cy_common_adminergroup`;
INSERT INTO `cy_common_adminergroup` (`cag_id`, `cag_title`, `cag_enable`, `cag_role`, `cag_description`, `cag_status`, `cag_created`, `cag_updated`, `cag_deleted`) VALUES
(1,	'系统管理员',	2,	'-1',	'后台系统最高管理权限组，不可删除',	1,	1393314345,	0,	0);

TRUNCATE `cy_common_cpmenu`;
INSERT INTO `cy_common_cpmenu` (`ccm_id`, `cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`) VALUES
(1,	0,	1,	'manage',	'',	'',	'module',	0,	'公司管理',	'fa-tachometer',	1,	11001,	1,	0,	0,	0),
(2,	0,	1,	'setting',	'',	'',	'module',	0,	'系统设置',	'fa-cogs',	1,	11002,	1,	0,	0,	0),
(3,	0,	1,	'manage',	'adminer',	'',	'operation',	0,	'管理成员',	'',	1,	21005,	1,	0,	0,	0),
(4,	0,	1,	'manage',	'adminergroup',	'',	'operation',	0,	'管理组',	'',	1,	21006,	1,	0,	0,	0),
(5,	0,	1,	'setting',	'common',	'',	'operation',	0,	'系统环境设置',	'',	1,	21012,	1,	0,	0,	0),
(6,	0,	1,	'setting',	'cache',	'',	'operation',	0,	'缓存更新',	'',	1,	21013,	1,	0,	0,	0),
(7,	0,	1,	'manage',	'adminer',	'list',	'subop',	1,	'管理员列表',	'fa-list',	1,	31018,	1,	0,	0,	0),
(8,	0,	1,	'manage',	'adminer',	'add',	'subop',	0,	'添加管理员',	'fa-plus',	1,	31019,	1,	0,	0,	0),
(9,	0,	1,	'manage',	'adminer',	'delete',	'subop',	0,	'删除管理员',	'fa-times',	1,	31020,	1,	0,	0,	0),
(10,	0,	1,	'manage',	'adminer',	'edit',	'subop',	0,	'编辑管理员',	'fa-edit',	1,	31021,	1,	0,	0,	0),
(11,	0,	1,	'manage',	'adminergroup',	'list',	'subop',	1,	'管理组列表',	'fa-list',	1,	31022,	1,	0,	0,	0),
(12,	0,	1,	'manage',	'adminergroup',	'add',	'subop',	0,	'新增管理组',	'fa-plus',	1,	31023,	1,	0,	0,	0),
(13,	0,	1,	'manage',	'adminergroup',	'edit',	'subop',	0,	'编辑管理组',	'',	1,	31024,	1,	0,	0,	0),
(14,	0,	1,	'manage',	'adminergroup',	'delete',	'subop',	0,	'删除管理组',	'',	1,	31025,	1,	0,	0,	0),
(15,	0,	1,	'setting',	'common',	'modify',	'subop',	1,	'更改设置',	'fa-gear',	1,	31046,	1,	0,	0,	0),
(16,	0,	1,	'setting',	'cache',	'refresh',	'subop',	1,	'更新缓存',	'fa-gear',	1,	31047,	1,	0,	0,	0),
(17,	0,	1,	'enterprise',	'',	'',	'module',	0,	'事务处理',	'fa-tachometer',	1,	12047,	1,	0,	0,	0),
(18,	0,	1,	'enterprise',	'company',	'',	'operation',	1,	'用户管理',	'',	1,	999,	1,	0,	0,	0),
(19,	0,	1,	'enterprise',	'company',	'list',	'subop',	1,	'列表',	'fa-list',	1,	999,	1,	0,	0,	0),
(26,	0,	1,	'enterprise',	'reccard',	'',	'operation',	0,	'名片识别',	'',	1,	999,	1,	0,	0,	0),
(27,	0,	1,	'enterprise',	'reccard',	'edit',	'subop',	1,	'待处理',	'fa-list',	1,	999,	1,	0,	0,	0),
(28,	0,	1,	'enterprise',	'recbill',	'',	'operation',	0,	'票据识别',	'',	1,	999,	1,	0,	0,	0),
(29,	0,	1,	'enterprise',	'recbill',	'edit',	'subop',	1,	'待处理',	'fa-list',	1,	999,	1,	0,	0,	0),
(30,	0,	1,	'enterprise',	'company',	'edit',	'subop',	0,	'编辑',	'',	1,	999,	1,	0,	0,	0),
(31,    0,  1,  'enterprise',   'sms',  '', 'operation',    0,  '手机短信', '', 1,  999,    1,  0,  0,  0),
(32,    0,  1,  'enterprise',   'sms',  'list', 'subop',    1,  '列表',  'fa-list',  1,  999,    1,  0,  0,  0);
TRUNCATE `cy_common_setting`;
INSERT INTO `cy_common_setting` (`cs_key`, `cs_value`, `cs_type`, `cs_comment`, `cs_status`, `cs_created`, `cs_updated`, `cs_deleted`) VALUES
('appname',	'当前应用名称',	0,	'应用名称',	1,	0,	0,	0),
('authkey',	'werbyvchangyi',	0,	'站点密钥',	1,	0,	0,	0),
('dateformat',	'Y-m-d',	0,	'日期格式',	1,	0,	0,	0),
('dbhost',	'数据库服务器',	0,	'数据库服务器',	1,	0,	0,	0),
('dbport',	'数据库端口',	0,	'数据库端口',	1,	0,	0,	0),
('dbpw',	'数据库密码',	0,	'数据库密码',	1,	0,	0,	0),
('domain',	'demo.vchangyi.com',	0,	'当前站点域名',	1,	0,	0,	0),
('sitename',	'畅移后台管理',	0,	'畅移云工作',	2,	0,	1402565133,	0),
('timeformat',	'H:i',	0,	'时间格式',	1,	0,	0,	0),
('uc_cid',	'企业id号',	0,	'企业id号',	1,	0,	0,	0),
('wxtplids',	'a:5:{s:7:\"default\";i:3449883;s:3:\"reg\";i:3448568;s:6:\"thread\";a:2:{s:5:\"share\";i:3448578;s:5:\"reply\";i:3448579;}s:7:\"meeting\";a:1:{s:3:\"new\";i:3448570;}s:6:\"askfor\";a:4:{s:3:\"new\";i:3448574;s:6:\"refuse\";i:3448575;s:7:\"approve\";i:3448575;s:8:\"transmit\";i:3448577;}}',	1,	'模板id',	1,	0,	0,	0);

TRUNCATE `cy_common_syscache`;

TRUNCATE `cy_enterprise_app`;

TRUNCATE `cy_enterprise_profile`;

TRUNCATE `cy_recognition_bill`;

TRUNCATE `cy_recognition_bill_backup`;

TRUNCATE `cy_recognition_namecard`;

TRUNCATE `cy_recognition_namecard_backup`;

-- 2014-06-20 10:16:32
