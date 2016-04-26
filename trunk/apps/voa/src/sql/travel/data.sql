INSERT INTO `{$prefix}customer_table{$suffix}` (`tid`, `uid`, `cp_identifier`, `tunique`, `tname`, `t_desc`, `status`, `created`, `updated`, `deleted`) VALUES
(1, 1, 'travel', 'customer', '客户表', '客户描述', 1, 0, 0, 0);

INSERT INTO `{$prefix}customer_tablecol{$suffix}` (`tc_id`, `uid`, `tid`, `field`, `fieldalias`, `fieldname`, `placeholder`, `tc_desc`, `ct_type`, `ftype`, `min`, `max`, `reg_exp`, `initval`, `unit`, `orderid`, `required`, `tpladd`, `isuse`, `coltype`, `status`, `created`, `updated`, `deleted`) VALUES
(1, 1, 1, 'truename', '', '姓名', '', '', 'varchar', 0, 1, 80, '', '', '', 2, 1, '', 1, 1, 2, 0, 1419472109, 0),
(2, 1, 1, 'mobile', '', '手机', '', '', 'mobile', 0, 0, 0, '', '', '', 3, 1, '', 1, 1, 2, 0, 1419472109, 0),
(3, 1, 1, 'gender', '', '性别', '', '', 'select', 0, 0, 0, '', '', '', 0, 0, '', 1, 1, 2, 0, 1419472109, 0),
(4, 1, 1, '', 'ages', '年龄', '', '', 'int', 0, 0, 0, '', '', '', 4, 0, '', 1, 1, 2, 0, 1419472109, 0);

INSERT INTO `{$prefix}customer_tablecolopt{$suffix}` (`tco_id`, `uid`, `tid`, `tc_id`, `value`, `attachid`, `status`, `created`, `updated`, `deleted`) VALUES
(1, 1, 1, 3, '未知', 0, 1, 0, 0, 0),
(2, 1, 1, 3, '先生', 0, 2, 0, 1418723416, 0),
(3, 1, 1, 3, '女士', 0, 2, 0, 1418723418, 0);

INSERT INTO `{$prefix}goods_table{$suffix}` (`tid`, `uid`, `cp_identifier`, `tunique`, `tname`, `t_desc`, `status`, `created`, `updated`, `deleted`) VALUES
(1, 1, 'travel', 'goods', '商品表', '商品描述', 1, 0, 0, 0);

INSERT INTO `{$prefix}goods_tablecol{$suffix}` (`tc_id`, `uid`, `tid`, `field`, `fieldalias`, `fieldname`, `placeholder`, `tc_desc`, `ct_type`, `ftype`, `min`, `max`, `reg_exp`, `initval`, `unit`, `orderid`, `required`, `tpladd`, `isuse`, `coltype`, `status`, `created`, `updated`, `deleted`) VALUES
(1, 1, 1, 'subject', '', '产品名称', '', '产品名称说明', 'varchar', 0, 0, 255, '', '', '', 3, 1, '', 1, 1, 2, 0, 1419494899, 0),
(2, 1, 1, 'message', '', '产品详情', '', '', 'text', 2, 0, 0, '', '', '', 7, 0, '', 1, 1, 2, 0, 1419490902, 0),
(3, 1, 1, 'proto_2', 'price', '价格', '', '', 'float', 0, 0, 0, '', '', '元', 1, 1, '', 1, 1, 2, 0, 1419490902, 0),
(4, 1, 1, '', 'fintime', '结束时间', '', '', 'date', 0, 0, 0, '', '', '', 2, 1, '', 1, 1, 2, 0, 1419491238, 0),
(5, 1, 1, 'proto_3', 'goodsnum', '编号', '', '', 'varchar', 0, 0, 255, '', '', '', 0, 0, '', 1, 1, 2, 0, 1419500259, 0),
(6, 1, 1, '', '', '简介', '', '', 'text', 1, 0, 0, '', '', '', 5, 0, '', 1, 1, 2, 0, 1419490902, 0),
(7, 1, 1, 'proto_4', 'recommend', '推荐理由', '', '', 'radio', 2, 0, 0, '', '', '', 8, 0, '', 3, 1, 2, 0, 1419502930, 0),
(8, 1, 1, '', 'cover', '封面', '', '', 'attach', 2, 0, 1, '', '', '', 6, 1, '', 1, 1, 2, 0, 1419490902, 0),
(9, 1, 1, '', 'slide', '幻灯片', '', '', 'attach', 0, 0, 5, '', '', '', 10, 1, '', 1, 1, 2, 0, 1419493655, 0),
(10, 1, 1, 'proto_5', 'saledcount', '售出总数', '', '', 'int', 0, 0, 0, '', '', '', 1, 0, '', 1, 1, 2, 0, 1419490902, 0),
(11, 1, 1, 'percentage', '', '提成比例', '', '销售提成比例', 'int', 0, 0, 100, '', '', '%', 5, 0, '', 1, 1, 2, 0, 1422076139, 0),
(12, 1, 1, 'fodder_img', '', '素材图片', '', '', 'attach', 2, 0, 12, '', '', '', 13, 0, '', 1, 1, 1, 0, 1422076139, 0),
(13, 1, 1, 'fodder_sub', '', '素材描述', '', '', 'text', 1, 0, 200, '', '', '', 14, 0, '', 1, 1, 1, 0, 1422076139, 0);


INSERT INTO `{$prefix}travel_diyindex{$suffix}` (`tiid`, `subject`, `message`, `related`, `uid`, `username`, `status`, `created`, `updated`, `deleted`) VALUES
(1, '通用首页', 'a:0:{}', 0, 0, '', 2, 0, 1429008462, 0);

INSERT INTO `{$prefix}travel_setting{$suffix}` (`skey`, `value`, `type`, `comment`, `status`, `created`, `updated`, `deleted`) VALUES
('customer_table_name', 'customer', 0, '客户表格名称', 1, 0, 0, 0),
('goods_table_name', 'goods', 0, '旅游商品表格名称', 1, 0, 0, 0),
('goods_tpl_style', 'crm', 0, '产品模板, travel: 旅游; crm: 在线商品;', 2, 0, 1423703015, 0),
('perpage', '10', 0, '分页数', 1, 0, 0, 0),
('pluginid', '24', 0, '插件id', 2, 0, 1406094491, 0);





