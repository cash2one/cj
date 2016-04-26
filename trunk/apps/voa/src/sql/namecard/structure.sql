CREATE TABLE IF NOT EXISTS `{$prefix}namecard{$suffix}` (
  `nc_id` mediumint(9) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `ncf_id` int(10) unsigned NOT NULL COMMENT '名片夹群组id',
  `ncc_id` int(10) unsigned NOT NULL COMMENT '公司id',
  `ncj_id` int(10) unsigned NOT NULL COMMENT '职业id',
  `nc_realname` varchar(50) NOT NULL COMMENT '真实姓名',
  `nc_pinyin` varchar(120) NOT NULL COMMENT '用户名称的汉字拼音, 多个汉字之间用 "," 隔开',
  `nc_mobilephone` varchar(12) NOT NULL COMMENT '手机号码',
  `nc_wxuser` varchar(40) NOT NULL COMMENT '微信号',
  `nc_address` varchar(255) NOT NULL COMMENT '住址',
  `nc_gender` tinyint(1) unsigned NOT NULL COMMENT '性别，0未设置，1男，2女',
  `nc_active` tinyint(1) unsigned NOT NULL COMMENT '在职状态，1在职，0离职',
  `nc_telephone` varchar(12) NOT NULL COMMENT '电话号码',
  `nc_email` varchar(40) NOT NULL COMMENT '邮箱',
  `nc_qq` varchar(12) NOT NULL COMMENT 'QQ',
  `nc_birthday` date NOT NULL COMMENT '生日',
  `nc_postcode` varchar(6) NOT NULL COMMENT '邮编',
  `nc_remark` varchar(255) NOT NULL COMMENT '备注',
  `at_id` int(10) unsigned NOT NULL COMMENT '附件id',
  `nc_displayorder` mediumint(8) unsigned NOT NULL COMMENT '排序值, 越大越靠前',
  `nc_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `nc_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `nc_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `nc_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`nc_id`),
  KEY `m_uid` (`m_uid`,`ncf_id`),
  KEY `nc_mobilephone` (`m_uid`,`nc_mobilephone`),
  KEY `nc_status` (`nc_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='名片详情';


CREATE TABLE IF NOT EXISTS `{$prefix}namecard_company{$suffix}` (
  `ncc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `ncc_name` varchar(30) NOT NULL COMMENT '公司名称',
  `ncc_num` smallint(6) NOT NULL COMMENT '当前成员数',
  `ncc_displayorder` mediumint(8) unsigned NOT NULL COMMENT '显示顺序, 越大越靠前',
  `ncc_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `ncc_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `ncc_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `ncc_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`ncc_id`),
  KEY `ncc_status` (`ncc_status`),
  KEY `ncc_displayorder` (`ncc_displayorder`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='名片的公司表';


CREATE TABLE IF NOT EXISTS `{$prefix}namecard_folder{$suffix}` (
  `ncf_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `ncf_name` varchar(30) NOT NULL COMMENT '群组名称',
  `ncf_num` smallint(6) NOT NULL COMMENT '当前成员数',
  `ncf_displayorder` mediumint(8) unsigned NOT NULL COMMENT '显示顺序, 越大越靠前',
  `ncf_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `ncf_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `ncf_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `ncf_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`ncf_id`),
  KEY `ncf_status` (`ncf_status`),
  KEY `ncf_displayorder` (`ncf_displayorder`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='名片的群组表';


CREATE TABLE IF NOT EXISTS `{$prefix}namecard_job{$suffix}` (
  `ncj_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `ncj_name` varchar(30) NOT NULL COMMENT '职位名称',
  `ncj_num` smallint(5) NOT NULL COMMENT '当前成员数',
  `ncj_displayorder` mediumint(8) unsigned NOT NULL COMMENT '显示顺序, 越大越靠前',
  `ncj_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `ncj_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `ncj_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `ncj_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`ncj_id`),
  KEY `ncj_status` (`ncj_status`),
  KEY `ncj_displayorder` (`ncj_displayorder`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='名片的职业表';


CREATE TABLE IF NOT EXISTS `{$prefix}namecard_search{$suffix}` (
  `ncso_id` mediumint(9) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `nc_id` int(10) unsigned NOT NULL COMMENT '名片ID',
  `ncso_message` text NOT NULL COMMENT '名片信息集合',
  `ncso_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `ncso_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `ncso_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `ncso_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`ncso_id`),
  KEY `ncso_status` (`ncso_status`),
  KEY `m_uid` (`m_uid`,`nc_id`),
  FULLTEXT KEY `ncso_message` (`ncso_message`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='名片详情搜索表';


CREATE TABLE IF NOT EXISTS `{$prefix}namecard_setting{$suffix}` (
  `ncs_key` varchar(50) NOT NULL COMMENT '变量名',
  `ncs_value` text NOT NULL COMMENT '值',
  `ncs_type` tinyint(3) unsigned NOT NULL COMMENT '缓存类型, 0:非数组, 1:数组',
  `ncs_comment` text NOT NULL COMMENT '说明',
  `ncs_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `ncs_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `ncs_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `ncs_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`ncs_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='名片夹设置表';