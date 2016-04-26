INSERT INTO `{$prefix}redpack_setting{$suffix}` (`key`, `value`, `type`, `comment`, `status`, `created`, `updated`, `deleted`) VALUES
('default_sender_avatar', '/misc/images/red-company-avatar.png', 0, '默认红包发送者头像URL', 2, 0, 1434471421, 0),
('default_sender_name', '畅移信息科技', 0, '默认红包发送者名称', 2, 0, 1434471421, 0),
('perpage', '30', 0, '每页记录数', 1, 0, 0, 0),
('pluginid', '32', 0, '插件id', 1, 0, 0, 0),
('privilege_uids', 'a:0:{}', 1, '有权限发送红包的用户uid', 2, 0, 1434471421, 0),
('redpack_max', '20000', 0, '红包最大值', 1, 0, 0, 0),
('redpack_min', '100', 0, '红包最小值', 1, 0, 0, 0),
('sign_redpack_id', '1', 0, '红包ID', 2, 0, 1434302769, 0),
('wxappid', '', 0, '微信企业号红包应用的 appid', 1, 0, 0, 0);

INSERT INTO `{$prefix}redpack{$suffix}` (`id`, `m_uid`, `m_username`, `actname`, `remark`, `type`, `total`, `left`, `redpacks`, `times`, `starttime`, `endtime`, `nickname`, `sendname`, `wishing`, `logoimgurl`, `sharecontent`, `shareurl`, `shareimgurl`, `min`, `max`, `highest`, `rule`, `status`, `created`, `updated`, `deleted`) VALUES
(1, 0, '', '扫码红包', '扫码即送红包', 1, 0, 0, 0, 0, 0, 0, '', '', '万事顺利', '', '', '', '', 100, 200, 0, '', 1, 0, 0, 0);
