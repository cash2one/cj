-- 公共数据表默认数据
-- 系统默认数据
-- 后台默认密码：password@2014

SET NAMES utf8;


TRUNCATE `oa_common_adminer`;

TRUNCATE `oa_common_adminergroup`;
INSERT INTO `oa_common_adminergroup` (`cag_id`, `cag_title`, `cag_enable`, `cag_role`, `cag_description`, `cag_status`, `cag_created`, `cag_updated`, `cag_deleted`) VALUES
(1, '系统管理员', 2, '-1', '后台系统最高管理权限组，不可删除', 1, 1393314345, 0, 0);

TRUNCATE `oa_common_attachment`;

TRUNCATE `oa_common_columntype`;
INSERT INTO `oa_common_columntype` (`ctid`, `ct_type`, `ct_name`, `min`, `max`, `reg_exp`, `initval`, `icon`, `status`, `created`, `updated`, `deleted`) VALUES
(1, 'int', '整形数值', 0, 0, '^([\\-\\+]?)\\d+$', '0', 'fa-sort-numeric-asc', 1, 1410767922, 0, 0),
(2, 'varchar', '字符串', 0, 255, '', '', 'fa-tags', 1, 1410767922, 0, 0),
(3, 'select', '下拉选择框', 0, 0, '', '', 'fa-sort-amount-desc', 1, 1410767922, 0, 0),
(4, 'text', '文本', 0, 65535, '', '', 'fa-text-width', 1, 1410767922, 0, 0),
(5, 'checkbox', '复选框', 0, 0, '', '', 'fa-check-square-o', 1, 1410767922, 0, 0),
(6, 'float', '浮点数', 0, 0, '^([\\-\\+]?)\\d+\\.?\\d*$', '0.0', 'fa-list-ol', 1, 1410767922, 0, 0),
(7, 'passwd', '密码', 0, 255, '', '', 'fa-key', 1, 1410767922, 0, 0),
(8, 'date', '日期', 0, 0, '^\\d{4}\\-\\d{2}\\-\\d{2}$', '0000-00-00', 'fa-calendar', 1, 1410767922, 0, 0),
(9, 'radio', '单选按钮', 0, 0, '', '', 'fa-dot-circle-o', 1, 1410767922, 0, 0),
(10, 'attach', '附件', 0, 0, '', '', 'fa-paperclip', 1, 1410767922, 0, 0),
(11, 'time', '时间', 0, 0, '^\\d{2}:\\d{2}:\\d{2}$', '00:00:00', 'fa-clock-o', 1, 1410767922, 0, 0),
(12, 'email', '邮箱', 0, 0, '^\\w+([\\.-]*\\w+)*@\\w+([\\.-]*\\w+)*(\\.\\w{2,3})+$', '', 'fa-envelope-o', 1, 1410767922, 0, 0),
(13, 'mobile', '手机', 0, 0, '^1\\d{10}$', '', 'fa-mobile-phone', 1, 1410767922, 0, 0),
(14, 'idcard', '身份证号', 0, 0, '', '', 'fa-credit-card', 1, 1410767922, 0, 0),
(15, 'postalcode', '邮编', 0, 0, '^\\d{6}$', '', 'fa-truck', 1, 1410767922, 0, 0),
(16, 'qq', 'QQ', 0, 0, '^\\d{5,11}$', '', 'fa-comment-o', 1, 1410767922, 0, 0),
(17, 'diy', '自定义类型', 0, 0, '', '', 'fa-smile-o', 1, 1410767922, 0, 0);

TRUNCATE `oa_common_cpmenu`;
INSERT INTO `oa_common_cpmenu` (`ccm_id`, `cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_subnavdisplay`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`) VALUES
(1, 0, 1, 'manage', '', '', 'module', 0, '人员管理', 'fa-tachometer', 1, 3, 1, 1, 0, 0, 0),
(10, 0, 1, 'manage', 'department', '', 'operation', 0, '部门管理', '', 1, 21, 1, 1, 0, 0, 0),
(11, 0, 1, 'manage', 'department', 'add', 'subop', 0, '添加新部门', 'fa-plus', 1, 221, 1, 1, 0, 0, 0),
(12, 0, 1, 'manage', 'department', 'delete', 'subop', 0, '删除部门', 'fa-times', 1, 222, 0, 1, 0, 0, 0),
(13, 0, 1, 'manage', 'department', 'edit', 'subop', 0, '修改部门', 'fa-edit', 1, 223, 0, 1, 0, 0, 0),
(14, 0, 1, 'manage', 'department', 'list', 'subop', 1, '部门列表', 'fa-list', 1, 224, 1, 1, 0, 0, 0),
(15, 0, 1, 'manage', 'job', '', 'operation', 0, '职位管理', '', 1, 22, 1, 1, 0, 0, 0),
(16, 0, 1, 'manage', 'job', 'add', 'subop', 0, '添加新职位', 'fa-plus', 1, 225, 1, 1, 0, 0, 0),
(17, 0, 1, 'manage', 'job', 'list', 'subop', 1, '职位列表', 'fa-list', 1, 226, 1, 1, 0, 0, 0),
(18, 0, 1, 'manage', 'job', 'modify', 'subop', 0, '删除修改', 'fa-edit', 1, 227, 0, 1, 0, 0, 0),
(19, 0, 1, 'manage', 'member', '', 'operation', 0, '员工管理', '', 1, 23, 1, 1, 0, 0, 0),
(22, 0, 1, 'manage', 'member', 'list', 'subop', 1, '员工列表', 'fa-list', 1, 230, 1, 1, 0, 0, 0),
(24, 0, 1, 'office', '', '', 'module', 0, '应用数据', 'fa-group', 1, 2, 1, 1, 0, 0, 0),
(38, 0, 1, 'setting', '', '', 'module', 0, '应用中心', 'fa-cloud', 1, 1, 1, 1, 0, 0, 0),
(39, 0, 1, 'setting', 'application', '', 'operation', 0, '应用中心', '', 1, 10, 1, 1, 0, 0, 0),
(40, 0, 1, 'setting', 'application', 'delete', 'subop', 0, '删除应用及其数据', 'fa-times', 1, 110, 0, 1, 0, 0, 0),
(41, 0, 1, 'setting', 'application', 'edit', 'subop', 0, '启用/关闭应用', 'fa-laptop', 1, 111, 0, 1, 0, 0, 0),
(42, 0, 1, 'setting', 'application', 'list', 'subop', 1, '应用中心', 'fa-gear', 1, 112, 1, 1, 0, 0, 0),
(43, 0, 1, 'system', 'setting', '', 'operation', 0, '环境设置', '', 1, 999, 1, 1, 0, 0, 0),
(44, 0, 1, 'system', 'setting', 'modify', 'subop', 1, '更改设置', 'fa-gear', 1, 113, 0, 1, 0, 0, 0),
(45, 0, 1, 'setting', 'servicetype', 'modify', 'subop', 0, '服务类型设置', 'fa-gear', 0, 9999, 0, 1, 0, 0, 0),
(46, 0, 1, 'setting', 'servicetype', '', 'operation', 0, '服务类型设置', '', 0, 9999, 1, 1, 0, 0, 0),
(49, 0, 1, 'system', '', '', 'module', 0, '系统设置', 'fa-cogs', 1, 4, 1, 1, 0, 0, 0),
(50, 0, 1, 'system', 'adminer', '', 'operation', 0, '管理员', '', 1, 31, 1, 1, 0, 0, 0),
(51, 0, 1, 'system', 'adminer', 'add', 'subop', 0, '添加管理员', 'fa-plus', 1, 115, 1, 1, 0, 0, 0),
(52, 0, 1, 'system', 'adminer', 'delete', 'subop', 0, '删除管理员', 'fa-times', 1, 116, 0, 1, 0, 0, 0),
(53, 0, 1, 'system', 'adminer', 'edit', 'subop', 0, '编辑管理员', 'fa-edit', 1, 117, 0, 1, 0, 0, 0),
(54, 0, 1, 'system', 'adminer', 'list', 'subop', 1, '管理员列表', 'fa-list', 1, 118, 1, 1, 0, 0, 0),
(55, 0, 1, 'system', 'adminergroup', '', 'operation', 0, '权限管理', '', 1, 32, 1, 1, 0, 0, 0),
(56, 0, 1, 'system', 'adminergroup', 'add', 'subop', 0, '新增管理组', 'fa-plus', 1, 119, 1, 1, 0, 0, 0),
(57, 0, 1, 'system', 'adminergroup', 'delete', 'subop', 0, '删除管理组', 'fa-times', 1, 120, 0, 1, 0, 0, 0),
(58, 0, 1, 'system', 'adminergroup', 'edit', 'subop', 0, '编辑管理组', 'fa-edit', 1, 130, 0, 1, 0, 0, 0),
(59, 0, 1, 'system', 'adminergroup', 'list', 'subop', 1, '管理组列表', 'fa-list', 1, 131, 1, 1, 0, 0, 0),
(60, 0, 1, 'system', 'cache', '', 'operation', 0, '缓存更新', '', 1, 33, 1, 1, 0, 0, 0),
(61, 0, 1, 'system', 'cache', 'refresh', 'subop', 1, '更新缓存', 'fa-refresh', 1, 132, 1, 1, 0, 0, 0),
(65, 0, 1, 'manage', 'member', 'impqywx', 'subop', 0, '同步通讯录', 'fa-exchange', 1, 232, 1, 1, 0, 0, 0),
(66, 0, 1, 'setting', 'application', 'suite', 'subop', 0, '应用套件授权', 'fa-laptop', 1, 166, 0, 1, 0, 0, 0),
(67, 0, 1, 'setting', 'application', 'bind', 'subop', 0, '绑定应用', 'fa-unlock', 1, 167, 0, 1, 0, 0, 0),
(68, 0, 1, 'manage', 'member', 'impmem', 'subop', 0, '批量导入', 'fa-plus', 1, 3, 0, 1, 0, 0, 0),
(69, 0,  1,  'manage',   'member',   'position', 'subop',    0,  '职务管理', 'fa-list',  1,  3,  1,  3,  0,  0,  0),
(70, 0, 1, 'system', 'message', '', 'operation', 0, '消息中心', '', 1, 3, 0, 1, 0, 0, 0),
(71, 0, 1, 'system', 'message', 'list', 'subop', 1, '未读消息', 'fa-list', 1, 4, 1, 1, 0, 0, 0),
(72, 0, 1, 'system', 'message', 'old', 'subop', 0, '已读消息', 'fa-eye', 1, 5, 1, 1, 0, 0, 0),
(73, 0, 1, 'system', 'message', 'view', 'subop', 0, '消息详情', 'fa-eye', 1, 6, 0, 1, 0, 0, 0);

TRUNCATE `oa_common_department`;

TRUNCATE `oa_common_job`;

TRUNCATE `oa_common_module_group`;
INSERT INTO `oa_common_module_group` (`cmg_id`, `cmg_name`, `cmg_displayorder`, `cmg_icon`, `cmg_dir`, `cmg_status`, `cmg_created`, `cmg_updated`, `cmg_deleted`) VALUES
(1, '微办公管理', 0, 'fa-group', 'office', 1, 0, 0, 0),
(2, '应用宝', 0, 'fa-puzzle-piece', 'tool', 1, 0, 0, 0);


TRUNCATE `oa_common_place_setting`;
REPLACE INTO `oa_common_place_setting` (`key`, `value`, `type`, `comment`, `status`, `created`, `updated`, `deleted`) VALUES
('allow_level_name_custom', '0', 0, '是否允许自定义级别权限称谓。0=不允许，1=允许', 1, 0, 0, 0),
('level_default_name', 'a:2:{i:1;s:9:\"负责人\";i:2;s:9:\"相关人\";}', 1, '场地相关人员默认级别名称，需要与place_member表level字段定义范围相同。', 1, 0, 0, 0),
('placetypeid_shop', '1', 0, '门店所在场所类型ID', 1, 0, 0, 0),
('place_address_length_max', '240', 0, '场地地址最大字符数', 1, 0, 0, 0),
('place_address_length_min', '2', 0, '场地地址最短字符数', 1, 0, 0, 0),
('place_master_count_max', '1', 0, '场所负责人最多允许设置数', 1, 0, 0, 0),
('place_master_count_min', '0', 0, '场所负责人最少必须设置数', 1, 0, 0, 0),
('place_name_length_max', '240', 0, '场地名称最长字符数', 1, 0, 0, 0),
('place_name_length_min', '2', 0, '场地名称最短字符数', 1, 0, 0, 0),
('place_normal_count_max', '50', 0, '场地相关人员最多允许设置数', 1, 0, 0, 0),
('place_normal_count_min', '0', 0, '场地相关人员最少必须设置数', 1, 0, 0, 0),
('region_deepin_max', '3', 0, '最多允许创建分区的级别数', 1, 0, 0, 0),
('region_master_count_max', '1', 0, '分区负责人最多允许设置数', 1, 0, 0, 0),
('region_master_count_min', '0', 0, '分区负责人最少必须设置数', 1, 0, 0, 0),
('region_name_length_max', '80', 0, '分区名称最长允许的字符数', 1, 0, 0, 0),
('region_name_length_min', '2', 0, '分区名称要求的最短字符数', 1, 0, 0, 0),
('region_normal_count_max', '0', 0, '分区相关人员最多允许设置数', 1, 0, 0, 0),
('region_normal_count_min', '0', 0, '分区相关人员最少必须设置数', 1, 0, 0, 0),
('type_max_count', '10', 0, '最多允许创建的类型数量', 1, 0, 0, 0),
('type_name_length_max', '32', 0, '类型名称最长允许的字符数', 1, 0, 0, 0),
('type_name_length_min', '2', 0, '分区名称要求的最短字符数', 1, 0, 0, 0);

TRUNCATE `oa_common_place_type`;
REPLACE INTO `oa_common_place_type` (`placetypeid`, `name`, `levels`, `status`, `created`, `updated`, `deleted`) VALUES
(1, '门店', 'a:2:{i:1;s:9:\"负责人\";i:2;s:9:\"相关人\";}', 1, 1417177966, 0, 0);


TRUNCATE `oa_common_plugin`;
INSERT INTO `oa_common_plugin` (`cp_pluginid`, `cp_identifier`, `cmg_id`, `cpg_id`, `cp_suiteid`, `cp_agentid`, `cp_displayorder`, `cp_available`, `cp_adminid`, `cp_name`, `cp_icon`, `cp_description`, `cp_datatables`, `cp_directory`, `cp_url`, `cp_version`, `cp_lastavailable`, `cp_lastopen`, `cyea_id`, `cp_status`, `cp_created`, `cp_updated`, `cp_deleted`) VALUES
(1, 'project', 1, 4, '', '', 1022, 0, 0, '任务', 'project.png', '随时随地发起任务，实时查看、推进任务进度，高效的移动任务管理平台', 'project*', 'project', 'project.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(2, 'minutes', 1, 1, '', '', 1015, 0, 0, '会议记录', 'minutes.png', '支持图片、文字快速记录，记录存档、检索功能', 'minutes*', 'minutes', 'minutes.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(3, 'namecard', 1, 0, '', '', 1003, 255, 0, '名片夹', 'namecard.png', '名片功能', 'namecard*', 'namecard', 'namecard.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(4, 'weekreport', 1, 0, '', '', 1004, 255, 0, '周报', 'weekreport.png', '周报功能', 'weekreport*', 'weekreport', 'weekreport.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(5, 'vnote', 1, 1, '', '', 1018, 0, 0, '备忘录', 'vnote.png', '支持文字快速创建备忘，备忘内容一键分享同事', 'vnote*', 'vnote', 'vnote.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(6, 'askfor', 1, 1, '', '', 1010, 0, 0, '审批', 'askfor.png', '随时随地发起审批，实时微信提醒，自由流程和固定流程结合，享受移动办公乐趣', 'askfor*', 'askfor', 'askfor.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(7, 'todo', 2, 0, '', '', 1007, 255, 0, '待办事项', 'todo.png', '个人待办事项管理', 'todo*', 'todo', 'todo.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(8, 'thread', 1, 5, '', '', 1005, 0, 0, '同事社区', 'thread.png', '企业内部移动化交流社区，员工在微信端快速发表话题并参与互动', 'thread*', 'thread', 'thread.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(9, 'vote', 2, 0, '', '', 1009, 255, 0, '微评选', '', '微信评选', 'vote*', 'vote', 'vote.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(10, 'reimburse', 1, 1, '', '', 1013, 0, 0, '报销', 'reimburse.png', '快速记录报销明细，自动生成报销记录，即时消息提醒，一站式闪电报销', 'reimburse*', 'reimburse', 'reimburse.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(11, 'dailyreport', 1, 1, '', '', 1016, 0, 0, '工作报告', 'dailyreport.png', '自定义报告类型，实时上传图文报告，自由评论，快捷归档及管理', 'dailyreport*', 'dailyreport', 'dailyreport.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(12, 'plan', 2, 0, '', '', 1012, 255, 0, '日程安排', 'plan.png', '个人日程管理', 'plan*', 'plan', 'plan.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(13, 'secret', 2, 0, '', '', 1013, 255, 0, '秘密', '', '秘密秘密', 'secret*', 'secret', 'secret.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(14, 'sign', 1, 1, '', '', 1011, 0, 0, '考勤', 'sign.png', '支持IP与经纬度双重定位，支持多部门分班次、多地点考勤设置；外勤人员必须现场拍照确认地理位置，可设置考勤提醒。', 'sign*', 'sign', 'sign.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(15, 'meeting', 1, 1, '', '', 1014, 0, 0, '订会议室', 'meeting.png', '三步快速预订会议室，与会人员自动通知，二维码签到核销', 'meeting*', 'meeting', 'meeting.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(16, 'askoff', 1, 1, '', '', 1012, 0, 0, '请假', 'askoff.png', '随时随地发起请假，实时微信提醒，快速审批请假申请', 'askoff*', 'askoff', 'askoff.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(17, 'notice', 1, 0, '', '', 1017, 255, 0, '通知公告', 'notice.png', '通知公告', 'notice*', 'notice', 'notice.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(18, 'footprint', 1, 0, '', '', 1018, 255, 0, '销售轨迹', 'footprint.png', '销售轨迹', 'footprint*', 'footprint', 'footprint.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(19, 'addressbook', 1, 1, '', '', 1008, 0, 0, '通讯录', 'addressbook.png', '移动版的企业通讯录，信息永不遗失，动态模式管理成员', 'addr*', 'addressbook', 'addressbook.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(20, 'inspect', 1, 3, '', '', 1019, 0, 0, '巡店', 'inspect.png', '针对各类终端门店和专柜量身打造的巡视类应用，巡店人员可以快速对门店情况进行核查打分并生成巡查结果', 'inspect*', 'inspect', 'inspect.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(21, 'file', 1, 0, '', '', 1021, 255, 0, '文件', 'file.png', '文件功能', 'file*', 'file', 'file.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(22, 'productive', 1, 0, '', '', 1022, 255, 0, '活动反馈', 'productive.png', '活动反馈功能', 'productive*', 'productive', 'productive.php', '0.1', 0, 0, 0, 1, 1403146977, 0, 0),
(23, 'workorder', 1, 3, '', '', 1020, 0, 0, '移动派单', 'workorder.png', '基于门店管理系统的工单应用，随时随地的发起工单、派发任务，执行人员快速跟进反馈', 'workorder*', 'workorder', 'workorder.php', '0.1', 0, 0, 0, 1, 1415280793, 0, 0),
(24, 'travel', 1, 2, '', '', 1024, 255, 0, '微分销', 'travel.png', '服务号与企业号打通，让售卖更方便。企业号让销售更方便的管理产品，服务号让客户更加方便的购买', 'travel*', 'travel', 'travel.php', '0.1', 0, 0, 0, 1, 1415280793, 0, 0),
(45, 'train', 1, 3, '', '', 1001, 255, 0, '培训', 'train.png', '企业内部移动化培训平台，培训资料快速编辑发布，微信端实时提醒，支持可见范围设置', 'train*', 'train', 'train.php', '0.1', 0, 0, 0, 1, 1417145069, 0, 0),
(26, 'showroom', 1, 3, '', '', 1021, 0, 0, '陈列', 'showroom.png', '企业快速下发陈列规范，门店人员在微信端接收查看并执行，帮组企业更方便的实施陈列标准化管理', 'showroom*', 'showroom', 'showroom.php', '0.1', 0, 0, 0, 1, 1422284436, 0, 0),
(27, 'superreport', 1, 3, '', '', 1027, 255, 0, '超级报表', 'superreport.png', '超级报表', 'superreport*', 'superreport', 'superreport.php', '0.1', 0, 0, 0, 1, 1422260139, 0, 0),
(28, 'news', 1, 5, '', '', 1004, 0, 0, '新闻公告', 'news.png', '企业移动化信息发布平台，支持消息保密设置，支持菜单类型自定义和模板选择，发布公告更省心', 'news*', 'news', 'news.php', '0.1', 0, 0, 0, 1, 1426852593, 0, 0),
(29, 'nvote', 1, 5, '', '', 1007, 0, 0, '投票调研', 'nvote.png', '企业快速调研通道，通过投票了解员工意向，创建公平民主的企业氛围', 'nvote*', 'nvote', 'nvote.php', '0.1', 0, 0, 0, 1, 1427261792, 0, 0),
(30, 'activity', 1, 5, '', '', 1006, 0, 0, '活动报名', 'activity.png', '企业组织活动型应用，员工在微信上可以快速发起活动、参与活动，允许分享到外部，支持微信扫一扫签到', 'activity*', 'activity', 'activity.php', '0.1', 0, 0, 0, 1, 1428579846, 0, 0),
(31, 'express', 1, 1, '', '', 1017, 0, 0, '快递助手', 'express.png', '高效管理接收快递流程，前台人员代收快递，扫一扫快速核销领取记录', 'express*', 'express', 'express.php', '0.1', 0, 0, 0, 1, 1429098883, 0, 0),
(32, 'campaign', 1, 2, '', '', 1032, 255, 0, '活动推广', 'campaign.png', '企业活动推广, 让企业活动更方便', 'campaign*', 'campaign', 'campaign.php', '0.1', 0, 0, 0, 1, 1429098883, 0, 0),
(33, 'redpack', 1, 5, '', '', 1033, 255, 0, '企业红包', 'redpack.png', '企业内部红包系统', 'redpack*', 'redpack', 'redpack.php', '0.1', 0, 0, 0, 1, 1426852593, 0, 0),
(34, 'sale', 1, 6, '', '', 1034, 255, 0, '销售管理', 'sale.png', '销售管理', 'sale*', 'sale', 'sale.php', '0.1', 0, 0, 0, 1, 1435721256, 0, 0),
(35, 'invite', 1, 1, '', '', 1009, 0, 0, '邀请人员', 'invite.png', '微信端快速邀请外部人员进入企业号，邀请权限自主设置，审批操作自主选择。', 'invite*', 'invite', 'invite.php', '0.1', 0, 0, 0, 1, 1426852593, 0, 0),
(36, 'chatgroup', 1, 7, '', '', 1024, 0, 0, '同事聊天', 'chatgroup.png', '同事交流最好的平台；无需添加好友，同事之间可以直接发起会话，PC端和微信手机端消息实时互通。', 'chatgroup*', 'chatgroup', 'chatgroup.php', '0.1', 0, 0, 0, 1, 1417145069, 0, 0),
(37, 'blessingredpack', 1, 4, '', '', 1023, 0, 0, '祝福红包', 'blessredpack.png', '激励员工自发了解企业文化，员工、企业心连心；打破传统，让企业福利变得更生动诱人。', 'blessredpack*', 'blessredpack', 'blessredpack.php', '0.1', 0, 0, 0, 1, 1417145069, 0, 0),
(38, 'exam', 1, 5, '', '', 1003, 0, 0, '考试', 'exam.png', '不受空间和时间限制，员工随时进行职业技能的测评，快速反馈结果。支持判断、单选、多选、填空等多种题型，灵活的题库管理，抽题、查询、提醒、统计等多种功能全支持，及时了解员工的能力提升情况。', 'exam*', 'exam', 'exam.php', '0.1', 0, 0, 0, 1, 0, 0, 0),
(39, 'banner', 1, 8, '', '', 1039, 255, 0, '微圈儿', 'banner.png', '精彩内容首页推，好友动态不错过。活动、话题、投票所有内容一手掌握。', 'banner*', 'banner', 'banner.php', '0.1', 0, 0, 0, 1, 0, 0, 0),
(40, 'cnvote', 1, 8, '', '', 1040, 255, 0, '投票', 'cnvote.png', '多种类型投票模式任你选，实时统计投票结果，互动评论更精彩', 'cnvote*', 'cnvote', 'cnvote.php', '0.1', 0, 0, 0, 1, 0, 0, 0),
(41, 'event', 1, 8, '', '', 1041, 255, 0, '活动', 'event.png', '随时随地发起活动，自动统计报名人数，一键提醒未报名人员，现场扫码统计签到，让活动组织更加简单', 'event*', 'event', 'event.php', '0.1', 0, 0, 0, 1, 0, 0, 0),
(42, 'cinvite', 1, 8, '', '', 1042, 255, 0, '邀请', 'cinvite.png', '微信快速邀请外部人员进入社群，后台自定义邀请函内容，轻松管理邀请信息', 'cinvite*', 'cinvite', 'cinvite.php', '0.1', 0, 0, 0, 1, 0, 1437707139, 0),
(43, 'community', 1, 8, '', '', 1043, 255, 0, '话题', 'community.png', '快速发布话题，参与互动讨论，收藏感兴趣的内容', 'community*', 'community', 'community.php', '0.1', 0, 0, 0, 1, 0, 0, 0),
(44, 'my', 1, 8, '', '', 1044, 255, 0, '个人中心', 'my.png', '轻松管理个人资料与收藏，查看历史参与信息。', 'my*', 'my', 'my.php', '0.1', 0, 0, 0, 1, 0, 0, 0),
(25, 'jobtrain', 1, 5, '', '', 1002, 0, 0, '培训', 'jobtrain.png', '移动互联网时代的企业移动知识库，有效的对企业培训知识进行管理、支持员工随时随地学习与提升。', 'jobtrain*', 'jobtrain', 'jobtrain.php', '0.1', 0, 0, 0, 1, 0, 0, 0),
(46, 'questionnaire', 1, 5, '', '', 1046, 0, 0, '问卷调查', 'questionnaire.png', '支持多题多类型的强大表单设计，可用于内部数据收集，外部市场调研。灵活设置问卷可见范围、实/匿名答题、定时发布问卷、未填人员提醒、是否允许重复填写、是否可分享。填写情况一键导出，轻松完成在线调查。', 'questionnaire*', 'questionnaire', 'questionnaire.php', '0.1', 0, 0, 0, 1, 0, 0, 0),
(47, 'haomai', 1, 2, '', '', 1047, 255, 0, '好卖', 'haomai.png', '畅移好卖', 'haomai*', 'haomai', 'haomai.php', '0.1', 0, 0, 0, 1, 0, 0, 0);


TRUNCATE `oa_common_plugin_group`;
INSERT INTO `oa_common_plugin_group` (`cpg_id`, `cpg_suiteid`, `cpg_name`, `cpg_icon`, `cpg_ordernum`, `pay_type`, `date_start`, `date_end`, `stop_status`, `pay_status`, `cpg_status`, `cpg_created`, `cpg_updated`, `cpg_deleted`) VALUES
(1, 'tj0129f84436fb3a58', '微信OA', 'fa-group', 8, 1, 0, 0, 0, 0, 1, 0, 0, 0),
(2, 'tjddb742f3f8c2e73d', '销售管理', 'fa-group', 0, 1, 0, 0, 0, 0, 1, 0, 0, 0),
(3, 'tj59546543529912af', '门店管理', 'fa-group', 7, 1, 0, 0, 0, 0, 1, 0, 0, 0),
(4, 'tjaf008b85e2a55916', '团队协作', 'fa-group', 6, 1, 0, 0, 0, 0, 1, 0, 0, 0),
(5, 'tj407a156836450616', '企业文化', 'fa-group', 9, 1, 0, 0, 0, 0, 1, 0, 0, 0),
(6, 'tj3562f4e669a24045', '销售管理', 'fa-group', 0, 1, 0, 0, 0, 0, 1, 0, 0, 0),
(7, 'tj706e8d913b31c376', '企业消息', 'fa-group', 5, 1, 0, 0, 0, 0, 1, 0, 0, 0),
(8, 'tjc7a647fe1b228920', '微社群', 'fa-group', 0, 1, 0, 0, 0, 0, 1, 0, 0, 0),
(9, 'tj317e9022defda8a2', '项目套件', 'fa-group', 0, 1, 0, 0, 0, 0, 1, 0, 0, 0);


TRUNCATE `oa_common_setting`;
INSERT INTO `oa_common_setting` (`cs_key`, `cs_value`, `cs_type`, `cs_comment`, `cs_status`, `cs_created`, `cs_updated`, `cs_deleted`) VALUES
('aes_key', '', 0, 'aes key', 1, 0, 0, 0),
('appname', '当前应用名称', 0, '应用名称', 1, 0, 0, 0),
('authkey', '站点加密密钥', 0, '站点密钥', 1, 0, 0, 0),
('cid', '企业id号', 0, '企业id号', 1, 0, 0, 0),
('corp_id', 'corpid', 0, 'corp_id', 1, 0, 0, 0),
('corp_secret', 'corpsecret', 0, '密钥', 1, 0, 0, 0),
('css_path', '/misc/styles', 0, 'style 脚本路径;', 1, 0, 0, 0),
('dateformat', 'Y-m-d', 0, '日期格式', 1, 0, 0, 0),
('dbhost', '数据库服务器', 0, '数据库服务器', 1, 0, 0, 0),
('dbport', '数据库端口', 0, '数据库端口', 1, 0, 0, 0),
('dbpw', '数据库密码', 0, '数据库密码', 1, 0, 0, 0),
('domain', '完整域名', 0, '当前站点域名', 1, 0, 0, 0),
('ep_id', '企业id', 0, '企业id号，来自总站后台', 1, 0, 0, 0),
('ep_wxqy', '0', 0, '是否开启了微信企业号', 1, 0, 0, 0),
('javascript_path', '/misc/scripts', 0, 'javascirpt 脚本路径;', 1, 0, 0, 0),
('locked', '0', 0, '锁定', 1, 0, 0, 0),
('openid', '企业号openid', 0, '企业号的openid', 1, 0, 0, 0),
('sitename', '网站名称', 0, '畅移云工作', 1, 0, 0, 0),
('sys_email_account', 'sys@vchangyi.com', 0, '邮件发送者名称', 1, 0, 0, 0),
('sys_email_user', '畅移云工作', 0, '邮件地址', 1, 0, 0, 0),
('timeformat', 'H:i', 0, '时间格式', 1, 0, 0, 0),
('token', '企业接口token', 0, '微信接口的token', 1, 0, 0, 0),
('update_face_interval', '86400', '0', '头像更新时间间隔', '1', '0', '0', '0'),
('qrcode', '企业二维码', 0, '企业二维码', 1, 0, 0, 0),
('xg_access_id', '2200038002', 0, '信鸽 ACCESS ID', 1, 0, 0, 0),
('xg_access_key', 'IVKLH6856Y2C', 0, '信鸽 ACCESS KEY', 1, 0, 0, 0),
('xg_secret_key', '36dcb132fc5815c2c5be4b9e026d60af', 0, '信鸽 SECRET KEY', 1, 0, 0, 0);

TRUNCATE `oa_common_syscache`;

TRUNCATE `oa_member`;

TRUNCATE `oa_member_field`;

TRUNCATE `oa_member_position`;
INSERT INTO `oa_member_position` (`mp_id`, `mp_name`, `mp_parent_id`, `mp_status`, `mp_created`, `mp_updated`, `mp_deleted`) VALUES
(1, '总经理', 0, 1, 0, 0, 0),
(2, '经理', 1, 1, 0, 0, 0),
(3, '主管', 2, 1, 0, 0, 0),
(4, '员工', 3, 1, 0, 0, 0);


TRUNCATE `oa_member_setting`;
INSERT INTO `oa_member_setting` (`m_key`, `m_value`, `m_type`, `m_comment`, `m_status`, `m_created`, `m_updated`, `m_deleted`) VALUES
('fields', 'a:2:{s:5:"fixed";a:7:{s:4:"name";a:6:{s:6:"number";i:1;s:4:"name";s:6:"姓名";s:4:"open";i:1;s:8:"required";i:1;s:4:"view";i:1;s:5:"level";i:0;}s:6:"userid";a:6:{s:6:"number";i:2;s:4:"name";s:6:"账号";s:4:"open";i:1;s:8:"required";i:0;s:4:"view";i:1;s:5:"level";i:1;}s:6:"gender";a:6:{s:6:"number";i:3;s:4:"name";s:6:"性别";s:4:"open";i:1;s:8:"required";i:1;s:4:"view";i:1;s:5:"level";i:1;}s:6:"mobile";a:6:{s:6:"number";i:4;s:4:"name";s:9:"手机号";s:4:"open";i:1;s:8:"required";i:0;s:4:"view";i:1;s:5:"level";i:1;}s:8:"weixinid";a:6:{s:6:"number";i:5;s:4:"name";s:6:"微信";s:4:"open";i:1;s:8:"required";i:0;s:4:"view";i:1;s:5:"level";i:1;}s:5:"email";a:6:{s:6:"number";i:6;s:4:"name";s:6:"邮箱";s:4:"open";i:1;s:8:"required";i:0;s:4:"view";i:1;s:5:"level";i:1;}s:10:"department";a:6:{s:6:"number";i:7;s:4:"name";s:6:"部门";s:4:"open";i:1;s:8:"required";i:1;s:4:"view";i:1;s:5:"level";i:1;}}s:6:"custom";a:4:{s:6:"leader";a:6:{s:6:"number";i:1;s:4:"name";s:12:"直属领导";s:4:"open";i:1;s:8:"required";i:0;s:4:"view";i:0;s:5:"level";i:2;}s:8:"birthday";a:6:{s:6:"number";i:2;s:4:"name";s:6:"生日";s:4:"open";i:1;s:8:"required";i:0;s:4:"view";i:0;s:5:"level";i:2;}s:7:"address";a:6:{s:6:"number";i:3;s:4:"name";s:6:"地址";s:4:"open";i:1;s:8:"required";i:0;s:4:"view";i:0;s:5:"level";i:2;}s:8:"position";a:6:{s:6:"number";i:4;s:4:"name";s:6:"职位";s:4:"open";i:1;s:8:"required";i:0;s:4:"view";i:0;s:5:"level";i:2;}}}', 1, '扩展字段设置', 1, 0, 0, 0),
('sensitive', '', 1, '敏感成员标签可见字段设置', 1, 0, 0, 0);

TRUNCATE `oa_weixin_location`;

TRUNCATE `oa_weixin_msg`;

TRUNCATE `oa_weixin_qrcode`;
ALTER TABLE oa_weixin_qrcode AUTO_INCREMENT = 1000000;

TRUNCATE `oa_weixin_setting`;
INSERT INTO `oa_weixin_setting` (`ws_key`, `ws_value`, `ws_type`, `ws_comment`, `ws_status`, `ws_created`, `ws_updated`, `ws_deleted`) VALUES
('access_token', '', 0, 'access_token 是公众号的全局唯一票据，公众号调用各接口时都需使用 access_token.', 2, 0, 1401873056, 0),
('token_expires', '', 0, 'access_token 的有效期, 时间戳超过该值, 则说明 access_token 已经过期', 2, 0, 1401873056, 0);


-- 2014-06-19 04:11:10
