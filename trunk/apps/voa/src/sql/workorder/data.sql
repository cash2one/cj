INSERT INTO `{$prefix}workorder_setting{$suffix}` (`key`, `value`, `type`, `comment`, `status`, `created`, `updated`, `deleted`) VALUES
('perpage', '10', 0, '分页数', 1, 0, 0, 0),
('complete_upload_count_min',	'1',	0,	'完成工单后最少要求上传的照片数',	1,	0,	0,	0),
('longlat_expire',	'900',	0,	'经纬度有效期，单位：秒',	1,	0,	0,	0),
('rule_address',	'a:2:{i:0;i:1;i:1;i:240;}',	1,	'联系地址的长度限制',	1,	0,	0,	0),
('rule_caption',	'a:2:{i:0;i:1;i:1;i:240;}',	1,	'执行完成的说明文字长度限制',	1,	0,	0,	0),
('rule_contacter',	'a:2:{i:0;i:0;i:1;i:32;}',	1,	'联系人的长度限制',	1,	0,	0,	0),
('rule_phone',	'a:2:{i:0;i:0;i:1;i:32;}',	1,	'联系电话的长度限制',	1,	0,	0,	0),
('rule_reason',	'a:2:{i:0;i:1;i:1;i:240;}',	1,	'操作原因的长度限制',	1,	0,	0,	0),
('rule_remark',	'a:2:{i:0;i:1;i:1;i:240;}',	1,	'工单备注说明的长度限制',	1,	0,	0,	0),
('send_upload_count_min',	'0',	0,	'派单时最少要求上传的附件数',	1,	0,	0,	0);
