SET NAMES UTF8;


CREATE TABLE IF NOT EXISTS `uc_common_sqlrecord` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='所有SQL日志';


CREATE TABLE IF NOT EXISTS `uc_appversion` (
  `ver_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ver_number` varchar(32) NOT NULL DEFAULT '' COMMENT '版本号',
  `ver_clienttype` enum('ios','android','windows phone') NOT NULL DEFAULT 'ios' COMMENT '客户端类型',
  `ver_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '版本更新日期',
  `ver_forceupdate` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否强制更新。1=强制,0=不强制',
  `ver_storeurl` varchar(255) NOT NULL DEFAULT '' COMMENT '更新应用所在应用商店URL',
  `ver_download` varchar(255) NOT NULL DEFAULT '' COMMENT '本地下载链接',
  `ver_message` text NOT NULL COMMENT '更新内容描述，文本格式',
  `ver_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态。1=初始化,2=已更新,3=已删除',
  `ver_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ver_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ver_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ver_id`),
  KEY `ver_clienttype` (`ver_clienttype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='应用版本库';


CREATE TABLE IF NOT EXISTS `uc_common_sqlrecord` (
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


CREATE TABLE IF NOT EXISTS `uc_crontab` (
  `c_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '任务ID',
  `c_taskid` char(32) NOT NULL COMMENT '任务id',
  `c_domain` char(40) NOT NULL COMMENT '域名',
  `c_ip` char(15) NOT NULL COMMENT '域名对应的IP',
  `c_type` varchar(45) NOT NULL COMMENT '任务类型',
  `c_method` enum('GET','POST') NOT NULL DEFAULT 'GET' COMMENT '请求Method方法',
  `c_params` text NOT NULL COMMENT '请求参数',
  `c_runtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '计划任务执行时间',
  `c_endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '计划任务结束时间',
  `c_looptime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务循环间隔',
  `c_times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '计划任务需要运行的次数',
  `c_runs` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '计划任务已运行次数',
  `c_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `c_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `c_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `c_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`c_id`),
  KEY `c_runtime` (`c_runtime`),
  KEY `c_status` (`c_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='计划任务表';


CREATE TABLE IF NOT EXISTS `uc_dbhost` (
  `db_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `db_title` char(32) NOT NULL DEFAULT '' COMMENT '主机名称,用于标识区分',
  `db_host` char(32) NOT NULL DEFAULT '' COMMENT '主机地址和端口号(如果存在)',
  `db_user` char(32) NOT NULL DEFAULT '' COMMENT '数据库用户名，具备创建数据库、表等权限的用户',
  `db_pw` char(32) NOT NULL DEFAULT '' COMMENT '数据库密码',
  `db_count` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '该主机当前存放的主机数',
  `db_maximum` smallint(6) unsigned NOT NULL DEFAULT '500' COMMENT '该主机可存放的最大数据库(企业)数',
  `db_lanip` char(15) NOT NULL DEFAULT '' COMMENT '该主机的内网IP，允许内网连接则设置，不允许则为空',
  `db_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态,1=初始化,2=已更新,3=已删除',
  `db_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `db_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `db_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`db_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据库主机池,记录具备创建数据库权限的帐号信息';


CREATE TABLE IF NOT EXISTS `uc_dnspod` (
  `dp_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `dp_zoneid` int(10) unsigned NOT NULL COMMENT '主域名id',
  `dp_cname` char(64) DEFAULT NULL COMMENT '主机记录',
  `dp_type` enum('A','AAAA','CNAME','HINFO','MX','NAPTR','NS','PTR','RP','SRV','TXT') DEFAULT NULL COMMENT '域名类型',
  `dp_data` char(128) DEFAULT NULL COMMENT 'A记录值',
  `dp_ttl` int(10) unsigned NOT NULL DEFAULT '600' COMMENT 'TTL',
  `dp_record_id` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT 'dnspod记录id',
  `dp_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1:待绑定,2:已绑定,3:待删除,4:待修改,5:已删除',
  `dp_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `dp_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `dp_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`dp_id`),
  KEY `cdp_name` (`dp_cname`),
  KEY `cdp_rr` (`dp_zoneid`,`dp_cname`,`dp_type`,`dp_data`),
  KEY `cdp_status` (`dp_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='dnspod cname 记录';


CREATE TABLE IF NOT EXISTS `uc_enterprise` (
  `ep_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ep_wxqy` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '微信企业号开通状态：0=未开通,1=已开通手动开启应用,2=开启授权模式',
  `ep_enumber` char(20) NOT NULL DEFAULT '' COMMENT '企业帐号',
  `ep_domain` char(120) NOT NULL DEFAULT '' COMMENT '企业域名(完整域名)',
  `ep_name` char(50) NOT NULL DEFAULT '' COMMENT '企业名称',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业管理员在其库里的m_uid',
  `ep_adminemail` char(40) NOT NULL DEFAULT '' COMMENT '管理人员邮箱',
  `ep_adminmobilephone` char(11) NOT NULL DEFAULT '' COMMENT '管理人员手机号',
  `ep_adminrealname` char(50) NOT NULL DEFAULT '' COMMENT '管理人员姓名',
  `ep_adminunionid` char(50) NOT NULL DEFAULT '' COMMENT '管理人员微信openid',
  `ep_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化,2=已更新, 3=关闭,4=数据库建立中,5=DNS写入中,6=已删除',
  `ep_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ep_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ep_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ep_id`),
  KEY `ep_status` (`ep_status`),
  KEY `ep_enterprise` (`ep_enumber`),
  KEY `ep_adminemail` (`ep_adminemail`),
  KEY `ep_adminmobile` (`ep_adminmobilephone`),
  KEY `ep_openid` (`ep_adminunionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='企业信息主表';


CREATE TABLE IF NOT EXISTS `uc_enterprise_adminer` (
  `ad_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `ep_id` int(10) NOT NULL DEFAULT '0' COMMENT '公司ID',
  `ca_id` int(10) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `mobilephone` char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '数据记录状态。1=新建,2=已更新,3=已删除',
  `created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ad_id`),
  KEY `mobilephone_status` (`mobilephone`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='普通登录手机号和公司信息关联表';


CREATE TABLE IF NOT EXISTS `uc_enterprise_profile` (
  `ep_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID,来自uc_enterprise',
  `epp_wxname` varchar(50) NOT NULL DEFAULT '' COMMENT '微信企业号名称',
  `epp_wxuname` varchar(50) NOT NULL DEFAULT '' COMMENT '微信企业号用户名',
  `epp_wxpasswd` varchar(255) NOT NULL DEFAULT '' COMMENT '微信企业号密码加密',
  `epp_wxcorpid` varchar(100) NOT NULL DEFAULT '' COMMENT '微信企业号corpid',
  `epp_wxcorpsecret` varchar(200) NOT NULL DEFAULT '' COMMENT '微信企业号corpsecret',
  `epp_wxopenid` varchar(200) NOT NULL DEFAULT '' COMMENT '微信企业号openid，同corpid',
  `db_id` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '关联uc_dbhost表',
  `epp_dbhost` varchar(20) NOT NULL DEFAULT '' COMMENT '所在数据库主机和端口号',
  `epp_dbuser` varchar(15) NOT NULL DEFAULT '' COMMENT '所在数据库用户名',
  `epp_dbpw` varchar(15) NOT NULL DEFAULT '' COMMENT '所在数据库密码',
  `epp_dbname` varchar(15) NOT NULL DEFAULT '' COMMENT '所在数据库名',
  `web_id` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '关联uc_webhost表',
  `epp_webip` varchar(15) NOT NULL DEFAULT '' COMMENT '所在WEB主机IP地址',
  `epp_industry` varchar(100) NOT NULL DEFAULT '' COMMENT '行业',
  `epp_companysize` varchar(100) NOT NULL DEFAULT '' COMMENT '企业规模',
  `epp_ref` varchar(20) NOT NULL DEFAULT '' COMMENT '来源',
  `epp_city` varchar(30) NOT NULL DEFAULT '' COMMENT '城市',
  `epp_agent` varchar(30) NOT NULL DEFAULT '' COMMENT '代理商',
  `epp_contact` varchar(30) NOT NULL DEFAULT '' COMMENT '联系人',
  `epp_contactmobilephone` varchar(11) NOT NULL DEFAULT '' COMMENT '联系人手机号',
  `epp_contactemail` varchar(40) NOT NULL DEFAULT '' COMMENT '联系人邮箱地址',
  `epp_contactposition` varchar(30) NOT NULL DEFAULT '' COMMENT '联系人职务',
  `epp_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=关闭,4=已删除',
  `epp_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `epp_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `epp_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ep_id`),
  KEY `db_id` (`db_id`),
  KEY `web_id` (`web_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='企业扩展信息表';


CREATE TABLE IF NOT EXISTS `uc_mailcloud` (
  `mc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `mc_tplname` int(32) unsigned NOT NULL COMMENT '模板名称',
  `mc_email` char(255) CHARACTER SET gbk DEFAULT NULL COMMENT '邮件地址',
  `mc_subject` char(255) NOT NULL COMMENT '主题',
  `mc_vars` text NOT NULL COMMENT '模板变量, 数组的序列化字串',
  `mc_repeat` tinyint(3) unsigned NOT NULL COMMENT '重发次数',
  `mc_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1:发送成功; 2:未成功; 3:已删除',
  `mc_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `mc_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `mc_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`mc_id`),
  KEY `mc_status` (`mc_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邮件发送记录';


CREATE TABLE IF NOT EXISTS `uc_member` (
  `m_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_mobilephone` char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `m_email` char(11) NOT NULL DEFAULT '' COMMENT '邮箱',
  `m_wechatunionid` char(32) NOT NULL DEFAULT '' COMMENT '微信用户unionid',
  `m_qqopenid` char(32) NOT NULL DEFAULT '' COMMENT 'QQ用户openid',
  `m_password` char(32) NOT NULL DEFAULT '' COMMENT '密码',
  `m_realname` char(54) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `m_salt` char(6) NOT NULL DEFAULT '' COMMENT '密码盐值',
  `m_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '数据记录状态。1=新建,2=已更新,99=已删除',
  `m_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `m_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `m_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`m_id`),
  KEY `m_mobilephone` (`m_mobilephone`),
  KEY `m_email` (`m_email`),
  KEY `m_wechatunionid` (`m_wechatunionid`),
  KEY `m_qqopenid` (`m_qqopenid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户表';


CREATE TABLE IF NOT EXISTS `uc_memberfield` (
  `m_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID，关联member表',
  `mf_regip` char(15) NOT NULL DEFAULT '0.0.0.0' COMMENT '注册IP',
  `mf_regdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `mf_lastloginip` char(15) NOT NULL DEFAULT '0.0.0.0' COMMENT '最后登录ip',
  `mf_lastlogin` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `mf_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态。1=新创建,2=已更新,99=已删除',
  `mf_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mf_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `mf_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`m_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户信息扩展表';


CREATE TABLE IF NOT EXISTS `uc_sms` (
  `sms_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `sms_mobile` char(12) NOT NULL COMMENT '手机号',
  `sms_message` char(255) CHARACTER SET gbk DEFAULT NULL COMMENT '短信内容',
  `sms_ip` char(15) NOT NULL COMMENT 'IP地址',
  `sms_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1:发送成功; 2:未成功; 3:已删除',
  `sms_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `sms_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `sms_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`sms_id`),
  KEY `sms_status` (`sms_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手机短信发送记录';


CREATE TABLE IF NOT EXISTS `uc_smscode` (
  `smscode_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `smscode_mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `smscode_code` char(6) NOT NULL DEFAULT '' COMMENT '验证码',
  `smscode_ip` char(15) NOT NULL DEFAULT '' COMMENT 'IP 地址',
  `smscode_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态：1=初始化(未使用),2=已使用验证,3=已删除',
  `smscode_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `smscode_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `smscode_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`smscode_id`),
  KEY `smscode_mobilephone` (`smscode_mobile`),
  KEY `smscode_ip` (`smscode_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短信验证码发送记录';


CREATE TABLE IF NOT EXISTS `uc_suggestion` (
  `sug_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `sug_message` text NOT NULL COMMENT '建议内容',
  `sug_domain` char(64) CHARACTER SET gbk DEFAULT NULL COMMENT '域名',
  `sug_username` char(80) CHARACTER SET gbk DEFAULT NULL COMMENT '用户名称',
  `sug_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1:正常,2:已更新,3:已删除',
  `sug_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `sug_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `sug_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`sug_id`),
  KEY `cdp_status` (`sug_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='意见建议记录';


CREATE TABLE IF NOT EXISTS `uc_suite` (
  `su_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `su_suite_id` varchar(255) NOT NULL DEFAULT '' COMMENT '套件id',
  `su_suite_secret` varchar(255) NOT NULL DEFAULT '' COMMENT '套件秘钥',
  `su_suite_aeskey` varchar(43) NOT NULL DEFAULT '' COMMENT '套件aeskey',
  `su_token` varchar(255) NOT NULL DEFAULT '' COMMENT '用于应用对消息推送请求时校验的进行签名认证的token',
  `su_ticket` varchar(255) NOT NULL DEFAULT '' COMMENT 'ticket',
  `su_ips` text NOT NULL COMMENT 'ip地址',
  `su_suite_access_token` varchar(255) NOT NULL DEFAULT '' COMMENT '套件令牌',
  `su_access_token_expires` int(11) NOT NULL DEFAULT '0' COMMENT '过期时间',
  `su_pre_auth_code` varchar(255) NOT NULL DEFAULT '' COMMENT '预授权码',
  `su_auth_code_expires` int(11) NOT NULL DEFAULT '0' COMMENT '预授权码期限',
  `su_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `su_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `su_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `su_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`su_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='套件表';


CREATE TABLE IF NOT EXISTS `uc_webhost` (
  `web_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `web_title` char(32) NOT NULL DEFAULT '' COMMENT 'WEB主机名称,用于标识区分',
  `web_ip` char(15) NOT NULL DEFAULT '' COMMENT 'WEB主机IP地址',
  `web_alias` char(64) NOT NULL DEFAULT '' COMMENT '该主机的域名别名，用于解析',
  `web_count` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '该主机当前存放的企业数',
  `web_maximum` smallint(6) unsigned NOT NULL DEFAULT '500' COMMENT '该主机最大可存放的企业数',
  `web_lanip` char(15) NOT NULL DEFAULT '' COMMENT '所在内网IP',
  `web_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态,1=初始化,2=已更新,3=已删除',
  `web_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `web_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `web_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`web_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='WEB主机池表，记录所有WEB主机IP地址';


CREATE TABLE IF NOT EXISTS `uc_weixin_send_queue` (
  `wsq_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `wsq_cardid` int(10) unsigned NOT NULL COMMENT '商家ID',
  `wsq_skey` varchar(32) NOT NULL COMMENT '密钥',
  `wsq_openid` varchar(32) NOT NULL COMMENT '接收用户的openid',
  `wsq_tplid` int(10) unsigned NOT NULL COMMENT '模板消息id',
  `wsq_message` text NOT NULL COMMENT '模板变量',
  `wsq_failtimes` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '发送失败次数',
  `wsq_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=发送成功, 3=发送失败，4=已删除',
  `wsq_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `wsq_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `wsq_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`wsq_id`),
  KEY `wsq_status` (`wsq_status`,`wsq_failtimes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='给用户发送模板消息的队列表';


CREATE TABLE IF NOT EXISTS `uc_weopen` (
  `woid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `appid` varchar(255) NOT NULL DEFAULT '' COMMENT '套件id',
  `appsecret` varchar(255) NOT NULL DEFAULT '' COMMENT '套件秘钥',
  `aeskey` varchar(43) NOT NULL DEFAULT '' COMMENT '套件aeskey',
  `token` varchar(255) NOT NULL DEFAULT '' COMMENT '用于应用对消息推送请求时校验的进行签名认证的token',
  `ticket` varchar(255) NOT NULL DEFAULT '' COMMENT 'ticket',
  `ips` text NOT NULL COMMENT 'ip地址',
  `access_token` varchar(255) NOT NULL DEFAULT '' COMMENT '服务令牌',
  `token_expires` int(11) NOT NULL DEFAULT '0' COMMENT '过期时间',
  `pre_auth_code` varchar(255) NOT NULL DEFAULT '' COMMENT '预授权码',
  `auth_code_expires` int(11) NOT NULL DEFAULT '0' COMMENT '预授权码期限',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`woid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='微信开放平台服务表';


-- 2014-07-06 12:44:28
-- 2014-07-06 12:44:28

DROP TABLE IF EXISTS `uc_fastinformation`;
CREATE TABLE `uc_fastinformation` (
  `fa_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `userid` varchar(64) NOT NULL DEFAULT '' COMMENT '微信扫描返回的管理员userid（认证过的企业号才有）',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '微信返回的管理员用户名（认证过的企业号才有）',
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '微信返回的管理员手机号（认证过的企业号才有）',
  `email` varchar(64) NOT NULL DEFAULT '' COMMENT '微信返回的管理员邮箱（认不认证都有）',
  `corpid` varchar(64) NOT NULL DEFAULT '' COMMENT '企业号ID',
  `corp_name` varchar(64) NOT NULL DEFAULT '' COMMENT '企业号名字',
  `corp_type` varchar(64) NOT NULL DEFAULT '' COMMENT '企业号格式',
  `corp_round_logo_url` varchar(255) NOT NULL DEFAULT '' COMMENT '企业号LOGO',
  `corp_square_logo_url` varchar(255) NOT NULL DEFAULT '' COMMENT '企业号LOGO',
  `corp_user_max` int(10) NOT NULL DEFAULT '0' COMMENT '企业号最多人数',
  `corp_agent_max` int(10) NOT NULL DEFAULT '0' COMMENT '企业号最多代理数',
  `corp_wxqrcode` varchar(255) NOT NULL DEFAULT '' COMMENT '企业号二维码',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '数据记录状态。1=新建,2=已更新,3=已删除',
  `created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`fa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快速登录信息表        ';


DROP TABLE IF EXISTS `uc_fastlogin`;
CREATE TABLE `uc_fastlogin` (
  `fast_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `fa_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联信息表',
  `email` char(64) NOT NULL DEFAULT '' COMMENT '微信返回的管理员邮箱（认不认证都有）',
  `corpid` char(64) NOT NULL DEFAULT '' COMMENT '公司ID（微信来源）',
  `ep_id` int(10) NOT NULL DEFAULT '0' COMMENT '公司ID（uc_enterprise）',
  `ca_id` int(10) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `lastlogin` int(10) NOT NULL DEFAULT '0' COMMENT '最后一次登录的时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '数据记录状态。1=新建,2=已更新,3=已删除',
  `created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`fast_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快速登录关联表';

-- 2015年6月19日 21:21:36

CREATE TABLE `uc_common_plugin_group` (
  `cpg_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `cpg_suiteid` char(100) NOT NULL DEFAULT '' COMMENT '关联的应用套件ID',
  `cpg_name` char(255) NOT NULL COMMENT '插件分组名称',
  `cpg_icon` char(255) NOT NULL DEFAULT '' COMMENT '应用分组图标',
  `cpg_ordernum` mediumint(6) unsigned NOT NULL COMMENT '显示顺序',
  `cpg_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `cpg_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `cpg_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `cpg_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cpg_id`),
  KEY `cpg_status` (`cpg_status`),
  KEY `cpg_suiteid` (`cpg_suiteid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='应用分组表';

-- 2015年10月28日 22:58:30