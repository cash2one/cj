SET NAMES UTF8;

DROP TABLE IF EXISTS `cy_common_adminer`;
CREATE TABLE `cy_common_adminer` (
  `ca_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员id，流水id',
  `ca_username` char(15) NOT NULL DEFAULT '' COMMENT '登录用户名',
  `ca_password` char(32) NOT NULL DEFAULT '' COMMENT '后台登录密码',
  `cag_id` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '所属管理组组id',
  `ca_locked` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否锁定登录，0允许登录，1禁止登录，2系统帐号禁止删除',
  `cd_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所在部门',
  `ca_realname` char(54) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `ca_mobilephone` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `ca_lastlogin` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `ca_lastloginip` char(15) NOT NULL DEFAULT '0.0.0.0' COMMENT '最后登录ip',
  `ca_salt` varchar(6) NOT NULL DEFAULT '' COMMENT '密码干扰串',
  `ca_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `ca_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ca_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ca_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ca_id`),
  KEY `ca_status` (`ca_status`),
  KEY `ca_username` (`ca_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='后台管理成员表';


DROP TABLE IF EXISTS `cy_common_adminergroup`;
CREATE TABLE `cy_common_adminergroup` (
  `cag_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理组id，流水id',
  `cag_title` varchar(32) NOT NULL DEFAULT '' COMMENT '管理组名称',
  `cag_enable` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '管理组状态。0停用，1启用，2最高权限组禁止编辑权限以及删除',
  `cag_role` text NOT NULL COMMENT '管理组角色权限，serialize',
  `cag_description` varchar(100) NOT NULL DEFAULT '' COMMENT '管理组描述',
  `cag_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `cag_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `cag_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `cag_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cag_id`),
  KEY `grouptitle` (`cag_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='后台管理人员用户组';


DROP TABLE IF EXISTS `cy_common_cpmenu`;
CREATE TABLE `cy_common_cpmenu` (
  `ccm_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单id',
  `cp_pluginid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '插件id',
  `ccm_system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为系统菜单，1是，0否',
  `ccm_module` char(30) NOT NULL DEFAULT '' COMMENT '后台模块代码',
  `ccm_operation` char(30) NOT NULL DEFAULT '' COMMENT '主业务代码',
  `ccm_subop` char(30) NOT NULL DEFAULT '' COMMENT '子业务代码',
  `ccm_type` enum('module','operation','subop') NOT NULL DEFAULT 'module' COMMENT '动作类型',
  `ccm_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为该动作的默认业务',
  `ccm_name` char(30) NOT NULL DEFAULT '' COMMENT '名称',
  `ccm_icon` char(50) NOT NULL DEFAULT '' COMMENT '图标',
  `ccm_display` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用，1启用，0禁止',
  `ccm_displayorder` smallint(6) unsigned NOT NULL DEFAULT '999' COMMENT '显示顺序',
  `ccm_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `ccm_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ccm_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ccm_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ccm_id`),
  KEY `ccm_operation_ccm_subop` (`ccm_operation`,`ccm_subop`),
  KEY `ccm_default` (`ccm_default`),
  KEY `ccm_display` (`ccm_display`),
  KEY `cp_id` (`cp_pluginid`),
  KEY `ccm_module_ccm_operation_ccm_subop_cp_pluginid` (`ccm_module`,`ccm_operation`,`ccm_subop`,`cp_pluginid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='后台菜单表';


DROP TABLE IF EXISTS `cy_common_setting`;
CREATE TABLE `cy_common_setting` (
  `cs_key` varchar(50) NOT NULL COMMENT '变量名',
  `cs_value` text NOT NULL COMMENT '值',
  `cs_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `cs_comment` text NOT NULL COMMENT '说明',
  `cs_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `cs_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `cs_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `cs_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cs_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='设置表';


DROP TABLE IF EXISTS `cy_common_sqlrecord`;
CREATE TABLE `cy_common_sqlrecord` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uniqueid` char(32) NOT NULL DEFAULT '' COMMENT '页面唯一键值',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '当前时间',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '当前URL',
  `get` text NOT NULL COMMENT '当前GET参数',
  `post` text NOT NULL COMMENT 'POST参数',
  `sql` text NOT NULL COMMENT 'SQL语句',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '数据状态: 1 新建; 2 已更新; 3 已删除;',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `status` (`status`),
  KEY `updated` (`updated`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='所有SQL日志';


DROP TABLE IF EXISTS `cy_common_syscache`;
CREATE TABLE `cy_common_syscache` (
  `csc_name` varchar(32) NOT NULL COMMENT '缓存文件名',
  `csc_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `csc_data` mediumblob NOT NULL COMMENT '数据',
  `csc_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `csc_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `csc_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `csc_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`csc_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `cy_enterprise_app`;
CREATE TABLE `cy_enterprise_app` (
  `ea_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ca_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '跟进此应用的管理人员ID',
  `ep_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `ea_name` varchar(40) NOT NULL DEFAULT '' COMMENT '应用名称',
  `ea_agentid` varchar(32) NOT NULL DEFAULT '' COMMENT '代理ID',
  `ea_appstatus` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '应用维护状态：0待建立，1待删除，2待关闭，3已建立，4已删除，5已关闭',
  `ea_icon` varchar(255) NOT NULL DEFAULT '' COMMENT '应用图标 URL',
  `ea_description` varchar(255) NOT NULL DEFAULT '' COMMENT '应用描述内容',
  `oacp_pluginid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '企业站对应此应用的common_plugin表cp_pluginid',
  `ea_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `ea_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ea_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ea_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ea_id`),
  KEY `ea_status_ea_created` (`ea_appstatus`,`ea_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='企业应用请求表';


DROP TABLE IF EXISTS `cy_enterprise_profile`;
CREATE TABLE `cy_enterprise_profile` (
  `ep_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ca_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '跟进该企业的管理人员ID',
  `ep_name` varchar(50) NOT NULL DEFAULT '' COMMENT '企业名称',
  `ep_industry` varchar(100) DEFAULT '0' COMMENT '行业',
  `ep_companysize` varchar(50) DEFAULT '0' COMMENT '公司规模',
  `ep_ref` varchar(20) DEFAULT '0' COMMENT '来源',
  `ep_city` varchar(20) NOT NULL DEFAULT '' COMMENT '城市',
  `ep_agent` varchar(50) NOT NULL DEFAULT '' COMMENT '代理商',
  `ep_domain` varchar(120) NOT NULL DEFAULT '' COMMENT '域名',
  `ep_contact` varchar(12) NOT NULL DEFAULT '' COMMENT '联系人',
  `ep_contactposition` varchar(20) NOT NULL DEFAULT '0' COMMENT '联系人职位',
  `ep_mobilephone` varchar(11) NOT NULL DEFAULT '' COMMENT '联系人手机号',
  `ep_email` varchar(50) NOT NULL DEFAULT '' COMMENT '邮箱',
  `ep_wxqy` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启了微信企业号服务',
  `ep_wxname` varchar(50) NOT NULL DEFAULT '' COMMENT '微信企业号名称',
  `ep_wxuname` varchar(50) NOT NULL DEFAULT '' COMMENT '微信企业号用户名',
  `ep_wxpasswd` varchar(255) NOT NULL DEFAULT '' COMMENT '微信企业号密码加密',
  `ep_wxcorpid` varchar(100) NOT NULL DEFAULT '' COMMENT '微信企业号corpid',
  `ep_wxcorpsecret` varchar(200) NOT NULL DEFAULT '' COMMENT '微信企业号corpsecret',
  `ep_wxtoken` varchar(200) NOT NULL DEFAULT '' COMMENT '微信企业号token',
  `ep_xgaccessid` varchar(200) NOT NULL DEFAULT '' COMMENT '信鹆access id',
  `ep_xgaccesskey` varchar(200) NOT NULL DEFAULT '' COMMENT '信鹆access key',
  `ep_xgsecretkey` varchar(200) NOT NULL DEFAULT '' COMMENT '信鹆secret key',
  `ep_qrcode` varchar(200) NOT NULL DEFAULT '' COMMENT '二维码',
  `ep_statuswx` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '微信企业号信息填写状态：1完成，0未完成',
  `ep_statusmail` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '邮件发送状态：1完成，0未完成',
  `ep_statusep` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '企业信息填写情况：1完成，0未完成',
  `ep_statusapp` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '应用添加状态：1完成，0未完成',
  `ep_statusall` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '已上线',
  `ep_locked` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '企业锁定, 0=正常， 1=锁定',
  `ep_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, -1=已经锁定, 1=初始化，2=已更新, 3=已删除',
  `ep_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ep_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ep_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `ep_adminmobile` varchar(32) NOT NULL DEFAULT '' COMMENT '管理员手机号',
  `ep_adminrealname` varchar(32) NOT NULL DEFAULT '' COMMENT '管理员姓名',
  `ep_admindepartment` varchar(32) NOT NULL DEFAULT '' COMMENT '管理员部门',
  PRIMARY KEY (`ep_id`),
  UNIQUE KEY `ep_domain` (`ep_domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='企业资料表';

DROP TABLE IF EXISTS `cy_enterprise_account`;
CREATE TABLE `cy_enterprise_account` (
  `acid` int(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `province` char(15) NOT NULL DEFAULT '' COMMENT '所在省份',
  `city` char(32) NOT NULL DEFAULT '' COMMENT '市',
  `county` char(54) NOT NULL DEFAULT '' COMMENT '所在县',
  `co_name` char(11) NOT NULL DEFAULT '' COMMENT '公司名称',
  `intro` varchar(255) NOT NULL COMMENT '公司简介',
  `link_name` varchar(255) NOT NULL DEFAULT '' COMMENT '联系人姓名',
  `link_phone` char(15) NOT NULL COMMENT '联系人手机',
  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '代理期限',
  `count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '代理数量',
  `created_day` varchar(255) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `created_hour` varchar(255) NOT NULL DEFAULT '0' COMMENT '注册时间时',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`acid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代理主表';

DROP TABLE IF EXISTS `cy_enterprise_alog`;
CREATE TABLE `cy_enterprise_alog` (
  `loid` int(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `record` varchar(255) DEFAULT NULL COMMENT '记录',
  `uid` int(10) unsigned DEFAULT NULL COMMENT '操作人id',
  `epid` int(10) unsigned DEFAULT NULL COMMENT '修改公司id',
  PRIMARY KEY (`loid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代理操作日志表';

DROP TABLE IF EXISTS `cy_news`;
CREATE TABLE `cy_news` (
  `nid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '新闻自增ID',
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID,来自分类表',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '新闻标题',
  `keywords` varchar(100) NOT NULL DEFAULT '' COMMENT '新闻关键字',
  `description` varchar(200) NOT NULL DEFAULT '' COMMENT '新闻描述',
  `coverimg` varchar(100) NOT NULL DEFAULT '' COMMENT '封面图片路径',
  `content` mediumtext NOT NULL COMMENT '新闻内容',
  `hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击阅读记录数',
  `publishtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '自定义文章发表时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`nid`),
  KEY `cid_publishtime_status` (`cid`,`publishtime`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='新闻资讯表';


DROP TABLE IF EXISTS `cy_news_templates`;
CREATE TABLE `cy_news_templates` (
  `ne_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '',
  `summary` varchar(120) NOT NULL DEFAULT '' COMMENT '摘要',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT 'ICON',
  `cover_url` varchar(250) NOT NULL DEFAULT '',
  `content` text COMMENT '新闻公告内容',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ne_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='新闻公告表';


DROP TABLE IF EXISTS `cy_news_view`;
CREATE TABLE `cy_news_view` (
  `nvid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `nid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新闻ID,来自新闻主表',
  `fromip` char(15) NOT NULL DEFAULT '0.0.0.0' COMMENT '来源IP',
  `visittime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '访问时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`nvid`),
  KEY `nid_fromip_status` (`nid`,`fromip`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='新闻阅读表';


CREATE TABLE `cy_stat_log` (
  `stat_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '访问者时间',
  `ip` varchar(15) NOT NULL DEFAULT '0.0.0.0' COMMENT '访问者IP地址',
  `wechat` varchar(32) NOT NULL DEFAULT '' COMMENT '微信版本号',
  `ep_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '访问者m_uid',
  `plugin` varchar(32) NOT NULL DEFAULT '' COMMENT '访问的应用唯一标识符（cp_identifier）',
  `domain` varchar(120) NOT NULL DEFAULT '' COMMENT '域名',
  `appinfirst` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为当日首次进入应用',
  `appin` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否计算为进入应用次数',
  `url` varchar(120) NOT NULL DEFAULT '' COMMENT '受访URL',
  `user` varchar(255) NOT NULL DEFAULT '' COMMENT '浏览器user-agent',
  `referer` varchar(255) NOT NULL DEFAULT '' COMMENT '来路URL',
  `nettype` varchar(255) NOT NULL COMMENT '客户端网络环境',
  `language` varchar(255) NOT NULL COMMENT '客户端语言',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '数据记录状态。1=新创建;2=已更新;3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`stat_log_id`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='访问日志表';


DROP TABLE IF EXISTS `cy_recognition_bill`;
CREATE TABLE `cy_recognition_bill` (
  `rb_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `bill_id` int(11) unsigned NOT NULL COMMENT '单据id',
  `ep_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `rb_pictureurl` varchar(255) NOT NULL DEFAULT '' COMMENT '图片地址',
  `rb_billtext` text NOT NULL COMMENT '识别文本，使用序列化字符串储存',
  `rb_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未处理，1已识别，2图片不清晰，3非报销单据，4识别有误，5已确认识别结果',
  `rb_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ca_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '处理此数据的管理人员id',
  `rb_handletime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '处理时间',
  `rb_authorid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '确认识别结果的管理人员id',
  `rb_confirmtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '确认识别结果时间',
  PRIMARY KEY (`rb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报销单据识别记录表';


DROP TABLE IF EXISTS `cy_recognition_bill_backup`;
CREATE TABLE `cy_recognition_bill_backup` (
  `rb_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `bill_id` int(11) unsigned NOT NULL COMMENT '单据id',
  `ep_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `rb_pictureurl` varchar(255) NOT NULL DEFAULT '' COMMENT '图片地址',
  `rb_billtext` text NOT NULL COMMENT '识别文本，使用序列化字符串储存',
  `rb_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未处理，1已识别，2图片不清晰，3非报销单据，4识别有误，5已确认识别结果',
  `rb_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ca_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '处理此数据的管理人员id',
  `rb_handletime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '处理时间',
  `rb_authorid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '确认识别结果的管理人员id',
  `rb_confirmtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '确认识别结果时间',
  PRIMARY KEY (`rb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报销单据识别数据备份表';


DROP TABLE IF EXISTS `cy_recognition_namecard`;
CREATE TABLE `cy_recognition_namecard` (
  `rnc_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `nc_id` int(11) unsigned NOT NULL COMMENT '名片id',
  `ep_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `rnc_pictureurl` varchar(255) NOT NULL DEFAULT '' COMMENT '图片地址',
  `rnc_namecardtext` text NOT NULL COMMENT '识别文本，使用序列化字符串储存',
  `rnc_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未处理，1已识别，2图片不清晰，3非报销单据，4识别有误，5已确认识别结果',
  `rnc_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ca_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '处理此数据的管理人员id',
  `rnc_handletime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '处理时间',
  `rnc_authorid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '确认识别结果的管理人员id',
  `rnc_confirmtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '确认识别结果时间',
  PRIMARY KEY (`rnc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='名片识别记录表';


DROP TABLE IF EXISTS `cy_recognition_namecard_backup`;
CREATE TABLE `cy_recognition_namecard_backup` (
  `rnc_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `nc_id` int(11) unsigned NOT NULL COMMENT '名片id',
  `ep_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `rnc_pictureurl` varchar(255) NOT NULL DEFAULT '' COMMENT '图片地址',
  `rnc_namecardtext` text NOT NULL COMMENT '识别文本，使用序列化字符串储存',
  `rnc_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未处理，1已识别，2图片不清晰，3非报销单据，4识别有误，5已确认识别结果',
  `rnc_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ca_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '处理此数据的管理人员id',
  `rnc_handletime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '处理时间',
  `rnc_authorid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '确认识别结果的管理人员id',
  `rnc_confirmtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '确认识别结果时间',
  PRIMARY KEY (`rnc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='名片识别数据备份表';

DROP TABLE IF EXISTS `cy_report_wxjs`;
CREATE TABLE `cy_report_wxjs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `hash` char(32) NOT NULL DEFAULT '' COMMENT '报告请求的md5',
  `ip` char(15) NOT NULL DEFAULT '0.0.0.0' COMMENT '报告发送者的IP地址',
  `wxversion` char(32) NOT NULL DEFAULT '' COMMENT '微信版本号',
  `mobile` char(15) NOT NULL DEFAULT '' COMMENT '用户手机号',
  `res` text NOT NULL COMMENT '微信JS接口返回的结果',
  `type` char(32) NOT NULL DEFAULT '' COMMENT '微信JS接口返回错误的方法',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '发送报告的页面URL',
  `useragent` text NOT NULL COMMENT '发送报告的浏览器代理头信息',
  `domain` varchar(100) NOT NULL DEFAULT '' COMMENT '所在域名',
  `ep_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送报告所在企业所属的企业ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '该企业的人员用户m_uid',
  `count` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '发生次数',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '数据记录状态',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `ep_id_updated` (`ep_id`,`updated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='微信前端JS接口错误报告表';

-- 2014-06-20 10:17:49