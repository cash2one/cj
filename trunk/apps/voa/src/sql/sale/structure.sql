CREATE TABLE IF NOT EXISTS `{$prefix}sale_business{$suffix}` (
  `bid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '商机表主键',
  `scid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户表主键',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销售人员id',
  `title` varchar(300) NOT NULL DEFAULT '' COMMENT '机会名称',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态：1初步沟通（10）2立项跟踪（30）3呈报方案（50）4商务谈判（80）5赢单（100）6输单（0）',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '预计销售金额',
  `content` text NOT NULL COMMENT '备注',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`bid`),
  KEY `idx_scid_type` (`scid`,`type`) USING BTREE,
  KEY `idx_scid` (`scid`) USING BTREE,
  KEY `idx_type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商机表';

CREATE TABLE IF NOT EXISTS `{$prefix}sale_coustmer{$suffix}` (
  `scid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '客户表主键',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '联系人',
  `company` varchar(40) NOT NULL DEFAULT '' COMMENT '公司名称（全称）',
  `companyshortname` varchar(10) NOT NULL DEFAULT '' COMMENT '公司简称',
  `address` varchar(100) NOT NULL DEFAULT '' COMMENT '公司地址',
  `phone` varchar(30) NOT NULL DEFAULT '' COMMENT '联系电话',
  `cm_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当前销售人员ID，用来限制别的销售人员跟踪这个客户',
  `sale_name` varchar(10) NOT NULL DEFAULT '' COMMENT '当前跟踪人姓名',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销售人员',
  `source_stid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来源ID目前有1.市场活动2.百度推广3.谷歌推广4.网上查找5.公司分配6.其他',
  `source` varchar(10) NOT NULL DEFAULT '' COMMENT '来源名称',
  `type_stid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户状态ID,0是新增客户，其他id是用户自定义',
  `type` varchar(10) NOT NULL DEFAULT '' COMMENT '客户状态名称',
  `color` varchar(10) NOT NULL DEFAULT '' COMMENT '色块颜色',
  `sfields` text NOT NULL COMMENT '新增的字段信息序列化字符串。array(stid=>name,stid=>name,..)',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`scid`),
  KEY `idx_ssid` (`source_stid`) USING BTREE,
  KEY `idx_stid` (`type_stid`) USING BTREE,
  KEY `idx_name_phone` (`name`, `phone`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='客户表';

CREATE TABLE IF NOT EXISTS `{$prefix}sale_setting{$suffix}` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='销售管理设置表';

CREATE TABLE IF NOT EXISTS `{$prefix}sale_trajectory{$suffix}` (
  `strid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '轨迹id',
  `scid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户id',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销售人员id',
  `stid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户状态id 0是新增客户id',
  `source` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来源ID',
  `type` varchar(10) NOT NULL DEFAULT '' COMMENT '客户状态',
  `color` varchar(10) NOT NULL DEFAULT '' COMMENT '色块颜色',
  `content` text NOT NULL COMMENT '工作日报',
  `present_address` varchar(100) NOT NULL DEFAULT '' COMMENT '当前位置',
  `at_ids` varchar(50) NOT NULL DEFAULT '' COMMENT '图片：以逗号分隔。',
  `longitude` decimal(10,6) NOT NULL DEFAULT '0.000000' COMMENT '经度',
  `latitude` decimal(10,6) NOT NULL DEFAULT '0.000000' COMMENT '纬度',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`strid`),
  KEY `idx_scid` (`scid`) USING BTREE,
  KEY `idx_m_uid` (`m_uid`) USING BTREE,
  KEY `idx_stid` (`stid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='轨迹表';

CREATE TABLE IF NOT EXISTS `{$prefix}sale_type{$suffix}` (
  `stid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '客户自定义表主键',
  `name` char(10) NOT NULL DEFAULT '' COMMENT '字段名称',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '分类：1.自定义字段 2.自定义状态 3.自定义来源',
  `color` varchar(10) NOT NULL DEFAULT '' COMMENT '色块颜色',
  `required` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否必填，在type为1有用，默认为空',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`stid`),
  KEY `type` (`type`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='客户自定义表主键';