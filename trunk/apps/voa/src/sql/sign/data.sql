INSERT INTO `{$prefix}sign_setting{$suffix}` (`ss_key`, `ss_value`, `ss_type`, `ss_comment`, `ss_status`, `ss_created`, `ss_updated`, `ss_deleted`) VALUES
('available_distance',	'1000',	0,	'',	1,	1399187776,	1399187776,	0),
('late_range',	'600',	0,	'',	1,	1399187776,	1399187776,	0),
('leave_early_range',	'600',	0,	'',	1,	1399187776,	1399187776,	0),
('locationx',	'23.134521',	0,	'',	1,	1399187776,	1399187776,	0),
('locationy',	'113.358803',	0,	'',	1,	1399187776,	1399187776,	0),
('sign_begin_hi',	'05:00',	0,	'',	1,	1399187776,	1399187776,	0),
('sign_end_hi',	'23:59',	0,	'',	1,	1399187776,	1399187776,	0),
('sign_expires',	'30',	0,	'',	2,	1399187776,	1402304362,	0),
('work_begin_hi',	'09:00',	0,	'',	1,	1399187776,	1399187776,	0),
('work_end_hi',	'17:00',	0,	'',	1,	1399187776,	1399187776,	0),
('work_days',	'a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}',	1,	'',	1,	1414250411,	1414250411,	0),
('up_position_rate',	'60',	0,	'',	1,	0,	0,	0),
('pluginid', '14', 0, '插件id', 1, 0, 0, 0),
('out_sign_upload_img', '2', '0', '外出考勤是否必须上传图片 1-关闭；2-开启', '1', '0', '1457518469', '0'),
('rest_day_sign', '1', '0', '休息日是否允许考勤 1-关闭；2-开启', '1', '0', '1457518469', '0'),
('sign_come_late_range', '10', '0', '迟到规则', '1', '0', '1457505211', '0'),
('sign_late_range', '60', '0', '加班规则', '1', '0', '1456748109', '0'),
('sign_leave_early_range', '10', '0', '早退规则', '1', '0', '1456748109', '0'),
('sign_remind_off', '下班了，快来签退吧~！', '0', '签退提醒内容', '1', '0', '1456748109', '0'),
('sign_remind_off_rage', '5', '0', '签退时间点后XX分钟提醒', '1', '0', '1456748109', '0'),
('sign_remind_on', '上班了，快来签到吧~！', '0', '签到提醒内容', '1', '0', '1457505211', '0'),
('sign_remind_on_rage', '5', '0', '签到时间点前XX分钟提醒', '1', '0', '1457505211', '0'),
('sign_start_range', '60', '0', '签到时间范围', '1', '0', '1457505211', '0'),
('wxcpmenu', 'a:3:{i:0;a:4:{s:4:"name";s:12:"公司考勤";s:3:"url";s:53:"{domain_url}/frontend/sign/index/?pluginid={pluginid}";s:4:"type";s:4:"view";s:9:"$$hashKey";s:10:"object:105";}i:1;a:4:{s:4:"name";s:12:"外出考勤";s:3:"url";s:58:"{domain_url}/frontend/sign/uplocation/?pluginid={pluginid}";s:4:"type";s:4:"view";s:9:"$$hashKey";s:10:"object:106";}i:2;a:4:{s:4:"name";s:12:"考勤记录";s:3:"url";s:58:"{domain_url}/frontend/sign/signsearch/?pluginid={pluginid}";s:4:"type";s:4:"view";s:9:"$$hashKey";s:10:"object:107";}}', 1, '', 1, 1457580775, 1458247329, 0),
('sign_end_rage', '120', '0', '签退时间范围', '1', '0', '1456748109', '0');


INSERT INTO `{$prefix}sign_batch{$suffix}` (`sbid`, `name`, `work_begin`, `work_end`, `work_days`, `start_begin`, `start_end`, `longitude`, `latitude`, `address`, `address_range`, `sb_set`, `late_range`, `remind_on`, `remind_off`, `leave_early_range`, `come_late_range`, `enable`, `range_on`, `remind_on_rage`, `remind_off_rage`, `sign_on`, `sign_off`, `late_work_time`, `absenteeism_range`, `min_work_hours`, `sign_start_range`, `sign_end_range`, `rule`, `type`, `late_range_on`, `late_work_time_on`, `come_late_range_on`, `absenteeism_range_on`, `flag`, `status`, `updated`, `deleted`, `created`) VALUES
(1, '默认班次', 1457917200, 1457949600, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3600, '上班了，快来签到吧~！', '下班了，快来签退吧~！', 600, 600, 1, 0, 600, 300, 1, 1, NULL, NULL, NULL, 3600, 7200, 1, 1, 1, 0, 1, 0, 0, 1, 0, 0, 1457922920);

INSERT INTO `{$prefix}sign_schedule{$suffix}` (`id`, `cd_id`, `sbid`, `enabled`, `schedule_begin_time`, `schedule_end_time`, `cycle_unit`, `cycle_num`, `schedule_everyday_detail`, `add_work_day`, `remove_day`, `range_on`, `address`, `address_range`, `longitude`, `latitude`, `status`, `created`, `updated`, `deleted`) VALUES
(1, 0, ',1,1,1,1,1,', 2, 1451577600, 0, 2, NULL, 'a:7:{i:0;a:1:{i:0;a:4:{s:4:"name";s:12:"默认班次";s:4:"type";s:1:"1";s:2:"id";s:1:"1";s:4:"time";s:21:"1457917200-1457949600";}}i:1;a:1:{i:0;a:4:{s:4:"name";s:12:"默认班次";s:4:"type";s:1:"1";s:2:"id";s:1:"1";s:4:"time";s:21:"1457917200-1457949600";}}i:2;a:1:{i:0;a:4:{s:4:"name";s:12:"默认班次";s:4:"type";s:1:"1";s:2:"id";s:1:"1";s:4:"time";s:21:"1457917200-1457949600";}}i:3;a:1:{i:0;a:4:{s:4:"name";s:12:"默认班次";s:4:"type";s:1:"1";s:2:"id";s:1:"1";s:4:"time";s:21:"1457917200-1457949600";}}i:4;a:1:{i:0;a:4:{s:4:"name";s:12:"默认班次";s:4:"type";s:1:"1";s:2:"id";s:1:"1";s:4:"time";s:21:"1457917200-1457949600";}}i:5;a:1:{i:0;a:2:{s:4:"name";s:6:"休息";s:4:"type";s:1:"2";}}i:6;a:1:{i:0;a:2:{s:4:"name";s:6:"休息";s:4:"type";s:1:"2";}}}', '', '', 0, NULL, NULL, NULL, NULL, 1, 1457923010, 1457923010, 0);

