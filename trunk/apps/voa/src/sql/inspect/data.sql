INSERT INTO `{$prefix}inspect_setting{$suffix}` (`is_key`, `is_value`, `is_type`, `is_comment`, `is_status`, `is_created`, `is_updated`, `is_deleted`) VALUES
('perpage', '10', 0, '分页数', 1, 0, 0, 0),
('pluginid', '20', 0, '插件id', 1, 0, 0, 0),
('rank_only_view_self', '1', 0, '是否只允许查看自己的门店排行', 1, 0, 0, 0),
('score_rules', 'a:4:{i:1;s:6:"合格";i:2;s:9:"不合格";i:3;s:9:"老问题";i:4;s:2:"NA";}', 1, '评估等级选项', 1, 0, 0, 0),
('score_rules_ignoreid', '4', '0', '忽略该项, 不计算', '1', '0', '0', '0'),
('score_rules_passid', '1', 0, '评估结果合格的id, 对应的评估选项的id', 1, 0, 0, 0),
('score_rule_diy', '0', 0, '是否自定义评估规则', 1, 0, 0, 0),
('score_title_describe', '现象描述', 0, '评分问题标题', 1, 0, 0, 0),
('score_title_mark', '评估结果', 0, '评分结果标题', 1, 0, 0, 0),
('score_title_standard', '评估标准', 0, '评估标准标题', 1, 0, 0, 0),
('title_city', '大区', 0, '城市别称', 1, 0, 0, 0),
('title_examinator', '区域经理', 0, '巡查者称呼', 1, 0, 0, 0),
('title_region', '小区', 0, '区的别称', 1, 0, 0, 0);