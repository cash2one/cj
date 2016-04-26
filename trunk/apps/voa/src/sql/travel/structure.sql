CREATE TABLE IF NOT EXISTS `{$prefix}customer_class{$suffix}` (
  `classid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类id',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类id',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属表tid',
  `classname` varchar(255) NOT NULL DEFAULT '' COMMENT '产品名称',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`classid`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='产品分类表';


CREATE TABLE IF NOT EXISTS `{$prefix}customer_data{$suffix}` (
  `dataid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '数据id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户uid',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据表id',
  `truename` varchar(255) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `avatarid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '头像的附件id值',
  `classid` mediumint(9) NOT NULL DEFAULT '0' COMMENT '产品分类id',
  `mobile` varchar(12) NOT NULL DEFAULT '' COMMENT '手机号码',
  `gender` tinyint(4) NOT NULL DEFAULT '0' COMMENT '性别',
  `customst` mediumint(9) NOT NULL DEFAULT '0' COMMENT '客户进度',
  `proto_1` varchar(255) NOT NULL DEFAULT '' COMMENT '搜索字段信息',
  `proto_2` varchar(255) NOT NULL DEFAULT '' COMMENT '搜索字段信息',
  `proto_3` varchar(255) NOT NULL DEFAULT '' COMMENT '搜索字段信息',
  `proto_4` varchar(255) NOT NULL DEFAULT '' COMMENT '搜索字段信息',
  `diys` text NOT NULL COMMENT '非索引的自定义信息, serialize 字串',
  `message` text NOT NULL COMMENT '备注信息',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`dataid`),
  KEY `status` (`status`),
  KEY `uid` (`uid`),
  KEY `tid` (`tid`),
  KEY `updated` (`updated`),
  KEY `proto_1` (`proto_1`),
  KEY `proto_4` (`proto_4`),
  KEY `proto_3` (`proto_3`),
  KEY `proto_2` (`proto_2`),
  KEY `subject` (`truename`),
  KEY `classid` (`classid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='数据表基本数据';


CREATE TABLE IF NOT EXISTS `{$prefix}customer_goods{$suffix}` (
  `cgid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `custom_tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户所在的表格 tid',
  `custom_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户id',
  `goods_tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品所处的表格 tid',
  `goods_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据状态, 1: 新建; 2: 更新; 3: 删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cgid`),
  KEY `customid` (`custom_id`),
  KEY `goodsid` (`goods_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='客户和商品对照表';


CREATE TABLE IF NOT EXISTS `{$prefix}customer_table{$suffix}` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'uid, 为 0 时, 是系统默认表',
  `cp_identifier` varchar(255) NOT NULL DEFAULT '' COMMENT '插件唯一标识',
  `tunique` varchar(30) NOT NULL DEFAULT '' COMMENT '表格唯一标识',
  `tname` varchar(60) NOT NULL DEFAULT '' COMMENT '数据表格名称',
  `t_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '数据表格描述',
  `status` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tid`),
  KEY `status` (`status`),
  KEY `uid` (`uid`),
  KEY `cp_identifier` (`cp_identifier`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='数据表格的扩展表';


CREATE TABLE IF NOT EXISTS `{$prefix}customer_tablecol{$suffix}` (
  `tc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '字段自增id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'uid',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据表格id',
  `field` varchar(60) NOT NULL DEFAULT '' COMMENT '字段名称',
  `fieldalias` varchar(255) NOT NULL DEFAULT '' COMMENT '字段别名',
  `fieldname` varchar(255) NOT NULL DEFAULT '' COMMENT '字段名称显示',
  `placeholder` varchar(255) NOT NULL DEFAULT '' COMMENT '输入提示文字',
  `tc_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '数据字段描述',
  `ct_type` varchar(255) NOT NULL DEFAULT '' COMMENT '字段类型, 为 columntype 表的 ct_type',
  `ftype` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '子类型, 文本类型(1: 多行文本; 2: 富文本); 附件类型(1: 文本文件; 2: 图片文件; 4: 语音文件; 8: 视频文件); 单选/复选(1:文本, 2:图片)',
  `min` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最小值或长度',
  `max` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最大值或长度',
  `reg_exp` varchar(255) NOT NULL DEFAULT '' COMMENT '正则表达式',
  `initval` varchar(255) NOT NULL DEFAULT '' COMMENT '该字段的默认值',
  `unit` varchar(255) NOT NULL DEFAULT '' COMMENT '单位名称',
  `orderid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序序号, 越大越靠前',
  `required` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否必填, 1:必填; 0:选填',
  `tpladd` varchar(255) NOT NULL DEFAULT '' COMMENT '输入模板',
  `isuse` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用, 1: 启用; 2未启用',
  `coltype` tinyint(3) unsigned NOT NULL DEFAULT '2' COMMENT '字段类型, 1: 系统; 2:自定义',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '字段状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tc_id`),
  KEY `status` (`status`),
  KEY `tid` (`tid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='数据表字段信息';


CREATE TABLE IF NOT EXISTS `{$prefix}customer_tablecolopt{$suffix}` (
  `tco_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '选项id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'uid',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '表格tid',
  `tc_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '表格列tcid',
  `value` varchar(255) NOT NULL DEFAULT '' COMMENT '可选项值',
  `attachid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件id',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tco_id`),
  KEY `tid` (`tid`),
  KEY `tc_id` (`tc_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='表格列的可选项值';


CREATE TABLE IF NOT EXISTS `{$prefix}goods_attach{$suffix}` (
  `gaid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属表tid',
  `dataid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `at_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件id',
  `attype` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '附件类型, 1: 封面图; 2: 幻灯片; 3: 详情中的图片',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常; 2=已删除;',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`gaid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商品的附件表';


CREATE TABLE IF NOT EXISTS `{$prefix}goods_class{$suffix}` (
  `classid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类id',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类id',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属表tid',
  `classname` varchar(255) NOT NULL DEFAULT '' COMMENT '产品名称',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`classid`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='产品分类表';


CREATE TABLE IF NOT EXISTS `{$prefix}goods_data{$suffix}` (
  `dataid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '数据id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户uid',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据表id',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '产品名称',
  `classid` mediumint(9) NOT NULL DEFAULT '0' COMMENT '产品分类id',
  `percentage` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提成百分比',
  `proto_1` varchar(255) NOT NULL DEFAULT '' COMMENT '搜索字段信息',
  `proto_2` varchar(255) NOT NULL DEFAULT '' COMMENT '搜索字段信息',
  `proto_3` varchar(255) NOT NULL DEFAULT '' COMMENT '搜索字段信息',
  `proto_4` varchar(255) NOT NULL DEFAULT '' COMMENT '搜索字段信息',
  `proto_5` varchar(255) NOT NULL DEFAULT '' COMMENT '搜索字段信息',
  `proto_6` varchar(255) NOT NULL DEFAULT '' COMMENT '搜索字段信息',
  `diys` text NOT NULL COMMENT '非索引的自定义信息, serialize 字串',
  `message` text NOT NULL COMMENT '详情信息',
  `fodder_img`  varchar(255)  NULL DEFAULT NULL COMMENT '素材图片',
  `fodder_sub`  varchar(255)  NULL DEFAULT NULL COMMENT '素材描述',
  `fodder_link`  varchar(255) NULL DEFAULT NULL COMMENT '产品短链',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`dataid`),
  KEY `status` (`status`),
  KEY `uid` (`uid`),
  KEY `tid` (`tid`),
  KEY `updated` (`updated`),
  KEY `proto_1` (`proto_1`),
  KEY `proto_4` (`proto_4`),
  KEY `proto_3` (`proto_3`),
  KEY `proto_2` (`proto_2`),
  KEY `subject` (`subject`),
  KEY `classid` (`classid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='数据表基本数据';


CREATE TABLE IF NOT EXISTS `oa_goods_express` (
  `expid` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(10) NOT NULL DEFAULT '0',
  `exptype` varchar(255) NOT NULL COMMENT '快递类型',
  `expcost` float NOT NULL COMMENT '快递费用',
  `status` smallint(5) NOT NULL DEFAULT '1' COMMENT '数据状态, 1初始化，2=已更新, 3=已删除',
  `created` int(11) NOT NULL DEFAULT '0',
  `updated` int(10) NOT NULL DEFAULT '0',
  `deleted` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`expid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='快递表';


CREATE TABLE IF NOT EXISTS `{$prefix}goods_table{$suffix}` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'uid, 为 0 时, 是系统默认表',
  `cp_identifier` varchar(255) NOT NULL DEFAULT '' COMMENT '插件唯一标识',
  `tunique` varchar(30) NOT NULL DEFAULT '' COMMENT '表格唯一标识',
  `tname` varchar(60) NOT NULL DEFAULT '' COMMENT '数据表格名称',
  `t_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '数据表格描述',
  `status` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tid`),
  KEY `status` (`status`),
  KEY `uid` (`uid`),
  KEY `cp_identifier` (`cp_identifier`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='数据表格的扩展表';


CREATE TABLE IF NOT EXISTS `{$prefix}goods_tablecol{$suffix}` (
  `tc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '字段自增id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'uid',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据表格id',
  `field` varchar(60) NOT NULL DEFAULT '' COMMENT '字段名称',
  `fieldalias` varchar(255) NOT NULL DEFAULT '' COMMENT '别名, 返回值时, 用于代替 field',
  `fieldname` varchar(255) NOT NULL DEFAULT '' COMMENT '字段名称显示',
  `placeholder` varchar(255) NOT NULL DEFAULT '' COMMENT '输入提示文字',
  `tc_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '数据字段描述',
  `ct_type` varchar(255) NOT NULL DEFAULT '' COMMENT '字段类型, 为 columntype 表的 ct_type',
  `ftype` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '子类型, 文本类型(1: 多行文本; 2: 富文本); 附件类型(1: 文本文件; 2: 图片文件; 4: 语音文件; 8: 视频文件); 单选/复选(1:文本, 2:图片)',
  `min` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最小值或长度',
  `max` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最大值或长度',
  `reg_exp` varchar(255) NOT NULL DEFAULT '' COMMENT '正则表达式',
  `initval` varchar(255) NOT NULL DEFAULT '' COMMENT '该字段的默认值',
  `unit` varchar(255) NOT NULL DEFAULT '' COMMENT '单位名称',
  `orderid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序序号, 越大越靠前',
  `required` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否必填, 1:必填; 0:选填',
  `tpladd` varchar(255) NOT NULL DEFAULT '' COMMENT '输入模板',
  `isuse` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用, 1: 启用; 2: 隐藏; 3: 未启用',
  `coltype` tinyint(3) unsigned NOT NULL DEFAULT '2' COMMENT '字段类型, 1: 系统; 2:自定义',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '字段状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tc_id`),
  KEY `status` (`status`),
  KEY `tid` (`tid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='数据表字段信息';


CREATE TABLE IF NOT EXISTS `{$prefix}goods_tablecolopt{$suffix}` (
  `tco_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '选项id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'uid',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '表格tid',
  `tc_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '表格列tcid',
  `value` varchar(255) NOT NULL DEFAULT '' COMMENT '可选项值',
  `attachid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件id',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tco_id`),
  KEY `tid` (`tid`),
  KEY `tc_id` (`tc_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='表格列的可选项值';


CREATE TABLE IF NOT EXISTS `{$prefix}mpuser{$suffix}` (
  `mpuid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户UID',
  `openid` char(32) NOT NULL DEFAULT '' COMMENT '唯一键值',
  `saleid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属销售的 m_uid',
  `web_access_token` varchar(255) NOT NULL DEFAULT '' COMMENT 'web access token',
  `web_token_expires` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'web token expires',
  `mobilephone` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `email` char(80) NOT NULL DEFAULT '' COMMENT '邮箱',
  `unionid` char(32) NOT NULL DEFAULT '' COMMENT '微信unionid',
  `username` char(54) NOT NULL DEFAULT '' COMMENT '姓名',
  `index` char(4) NOT NULL DEFAULT '' COMMENT '名字字母索引字符，同通讯录cab_index',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '用户密码',
  `groupid` mediumint(9) NOT NULL DEFAULT '0' COMMENT '用户组ID',
  `gender` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别, 0: 未知, 1:男; 2:女',
  `face` char(255) NOT NULL DEFAULT '' COMMENT '用户头像',
  `facetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '头像更新时间',
  `salt` char(6) NOT NULL DEFAULT '' COMMENT '密码干扰串',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新, 3=待验证，4=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`mpuid`),
  UNIQUE KEY `openid` (`openid`),
  KEY `status` (`status`),
  KEY `mobilephone` (`mobilephone`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户表';


CREATE TABLE IF NOT EXISTS `{$prefix}order{$suffix}` (
  `orderid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ordersn` char(20) DEFAULT NULL COMMENT '订单编号',
  `wx_orderid` char(40) DEFAULT NULL COMMENT '微信订单号',
  `amount` int(10) unsigned NOT NULL COMMENT '总额(以分为单位)',
  `profit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提成(分)',
  `sale_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '售销id',
  `sale_name` char(10) DEFAULT '' COMMENT '员工姓名',
  `sale_phone` char(15) DEFAULT '' COMMENT '销售电话',
  `sale_memo` varchar(255) DEFAULT NULL COMMENT '销售备注',
  `order_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '订单状态(1待支付,2.支付中,3已支付,4已发货,9已完成/已经签收,20已取消,30已失效,40支付失败)',
  `customer_id` int(10) unsigned DEFAULT '0' COMMENT '客户id',
  `customer_name` char(20) DEFAULT '' COMMENT '客户名称',
  `customer_openid` char(32) DEFAULT NULL COMMENT '客户微信开放id',
  `address` varchar(255) DEFAULT NULL COMMENT '客户地址',
  `mobile` char(15) DEFAULT NULL COMMENT '手机/电话',
  `customer_memo` varchar(255) DEFAULT NULL COMMENT '客户备注',
  `pay_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `complete_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签收时间(完成时间)',
  `ship_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发货时间',
  `expid` int(10) NOT NULL DEFAULT '0' COMMENT '快递ID',
  `express` varchar(255) DEFAULT NULL COMMENT '快递名称',
  `expressn` varchar(20) DEFAULT NULL COMMENT '快递单号',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`orderid`),
  UNIQUE KEY `ordersn` (`ordersn`),
  KEY `sale_id` (`sale_id`),
  KEY `customer_id` (`customer_id`),
  KEY `status` (`status`),
  KEY `order_status` (`order_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='订单表';


CREATE TABLE IF NOT EXISTS `{$prefix}order_cart{$suffix}` (
  `cartid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `openid` char(32) NOT NULL COMMENT '微信openid',
  `goods_id` int(10) unsigned NOT NULL COMMENT '产品id',
  `goods_name` varchar(255) NOT NULL COMMENT '产品名称',
  `cd_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销售所属部门id',
  `saleuid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销售uid',
  `salename` varchar(20) NOT NULL DEFAULT '' COMMENT '销售名称',
  `style_id` int(10) unsigned DEFAULT NULL COMMENT '规格id',
  `style_name` varchar(255) DEFAULT NULL COMMENT '规格',
  `num` int(10) unsigned DEFAULT '1' COMMENT '数量',
  `price` int(10) unsigned DEFAULT NULL COMMENT '价格(单位:分)',
  `scale` int(10) unsigned NOT NULL COMMENT '提成百分比',
  `profit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收益(分)',
  `status` tinyint(1) DEFAULT '1' COMMENT '数据状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`cartid`),
  KEY `openid` (`openid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='购物车';


CREATE TABLE IF NOT EXISTS `{$prefix}order_goods{$suffix}` (
  `ogid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` char(32) NOT NULL COMMENT '订单id',
  `goods_id` int(10) unsigned NOT NULL COMMENT '产品id',
  `goods_name` varchar(255) NOT NULL COMMENT '产品名称',
  `cd_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销售所属部门id',
  `saleuid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销售uid',
  `salename` varchar(20) NOT NULL DEFAULT '' COMMENT '销售名称',
  `style_id` int(10) unsigned DEFAULT '0' COMMENT '规格id',
  `style_name` varchar(255) DEFAULT NULL COMMENT '规格名称',
  `num` int(10) unsigned DEFAULT '1' COMMENT '数量',
  `price` int(10) unsigned DEFAULT NULL COMMENT '价格(单位:分)',
  `scale` int(10) unsigned NOT NULL COMMENT '价格(单位:分)',
  `profit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收益(分)',
  `status` tinyint(1) DEFAULT '1' COMMENT '数据状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`ogid`),
  KEY `order_id` (`order_id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='订单产品表';


CREATE TABLE IF NOT EXISTS `{$prefix}order_log{$suffix}` (
  `logid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL COMMENT '订单id',
  `old_status` tinyint(2) unsigned NOT NULL COMMENT '旧状态',
  `new_status` tinyint(2) unsigned NOT NULL COMMENT '新状态',
  `oper_id` int(10) unsigned NOT NULL COMMENT '操作管理员uid',
  `oper_name` char(20) DEFAULT NULL COMMENT '操作管理员用户名',
  `memo` varchar(255) NOT NULL COMMENT '原因',
  `status` tinyint(1) DEFAULT '1' COMMENT '数据状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL COMMENT '操作时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`logid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='订单操作日志';


CREATE TABLE IF NOT EXISTS `{$prefix}sale{$suffix}` (
  `saleid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(20) DEFAULT NULL COMMENT '姓名',
  `phone` char(20) DEFAULT NULL COMMENT '电话',
  `adr` varchar(255) DEFAULT NULL COMMENT '地址',
  `memo` varchar(255) DEFAULT NULL COMMENT '申请理由',
  `sale_status` tinyint(1) unsigned DEFAULT '0' COMMENT '销售状态(0申请中,1已通过)',
  `status` tinyint(1) DEFAULT '1' COMMENT '数据状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`saleid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='直销员';


CREATE TABLE IF NOT EXISTS `{$prefix}talk_lastview{$suffix}` (
  `cs_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销售uid',
  `tv_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '目标客户uid',
  `newct` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新的未读聊天记录',
  `lastts` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `goodsid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品id',
  `viewts` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看时间',
  `message` text NOT NULL COMMENT '最后一条消息内容',
  `status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据状态, 1: 创建; 2: 更新; 3: 删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cs_id`),
  KEY `uid` (`uid`),
  KEY `tv_uid` (`tv_uid`),
  KEY `lastts` (`lastts`),
  KEY `newct` (`newct`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='聊天记录最后查看时间';


CREATE TABLE IF NOT EXISTS `{$prefix}talk_viewer{$suffix}` (
  `tv_uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `username` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `ip` char(15) NOT NULL COMMENT 'IP地址',
  `status` smallint(6) NOT NULL DEFAULT '1' COMMENT '数据状态, 1: 创建; 2: 更新; 3: 删除',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tv_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='访客信息';


CREATE TABLE IF NOT EXISTS `{$prefix}talk_wechat{$suffix}` (
  `tw_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '目标uid',
  `tv_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '目标客户uid',
  `tw_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '发言类型, 1: 访客发言, 2: sales发言',
  `message` text NOT NULL COMMENT '聊天信息',
  `status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据状态, 1: 创建; 2: 更新; 3: 删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tw_id`),
  KEY `to_uid` (`uid`),
  KEY `tv_uid` (`tv_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='聊天记录';


CREATE TABLE IF NOT EXISTS `{$prefix}travel_customer_goods{$suffix}` (
  `cgid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户uid',
  `customer_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户id',
  `goods_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据状态, 1: 新建; 2: 更新; 3: 删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cgid`),
  KEY `customid` (`customer_id`),
  KEY `goodsid` (`goods_id`),
  KEY `status` (`status`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='客户和商品对照表';


CREATE TABLE IF NOT EXISTS `{$prefix}travel_customer_remark{$suffix}` (
  `crk_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '备注id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户uid',
  `customer_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户id',
  `message` text NOT NULL COMMENT '备注详情',
  `attachids` varchar(255) NOT NULL DEFAULT '' COMMENT '附件id, 多个id以 "," 分隔',
  `duration` varchar(255) NOT NULL DEFAULT '' COMMENT '语音的时长, 多个以 "," 分隔',
  `remindts` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提醒时间',
  `crk_type` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '备注类型, 1: 文字; 2: 图片; 3: 语音; 4: 提醒',
  `status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据状态, 1: 创建; 2: 已更新; 3: 已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`crk_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='客户备注';


CREATE TABLE IF NOT EXISTS `{$prefix}travel_diyindex{$suffix}` (
  `tiid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `subject` varchar(255) NOT NULL COMMENT '标题',
  `message` text NOT NULL COMMENT '首页内容, 序列化字串',
  `related` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否关联, 0: 未关联; 1: 已关联;',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户uid',
  `username` varchar(45) NOT NULL DEFAULT '' COMMENT '用户名称',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '数据状态',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tiid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='自定义主页';


CREATE TABLE IF NOT EXISTS `{$prefix}travel_material{$suffix}` (
  `mtid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '素材id',
  `subject` varchar(255) NOT NULL COMMENT '素材标题',
  `message` text NOT NULL COMMENT '素材内容',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1: 新建; 2: 更新; 3: 删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`mtid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='素材表';


CREATE TABLE IF NOT EXISTS `{$prefix}travel_mem2goods{$suffix}` (
  `mgid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户uid',
  `username` varchar(40) NOT NULL COMMENT '用户名',
  `dataid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品id',
  `fav` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否收藏, 1: 收藏; 0: 不收藏',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1: 新建; 2: 更新; 3: 删除;',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`mgid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户和产品对照表;';


CREATE TABLE IF NOT EXISTS `{$prefix}travel_setting{$suffix}` (
  `skey` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`skey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品设置表';


CREATE TABLE IF NOT EXISTS `{$prefix}travel_share_count{$suffix}` (
  `tsc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户uid',
  `goods_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品id',
  `sig` char(40) NOT NULL DEFAULT '' COMMENT '分享唯一标识',
  `viewcount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '统计总数',
  `inquirycount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '咨询次数',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态: 1: 创建; 2: 更新; 3: 删除;',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`tsc_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='产品分享统计';


CREATE TABLE IF NOT EXISTS `{$prefix}travel_styles{$suffix}` (
  `styleid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '款式id',
  `goodsid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品id',
  `stylename` varchar(255) NOT NULL DEFAULT '' COMMENT '款式名称',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT '数量',
  `price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '价格, 单位: 分',
  `state` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '规格状态, 1: 使用中; 2: 未启用;',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态, 1: 创建; 2: 更新; 3: 删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`styleid`),
  KEY `goodsid` (`goodsid`),
  KEY `status` (`status`),
  KEY `state` (`state`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='产品规格表';


CREATE TABLE IF NOT EXISTS `{$prefix}goods_express{$suffix}` (
  `expid` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(10) NOT NULL DEFAULT '0',
  `exptype` varchar(255) NOT NULL COMMENT '快递类型',
  `expcost` float NOT NULL COMMENT '快递费用',
  `status` smallint(5) NOT NULL DEFAULT '1' COMMENT '数据状态, 1初始化，2=已更新, 3=已删除',
  `created` int(11) NOT NULL DEFAULT '0',
  `updated` int(10) NOT NULL DEFAULT '0',
  `deleted` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`expid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='快递表';
