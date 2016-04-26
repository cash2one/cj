INSERT INTO `{$prefix}vote_setting{$suffix}` (`vs_key`, `vs_value`, `vs_type`, `vs_comment`, `vs_status`, `vs_created`, `vs_updated`, `vs_deleted`) VALUES
('perpage', '10', 0, '每页分页数', 1, 0, 0, 0),
('verify', '0', 0, '是否需要审核验证, 0: 不需要; 1: 需要', 1, 0, 0, 0),
('verify_uid', '', 0, '审核管理员 uid, 多个用户以 "," 隔开', 1, 0, 0, 0),
('pluginid', '9', 0, '插件id', 1, 0, 0, 0);