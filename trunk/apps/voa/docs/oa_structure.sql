-- 企业 OA 公共表结构
SET NAMES utf8;

CREATE TABLE `oa_comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `obj_id` char(10) NOT NULL COMMENT '评论对象id',
  `cp_identifier` varchar(40) NOT NULL DEFAULT '' COMMENT '应用唯一标识名',
  `plugin_id` int(10) unsigned NOT NULL COMMENT '应用id',
  `m_uid` int(10) unsigned NOT NULL COMMENT '评论用户id',
  `m_username` char(54) NOT NULL COMMENT '评论用户名',
  `m_face` char(255) NOT NULL COMMENT '用户头像',
  `reply_id` int(10) unsigned NOT NULL COMMENT '被回复评论id',
  `reply_m_uid` int(10) unsigned NOT NULL COMMENT '回复评论者id',
  `reply_m_username` varchar(255) NOT NULL COMMENT '回复评论者姓名',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text COMMENT '评论内容',
  `attach_id` varchar(255) NOT NULL DEFAULT '' COMMENT '附件ID，使用英文逗号,隔开',
  `likes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论点赞数目',
  `direction` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '方向 默认:0 蓝:1 红:2',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态: 1=正常; 2=已更新; 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `obj_id` (`obj_id`),
  KEY `plugin_id` (`plugin_id`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='评论表';


CREATE TABLE `oa_comment_likes` (
  `lid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `cid` int(10) unsigned NOT NULL COMMENT '评论id',
  `m_uid` int(10) unsigned NOT NULL COMMENT '评论用户id',
  `m_username` char(54) NOT NULL COMMENT '评论用户名',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '记录状态: 1=正常; 2=已更新; 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`lid`),
  KEY `cid` (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='评论表点赞';

CREATE TABLE `oa_common_adminer` (
  `ca_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ca_mobilephone` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `ca_email` char(45) NOT NULL DEFAULT '' COMMENT 'Email',
  `ca_username` char(15) NOT NULL DEFAULT '' COMMENT '显示名',
  `ca_password` char(32) NOT NULL DEFAULT '' COMMENT '后台登录密码',
  `cag_id` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '所属管理组组id',
  `ca_locked` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否锁定登录，0允许登录，1禁止登录，2系统帐号禁止删除',
  `ca_lastlogin` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `ca_lastloginip` char(15) NOT NULL DEFAULT '0.0.0.0' COMMENT '最后登录ip',
  `ca_salt` varchar(6) NOT NULL DEFAULT '' COMMENT '密码干扰串',
  `ca_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `ca_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ca_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ca_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ca_id`),
  KEY `ca_status` (`ca_status`),
  KEY `ca_username` (`ca_username`),
  KEY `ca_mobilephone` (`ca_mobilephone`),
  KEY `ca_email` (`ca_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='后台管理成员表';


CREATE TABLE `oa_common_adminergroup` (
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


CREATE TABLE `oa_common_apicache` (
  `cac_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `cac_name` char(32) NOT NULL DEFAULT '' COMMENT '请求参数的唯一标识字符串',
  `cac_unique` char(32) NOT NULL DEFAULT '' COMMENT '缓存的数据的唯一标识字符串',
  `cac_param` text NOT NULL COMMENT '请求的参数序列化',
  `cac_data` mediumtext NOT NULL COMMENT '缓存的数据的序列化',
  `cac_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态,1=初始化,2=已更新,3=已删除',
  `cac_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `cac_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `cac_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cac_id`),
  UNIQUE KEY `cac_name` (`cac_name`),
  KEY `cac_updated` (`cac_updated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='提供给API接口的公共数据缓存表';


CREATE TABLE `oa_common_attachment` (
  `at_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL COMMENT '用户名称',
  `at_filename` varchar(255) NOT NULL COMMENT '文件名称',
  `at_filesize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小(单位:字节)',
  `at_mediatype` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '文件类型标记：0=未知，99=普通文件，1=图片，2=音频，3=视频',
  `at_attachment` varchar(255) NOT NULL COMMENT '附件地址',
  `at_remote` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为远程地址, 1:是, 2:否',
  `at_description` varchar(255) NOT NULL COMMENT '附件的描述',
  `at_isimage` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为图片',
  `at_isattach` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0:不是附件,1:是附件',
  `at_width` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '图片宽度',
  `at_thumb` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否有缩微图, 1:有, 2:没有',
  `at_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '记录状态, 1=正常; 2=预删除; 3=已删除;',
  `at_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `at_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `at_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`at_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='附件表';


CREATE TABLE `oa_common_columntype` (
  `ctid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '元素id',
  `ct_type` varchar(60) NOT NULL COMMENT '字段类型, 唯一标识, 比如:int, char, select等',
  `ct_name` varchar(45) NOT NULL COMMENT '字段类型名称',
  `min` int(10) unsigned NOT NULL COMMENT '最小值或长度',
  `max` int(10) unsigned NOT NULL COMMENT '最大值或长度',
  `reg_exp` varchar(255) NOT NULL COMMENT '正则表达式',
  `initval` varchar(255) NOT NULL COMMENT '该字段的默认值',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '类型图标样式',
  `status` smallint(5) unsigned NOT NULL COMMENT '状态值, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL COMMENT '新建时间',
  `updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`ctid`),
  KEY `status` (`status`),
  KEY `ct_type` (`ct_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据表字段类型';


CREATE TABLE `oa_common_cpmenu` (
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
  `ccm_subnavdisplay` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否在子导航内显示，如：编辑、删除一类的可设为0，不显示',
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


CREATE TABLE `oa_common_department` (
  `cd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `cd_upid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级部门id',
  `cd_displayorder` tinyint(3) unsigned NOT NULL DEFAULT '99' COMMENT '显示顺序',
  `cd_name` varchar(255) NOT NULL DEFAULT '' COMMENT '部门简称',
  `cd_lastordertime` int(10) NOT NULL DEFAULT 1 COMMENT '上次排序时间',
  `cd_permission` int(10) NOT NULL DEFAULT 0 COMMENT '部门查看权限，0全公司，1仅本部门, 2指定部门',
  `cd_purview` int(10) NOT NULL DEFAULT '1' COMMENT '权限选择, 1=全公司，2=仅本部门，3=仅子部门',
  `cd_usernum` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '员工数(member表关联部门人数)',
  `cd_qywxid` varchar(11) NOT NULL DEFAULT '' COMMENT '企业微信关联的部门id',
  `cd_qywxparentid` varchar(11) NOT NULL DEFAULT '' COMMENT '企业微信关联的父亲部门id',
  `cd_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `cd_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `cd_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `cd_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cd_id`),
  KEY `cd_status` (`cd_status`),
  KEY `cd_name` (`cd_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='部门列表';


CREATE TABLE `oa_common_job` (
  `cj_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `cj_displayorder` tinyint(3) unsigned NOT NULL DEFAULT '99' COMMENT '显示顺序',
  `cj_name` varchar(255) NOT NULL DEFAULT '' COMMENT '职位简称',
  `cj_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `cj_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `cj_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `cj_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cj_id`),
  KEY `cj_status` (`cj_status`),
  KEY `cj_name` (`cj_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='职位列表';


CREATE TABLE `oa_common_module_group` (
  `cmg_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `cmg_name` char(16) NOT NULL DEFAULT '' COMMENT '组名称',
  `cmg_displayorder` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '显示顺序',
  `cmg_icon` char(32) NOT NULL DEFAULT '' COMMENT '组图标',
  `cmg_dir` char(16) NOT NULL DEFAULT '' COMMENT '组目录名',
  `cmg_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=已删除',
  `cmg_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `cmg_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `cmg_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cmg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='应用分组表';


CREATE TABLE `oa_common_place` (
  `placeid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `placetypeid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '所属类型ID',
  `placeregionid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '所属区域ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '地点名称',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地点详细地址',
  `lng` decimal(9,6) NOT NULL DEFAULT '0.000000' COMMENT '所在经度',
  `lat` decimal(9,6) NOT NULL DEFAULT '0.000000' COMMENT '所在纬度',
  `remove` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已删除',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '数据状态:1=新创建,2=已更新,99=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`placeid`),
  KEY `placetypeid` (`placetypeid`),
  KEY `placeregionid` (`placeregionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='场所-地点表';


CREATE TABLE `oa_common_place_member` (
  `placememberid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `placetypeid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '所属类型ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '人员ID。0=所有人',
  `placeregionid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '所在区域ID。非零=区域人员,0=场所人员',
  `placeid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所在场所ID。非零=场所人员,0=区域人员',
  `level` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '职务地位。1=负责人,2=普通人员',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '数据状态:1=新创建,2=已更新,99=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`placememberid`),
  KEY `placetypeid` (`placetypeid`),
  KEY `uid` (`uid`),
  KEY `placeregionid` (`placeregionid`),
  KEY `placeid` (`placeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='场所-场所相关人员表';


CREATE TABLE `oa_common_place_region` (
  `placeregionid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `placetypeid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '所属类型ID',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级区域ID。0=顶级区域',
  `deepin` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '分级深度（级别深度，顶级为1）',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '区域名称',
  `remove` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否标记为已删除',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '数据状态:1=新创建,2=已更新,99=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`placeregionid`),
  KEY `placetypeid_parentid` (`placetypeid`,`parentid`),
  KEY `name` (`name`),
  KEY `deepin` (`deepin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='场所-区域表';


CREATE TABLE `oa_common_place_setting` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 99=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='场地-设置表';


CREATE TABLE `oa_common_place_type` (
  `placetypeid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '类型名称',
  `levels` text NOT NULL COMMENT '该类型下的区域、场所相关人员级别权限称谓',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '数据状态:1=新创建,2=已更新,99=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`placetypeid`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='场所-地点表';


CREATE TABLE `oa_common_plugin` (
  `cp_pluginid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `cp_identifier` varchar(40) NOT NULL DEFAULT '' COMMENT '插件唯一标识名',
  `cmg_id` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '关联的模块组',
  `cpg_id` smallint(5) unsigned NOT NULL COMMENT '分组id',
  `cp_suiteid` varchar(100) NOT NULL DEFAULT '' COMMENT '应用套件ID',
  `cp_agentid` varchar(32) NOT NULL DEFAULT '' COMMENT '应用代理ID',
  `cp_displayorder` smallint(6) unsigned NOT NULL DEFAULT '9999' COMMENT '显示顺序，顺序排序',
  `cp_available` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '启用状态：0新应用，1待启用，2待关闭，3待删除，4已开启，5已关闭，6已删除，255应用未开放',
  `cp_adminid` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '操作权限',
  `cp_name` varchar(40) NOT NULL DEFAULT '' COMMENT '插件名称',
  `cp_icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标地址',
  `cp_description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `cp_datatables` varchar(255) NOT NULL DEFAULT '' COMMENT '数据表, 多表格以 "," 分隔',
  `cp_directory` varchar(100) NOT NULL DEFAULT '' COMMENT '插件程序目录',
  `cp_url` varchar(255) NOT NULL DEFAULT '' COMMENT '插件默认地址',
  `cp_version` varchar(20) NOT NULL DEFAULT '' COMMENT '版本号',
  `cp_lastavailable` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上次设置状态改变时间，用于避免频繁反复操作',
  `cp_lastopen` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后启用时间。该值不为0则启用应用时不导入默认数据，否则导入',
  `cyea_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来自总站的启用对应关系id.enterprise_app表ea_id',
  `cp_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `cp_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `cp_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `cp_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cp_pluginid`),
  KEY `cp_status` (`cp_status`),
  KEY `cp_identifier` (`cp_identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='应用表（含启用信息）';


CREATE TABLE `oa_common_plugin_display` (
  `cpd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` mediumint(9) NOT NULL COMMENT '用户uid',
  `cpd_isfav` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否常用：1常用，0普通',
  `cp_pluginid` mediumint(1) unsigned NOT NULL DEFAULT '0' COMMENT '插件id',
  `cpd_ordernum` mediumint(6) unsigned NOT NULL COMMENT '显示顺序',
  `cpd_lastusetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次使用时间',
  `cpd_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `cpd_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `cpd_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `cpd_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cpd_id`),
  KEY `cpf_status` (`cpd_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户的应用排序表';


CREATE TABLE `oa_common_plugin_group` (
  `cpg_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `cpg_suiteid` char(100) NOT NULL DEFAULT '' COMMENT '关联的应用套件ID',
  `cpg_name` char(255) NOT NULL COMMENT '插件分组名称',
  `cpg_icon` char(255) NOT NULL DEFAULT '' COMMENT '应用分组图标',
  `cpg_ordernum` mediumint(6) unsigned NOT NULL COMMENT '显示顺序',
  `pay_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1:标准产品,2:定制产品,3:私有部署',
  `date_start` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始日期',
  `date_end` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '截止日期',
  `stop_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否关闭状态:0,不是; 1, 关闭',
  `pay_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1:已付费;2:试用期',
  `cpg_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `cpg_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `cpg_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `cpg_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cpg_id`),
  UNIQUE KEY `cpg_suiteid` (`cpg_suiteid`),
  KEY `cpg_status` (`cpg_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='应用分组表';


CREATE TABLE `oa_common_pm` (
  `pm_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `cp_pluginid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '插件id',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名',
  `from_uid` int(10) unsigned NOT NULL COMMENT '发送者uid',
  `from_username` varchar(54) NOT NULL COMMENT '发送者名称',
  `pm_title` varchar(255) NOT NULL DEFAULT '' COMMENT '消息标题',
  `pm_message` text NOT NULL COMMENT '消息内容',
  `pm_params` varchar(255) NOT NULL DEFAULT '' COMMENT '链接所需参数',
  `pm_isread` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已读, 0: 未读, 1: 已读',
  `pm_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `pm_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `pm_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `pm_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`pm_id`),
  KEY `m_uid` (`m_uid`),
  KEY `pm_isread` (`pm_isread`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='站内消息表';


CREATE TABLE `oa_common_region` (
  `cr_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `cr_parent_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '地区上级id, 为0时, 是主地区',
  `cr_name` varchar(120) NOT NULL DEFAULT '' COMMENT '地区名称',
  `cr_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=已删除',
  `cr_created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `cr_updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `cr_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cr_id`),
  KEY `cr_parent_id` (`cr_parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='店铺地区表';


CREATE TABLE `oa_common_setting` (
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


CREATE TABLE IF NOT EXISTS `oa_common_sqlrecord` (
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


CREATE TABLE `oa_common_shop` (
  `csp_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `csp_name` varchar(255) NOT NULL DEFAULT '' COMMENT '门店名称',
  `csp_address` varchar(255) NOT NULL DEFAULT '' COMMENT '门店位置',
  `csp_lng` decimal(9,6) NOT NULL DEFAULT '0.000000' COMMENT '所在经度',
  `csp_lat` decimal(9,6) NOT NULL DEFAULT '0.000000' COMMENT '所在纬度',
  `cr_id` int(10) NOT NULL DEFAULT '0' COMMENT '地区id',
  `csp_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `csp_created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `csp_updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `csp_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`csp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='店铺信息表';


CREATE TABLE `oa_common_syscache` (
  `csc_name` varchar(32) NOT NULL COMMENT '缓存文件名',
  `csc_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `csc_data` mediumblob NOT NULL COMMENT '数据',
  `csc_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `csc_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `csc_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `csc_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`csc_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `oa_common_userlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'uid',
  `year` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '年份',
  `month` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '月份',
  `day` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '日期',
  `week` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '星期值',
  `status` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态, 1: 创建; 2: 更新; 3: 删除;',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户活跃统计';


CREATE TABLE `oa_diy_data` (
  `dataid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '数据扩展id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'uid',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '表格tid',
  `dr_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据行id',
  `tc_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据字段id',
  `data_ch` varchar(255) NOT NULL DEFAULT '' COMMENT '非文本字段数据信息',
  `data_txt` text NOT NULL COMMENT '文本字段数据信息',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`dataid`),
  KEY `uid` (`uid`),
  KEY `data_ch` (`data_ch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据详情信息';


CREATE TABLE `oa_diy_row` (
  `dr_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '数据id',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据表id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户uid',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`dr_id`),
  KEY `status` (`status`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据表基本数据';


CREATE TABLE `oa_diy_table` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据表格的扩展表';


CREATE TABLE `oa_diy_tablecol` (
  `tc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '字段自增id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'uid',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据表格id',
  `field` varchar(60) NOT NULL DEFAULT '' COMMENT '字段名称',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据表字段信息';


CREATE TABLE `oa_diy_tablecolopt` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='表格列的可选项值';

CREATE TABLE `oa_common_department_connect` (
  `dcid` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `cd_id` int(10) NOT NULL DEFAULT '0' COMMENT '部门id',
  `m_uid` int(10) NOT NULL DEFAULT '0' COMMENT '负责人id',
  `m_username` varchar(255) NOT NULL COMMENT '负责人姓名',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`dcid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='部门负责人关联表';

CREATE TABLE `oa_common_department_permission` (
  `dpid` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `cd_id` int(10) NOT NULL DEFAULT '0' COMMENT '部门id',
  `per_id` int(10) NOT NULL DEFAULT '0' COMMENT '权限部门id',
  `per_name` varchar(255) NOT NULL COMMENT '权限部门名称',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`dpid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='部门权限关联表';

CREATE TABLE `oa_common_label` (
  `laid` int(10) NOT NULL AUTO_INCREMENT COMMENT '标签自增id',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '标签名',
  `displayorder` int(10) NOT NULL DEFAULT '0' COMMENT '排序号',
  `lastordertime` varchar(255) NOT NULL COMMENT '最后一次更改排序时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`laid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='通讯录标签表';

CREATE TABLE `oa_common_label_member` (
  `lamid` int(10) NOT NULL AUTO_INCREMENT COMMENT '标签人员自增id',
  `laid` int(10) NOT NULL DEFAULT '0' COMMENT '标签id',
  `m_uid` int(10) NOT NULL DEFAULT '0' COMMENT '标签里的人员id',
  `m_username` varchar(255) NOT NULL DEFAULT '0' COMMENT '标签里的人姓名',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`lamid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签人员关联表';

CREATE TABLE `oa_member` (
  `m_uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户UID',
  `m_weixin` varchar(64) NOT NULL DEFAULT '' COMMENT '微信id',
  `m_openid` char(64) NOT NULL DEFAULT '' COMMENT '唯一键值',
  `pay_openid` char(64) NOT NULL DEFAULT '' COMMENT '微信企业支付用户openid',
  `m_mobilephone` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `m_email` char(80) NOT NULL DEFAULT '' COMMENT '邮箱',
  `m_unionid` char(32) NOT NULL DEFAULT '' COMMENT '微信unionid',
  `m_active` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '在职状态，1=在职，0=离职',
  `m_username` char(54) NOT NULL DEFAULT '' COMMENT '姓名',
  `m_index` char(4) NOT NULL DEFAULT '' COMMENT '名字字母索引字符，同通讯录cab_index',
  `m_password` char(32) NOT NULL DEFAULT '' COMMENT '用户密码',
  `m_number` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '员工工号',
  `m_admincp` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为管理员，0=否，1=是',
  `m_groupid` mediumint(9) NOT NULL DEFAULT '0' COMMENT '用户组ID',
  `cd_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '部门id，用户所在的主部门ID',
  `cj_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '职位id',
  `m_gender` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别, 0: 未知, 1:男; 2:女',
  `m_face` char(255) NOT NULL DEFAULT '' COMMENT '用户头像',
  `m_facetime` int(10) unsigned NOT NULL COMMENT '头像更新时间',
  `m_salt` char(6) NOT NULL DEFAULT '' COMMENT '密码干扰串',
  `m_displayorder` tinyint(3) NOT NULL DEFAULT '0' COMMENT '排序',
  `m_qywxstatus` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '企业微信关注状态：1已关注，2已冻结， 4=未关注',
  `m_source` tinyint(1) NOT NULL DEFAULT '2' COMMENT '用户来源1:扫码,2:系统,3:其它',
  `m_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新, 3=待验证，4=已删除',
  `m_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `m_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `m_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`m_uid`),
  KEY `m_status` (`m_status`),
  KEY `m_mobilephone` (`m_mobilephone`),
  KEY `m_email` (`m_email`),
  KEY `m_openid` (`m_openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户表';


CREATE TABLE `oa_member_department` (
  `md_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `mp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '职务id',
  `cd_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '部门ID',
  `md_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `md_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `md_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `md_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`md_id`),
  KEY `cd_id` (`cd_id`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户与部门关联表';


CREATE TABLE `oa_member_field` (
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `mf_address` varchar(255) NOT NULL DEFAULT '' COMMENT '住址',
  `mf_idcard` varchar(20) NOT NULL DEFAULT '' COMMENT '身份证号码',
  `mf_telephone` varchar(64) NOT NULL DEFAULT '' COMMENT '固定电话',
  `mf_qq` varchar(12) NOT NULL DEFAULT '' COMMENT 'QQ号码',
  `mf_weixinid` varchar(64) NOT NULL DEFAULT '' COMMENT '微信号',
  `mf_birthday` varchar(10) NOT NULL DEFAULT '' COMMENT '生日',
  `mf_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '其他备注',
  `mf_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `mf_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mf_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `mf_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `mf_devicetype` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '最后登陆的设备类型, 1=h5, 2=pc, 3=android, 4=ios',
  `mf_notificationtotal` int(11) NOT NULL DEFAULT '0' COMMENT '消息数目统计',
  `mf_ext1` varchar(500) NOT NULL DEFAULT '' COMMENT '扩展字段1',
  `mf_ext2` varchar(500) NOT NULL DEFAULT '' COMMENT '扩展字段2',
  `mf_ext3` varchar(500) NOT NULL DEFAULT '' COMMENT '扩展字段3',
  `mf_ext4` varchar(500) NOT NULL DEFAULT '' COMMENT '扩展字段4',
  `mf_ext5` varchar(500) NOT NULL DEFAULT '' COMMENT '扩展字段5',
  `mf_ext6` varchar(500) NOT NULL DEFAULT '' COMMENT '扩展字段6',
  `mf_ext7` varchar(500) NOT NULL DEFAULT '' COMMENT '扩展字段7',
  `mf_ext8` varchar(500) NOT NULL DEFAULT '' COMMENT '扩展字段8',
  `mf_ext9` varchar(500) NOT NULL DEFAULT '' COMMENT '扩展字段9',
  `mf_ext10` varchar(500) NOT NULL DEFAULT '' COMMENT '扩展字段10',
  `mf_leader` varchar(255) NOT NULL DEFAULT '0' COMMENT '直属领导',
  UNIQUE KEY `m_uid` (`m_uid`),
  KEY `mf_status` (`mf_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户信息表';


CREATE TABLE `oa_member_loginqrcode` (
  `auth_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `m_uid` int(10) NOT NULL DEFAULT '0' COMMENT '登录人的ID',
  `authcode` char(32) NOT NULL DEFAULT '' COMMENT 'authcode密钥',
  `errmsg` char(64) NOT NULL DEFAULT '' COMMENT '错误信息',
  `state` int(3) NOT NULL DEFAULT '0' COMMENT '登录状态：0,已获取密钥;1,已扫描; 2,已登录',
  `ip` char(15) NOT NULL DEFAULT '0' COMMENT '登录的IP地址',
  `status` int(3) NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常; 2=更新; 3=已删除',
  `created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`auth_id`),
  UNIQUE KEY `authcode` (`authcode`),
  KEY `state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PC auth登录';


CREATE TABLE `oa_member_position` (
  `mp_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `mp_name` varchar(500) NOT NULL COMMENT '职务',
  `mp_parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '职务父级id',
  `mp_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `mp_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mp_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `mp_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`mp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户职务表';


CREATE TABLE `oa_member_search` (
  `ms_id` mediumint(9) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `ms_message` text NOT NULL COMMENT '名片信息集合',
  `ms_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `ms_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ms_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ms_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ms_id`),
  UNIQUE KEY `m_uid` (`m_uid`),
  KEY `ms_status` (`ms_status`),
  FULLTEXT KEY `ms_message` (`ms_message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='通讯录详情搜索表';


CREATE TABLE `oa_member_setting` (
  `m_key` varchar(50) NOT NULL COMMENT '变量名',
  `m_value` text NOT NULL COMMENT '值',
  `m_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `m_comment` text NOT NULL COMMENT '说明',
  `m_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `m_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `m_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `m_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`m_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户设置表';


CREATE TABLE `oa_member_share` (
  `msh_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL COMMENT '用户UID',
  `msh_fields` text NOT NULL COMMENT '被分享的用户属性',
  `msh_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `msh_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `msh_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `msh_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`msh_id`),
  KEY `msh_status` (`msh_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户信息分享表';

CREATE TABLE `oa_member_browsepermission` (
  `mb_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) NOT NULL DEFAULT '0' COMMENT '人员ID',
  `mb_m_uid` text NOT NULL COMMENT '可查看的人员ID',
  `mb_cd_id` text NOT NULL COMMENT '可查看的部门ID',
  `mb_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新, 3=已删除',
  `mb_created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mb_updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `mb_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='人员的浏览权限';


CREATE TABLE `oa_msg_queue` (
  `mq_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `mq_touser` text NOT NULL COMMENT '接收用户的userid字串',
  `mq_toparty` text NOT NULL COMMENT '组标识或tag标识',
  `mq_msgtype` varchar(32) NOT NULL COMMENT '消息类型, text/image/voice/video/news',
  `mq_agentid` varchar(32) NOT NULL COMMENT '应用id',
  `cp_pluginid` int(11) NOT NULL DEFAULT '0' COMMENT '插件id',
  `mq_message` text NOT NULL COMMENT '消息内容',
  `mq_failtimes` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '发送失败次数',
  `mq_sendtime` int(10) unsigned NOT NULL COMMENT '消息发送时间',
  `mq_interval` int(10) unsigned NOT NULL COMMENT '重复发送时, 间隔时长, 单位:s',
  `mq_repeats` int(10) unsigned NOT NULL COMMENT '重复次数, -1:永久重复发送, 0 已结束发送',
  `mq_func` varchar(32) NOT NULL COMMENT '调用方法',
  `mq_vars` varchar(255) NOT NULL COMMENT '方法参数',
  `mq_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=发送成功, 3=发送失败，4=已删除',
  `mq_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `mq_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `mq_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`mq_id`),
  KEY `wsq_status` (`mq_status`,`mq_created`,`mq_failtimes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='给用户发送消息的队列表';


CREATE TABLE `oa_suite` (
  `su_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `suiteid` varchar(255) NOT NULL DEFAULT '' COMMENT '套件id',
  `auth_corpid` varchar(255) NOT NULL DEFAULT '' COMMENT '授权方企业id',
  `permanent_code` varchar(255) NOT NULL DEFAULT '' COMMENT '永久授权码',
  `access_token` varchar(255) NOT NULL DEFAULT '' COMMENT 'access_token',
  `expires` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
  `authinfo` text NOT NULL COMMENT '授权信息',
  `jsapi_ticket` varchar(255) NOT NULL DEFAULT '' COMMENT 'jsapi ticket缓存',
  `jsapi_ticket_expire` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'jsapi ticket过期时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '数据状态, 1: 新建; 2: 更新; 3: 删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`su_id`),
  UNIQUE KEY `suiteid` (`suiteid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='套件信息';


CREATE TABLE `oa_weixin_location` (
  `wl_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名',
  `wl_latitude` decimal(9,6) NOT NULL DEFAULT '0.000000' COMMENT '地理位置经度',
  `wl_longitude` decimal(9,6) NOT NULL DEFAULT '0.000000' COMMENT '地理位置纬度',
  `wl_precision` float NOT NULL DEFAULT '0' COMMENT '地理位置精度',
  `wl_ip` varchar(15) NOT NULL DEFAULT '0.0.0.0' COMMENT '当前IP',
  `wl_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `wl_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `wl_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `wl_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`wl_id`),
  KEY `wl_status` (`wl_status`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户位置记录表';


CREATE TABLE `oa_weixin_msg` (
  `wm_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `wm_msgid` char(32) NOT NULL DEFAULT '' COMMENT '消息ID',
  `wm_fromusername` varchar(32) NOT NULL DEFAULT '' COMMENT '来源微信openid',
  `wm_createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息创建时间',
  `wm_msg` text NOT NULL COMMENT '来自微信的完整xml',
  `wm_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `wm_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `wm_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `wm_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`wm_id`),
  KEY `wm_msgid` (`wm_msgid`),
  KEY `wm_status` (`wm_status`),
  KEY `wm_fromusername` (`wm_fromusername`,`wm_createtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='来自微信的消息记录表';


CREATE TABLE `oa_weixin_qrcode` (
  `wq_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '用户名',
  `wq_ip` varchar(15) NOT NULL DEFAULT '' COMMENT '当前IP',
  `wq_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除',
  `wq_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `wq_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `wq_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`wq_id`),
  KEY `wq_status` (`wq_status`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户二维码记录表';


CREATE TABLE `oa_weixin_send_queue` (
  `wsq_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `wsq_touser` text NOT NULL COMMENT '接收用户的userid字串',
  `wsq_toparty` text NOT NULL,
  `wsq_msgtype` varchar(32) NOT NULL COMMENT '消息类型',
  `wsq_agentid` varchar(32) NOT NULL COMMENT '应用id',
  `wsq_message` text NOT NULL COMMENT '消息内容',
  `wsq_failtimes` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '发送失败次数',
  `wsq_sendtime` int(10) unsigned NOT NULL COMMENT '消息发送时间',
  `wsq_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=发送成功, 3=发送失败，4=已删除',
  `wsq_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `wsq_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `wsq_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`wsq_id`),
  KEY `wsq_status` (`wsq_status`,`wsq_created`,`wsq_failtimes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='给用户发送消息的队列表';


CREATE TABLE `oa_weixin_setting` (
  `ws_key` varchar(50) NOT NULL COMMENT '变量名',
  `ws_value` text NOT NULL COMMENT '值',
  `ws_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `ws_comment` text NOT NULL COMMENT '说明',
  `ws_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `ws_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ws_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ws_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ws_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信相关配置表';


CREATE TABLE `oa_xinge_queue` (
  `xgq_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `xgq_touser` varchar(32) NOT NULL DEFAULT '' COMMENT '接收用户的字串, md5(domain+uid)',
  `xgq_msgtype` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '消息类型, 1=notification, 2=massage',
  `xgq_pluginid` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '应用id, 0=系统, 其余对应oa_common_plugin的cp_pluginid ',
  `xgq_itemid` int(11) DEFAULT NULL COMMENT '信息id',
  `xgq_message` text NOT NULL COMMENT '消息内容',
  `xgq_title` varchar(255) NOT NULL DEFAULT '' COMMENT '消息标题',
  `xgq_failtimes` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '发送失败次数',
  `xgq_sendtime` int(10) unsigned NOT NULL COMMENT '消息发送时间',
  `xgq_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=发送成功, 3=发送失败，4=已删除',
  `xgq_created` int(10) unsigned NOT NULL COMMENT '创建时间',
  `xgq_updated` int(10) unsigned NOT NULL COMMENT '更新时间',
  `xgq_deleted` int(10) unsigned NOT NULL COMMENT '删除时间',
  `xgq_devicetype` tinyint(1) unsigned NOT NULL COMMENT '设备类型, 1=h5, 2=pc, 3=android, 4=ios',
  `xgq_fromuser` varchar(32) NOT NULL DEFAULT '' COMMENT '发送用户字串',
  `xgq_notificationtotal` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xgq_id`),
  KEY `xgq_status` (`xgq_status`,`xgq_created`,`xgq_failtimes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='信鸽发送消息队列表';


CREATE TABLE `oa_year2014` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `appkey` char(32) NOT NULL DEFAULT '' COMMENT '缓存键名',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属人员uid。所有人的公共缓存=0',
  `data` text NOT NULL COMMENT '缓存的数据',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据储存状态',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '缓存时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `uid_key` (`uid`,`appkey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='2014年终总结数据缓存表';


-- 2015-05-22 01:18