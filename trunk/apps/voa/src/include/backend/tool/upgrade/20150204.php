<?php
/**
 * 20150204.php
 * 超级报表应用上线
 * Create By Deepseath
 * $Author$
 * $Id$
 */
error_reporting(E_ALL);
class execute {

	/** 数据库操作对象 */
	protected $_db = null;
	/** 表前缀 */
	protected $_tablepre = 'oa_';
	/** 当前站点系统设置 */
	protected $_settings = array();
	/** 来自命令行请求的参数 */
	protected $_options = array();
	/** 来自触发此脚本的父级参数 */
	protected $_params = array();
	/** 储存已执行的SQL语句，文件路径 */
	protected $_sql_logfile = '';
	/** 储存已执行SQL语句的恢复语句，文件路径 */
	protected $_sql_restore_logfile = '';

	public function __construct() {
	}

	/**
	 * 初始化环境参数
	 * @param object $db 数据库操作对象
	 * @param string $tablepre 表前缀
	 * @param array $settings 当前站点的setting
	 * @param array $options 传输进来的外部参数
	 * @param array $params 一些环境参数，来自触发执行本脚本
	 * @see voa_backend_tool_upgrade::main()
	 */
	public function init($db, $tablepre, $settings, $options, $params) {
		$this->_db = $db;
		$this->_tablepre = $tablepre;
		$this->_settings = $settings;
		$this->_options = $options;
		$this->_params = $params;

	}

	/**
	 * 脚本执行的主方法，不同的升级脚本具体操作动作不相同
	 * @return void
	 */
	public function run() {

		error_reporting(E_ALL);

		$steps = array(
			'create_place_table',// 创建门店相关数据表
			'open_superreport',// 启用应用
			'default_data', // 应用默认数据
			'add_diy_data',  // 初始化DIY数据
			'cache_clear' // 清理cpmenu菜单缓存
		);

		foreach ($steps as $_step) {
			$classname = '_'.$_step;
			$this->$classname();
		}

	}

	/**
	 * 创建门店相关数据表
	 * @return boolean
	 */
	protected function _create_place_table() {

		$this->_db->query("CREATE TABLE `oa_common_place` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='场所-地点表'");

		$this->_db->query("CREATE TABLE `oa_common_place_member` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='场所-场所相关人员表'");

		$this->_db->query("CREATE TABLE `oa_common_place_region` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='场所-区域表'");

		$this->_db->query("CREATE TABLE `oa_common_place_setting` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 99=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='场地-设置表'");

		$this->_db->query("CREATE TABLE `oa_common_place_type` (
  `placetypeid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '类型名称',
  `levels` text NOT NULL COMMENT '该类型下的区域、场所相关人员级别权限称谓',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '数据状态:1=新创建,2=已更新,99=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`placetypeid`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='场所-地点表'");

		$this->_db->query("INSERT INTO `oa_common_place_setting` (`key`, `value`, `type`, `comment`, `status`, `created`, `updated`, `deleted`) VALUES
('allow_level_name_custom',	'0',	0,	'是否允许自定义级别权限称谓。0=不允许，1=允许',	1,	0,	0,	0),
('level_default_name',	'a:2:{i:1;s:9:\"负责人\";i:2;s:9:\"相关人\";}',	1,	'场地相关人员默认级别名称，需要与place_member表level字段定义范围相同。',	1,	0,	0,	0),
('placetypeid_shop',	'1',	0,	'门店所在场所类型ID',	1,	0,	0,	0),
('place_address_length_max',	'240',	0,	'场地地址最大字符数',	1,	0,	0,	0),
('place_address_length_min',	'2',	0,	'场地地址最短字符数',	1,	0,	0,	0),
('place_master_count_max',	'1',	0,	'场所负责人最多允许设置数',	1,	0,	0,	0),
('place_master_count_min',	'0',	0,	'场所负责人最少必须设置数',	1,	0,	0,	0),
('place_name_length_max',	'240',	0,	'场地名称最长字符数',	1,	0,	0,	0),
('place_name_length_min',	'2',	0,	'场地名称最短字符数',	1,	0,	0,	0),
('place_normal_count_max',	'50',	0,	'场地相关人员最多允许设置数',	1,	0,	0,	0),
('place_normal_count_min',	'0',	0,	'场地相关人员最少必须设置数',	1,	0,	0,	0),
('region_deepin_max',	'3',	0,	'最多允许创建分区的级别数',	1,	0,	0,	0),
('region_master_count_max',	'1',	0,	'分区负责人最多允许设置数',	1,	0,	0,	0),
('region_master_count_min',	'0',	0,	'分区负责人最少必须设置数',	1,	0,	0,	0),
('region_name_length_max',	'80',	0,	'分区名称最长允许的字符数',	1,	0,	0,	0),
('region_name_length_min',	'2',	0,	'分区名称要求的最短字符数',	1,	0,	0,	0),
('region_normal_count_max',	'0',	0,	'分区相关人员最多允许设置数',	1,	0,	0,	0),
('region_normal_count_min',	'0',	0,	'分区相关人员最少必须设置数',	1,	0,	0,	0),
('type_max_count',	'10',	0,	'最多允许创建的类型数量',	1,	0,	0,	0),
('type_name_length_max',	'32',	0,	'类型名称最长允许的字符数',	1,	0,	0,	0),
('type_name_length_min',	'2',	0,	'分区名称要求的最短字符数',	1,	0,	0,	0)");

		$this->_db->query("INSERT INTO `oa_common_place_type` (`placetypeid`, `name`, `levels`, `status`, `created`, `updated`, `deleted`) VALUES
(1,	'门店',	'a:2:{i:1;s:9:\"负责人\";i:2;s:9:\"相关人\";}',	1,	1417177966,	0,	0)");

		return true;
	}


	/**
	 * 启用应用
	 */
	protected function _open_superreport() {

		/**
		 * 思路，先尝试删除，再进行添加，避免某些站点不存在此应用记录的情况
		 */
		$timestamp = time();
		// 先尝试删除
		$this->_db->query("DELETE FROM `oa_common_plugin` WHERE `cp_identifier`='superreport'");
		// 新增
		$this->_db->query("INSERT INTO `oa_common_plugin` (`cp_identifier`,
				`cmg_id`, `cpg_id`, `cp_suiteid`, `cp_agentid`, `cp_displayorder`,
				`cp_available`, `cp_adminid`, `cp_name`, `cp_icon`, `cp_description`,
				`cp_datatables`, `cp_directory`, `cp_url`, `cp_version`,
				`cp_lastavailable`, `cp_lastopen`, `cyea_id`, `cp_status`,
				`cp_created`, `cp_updated`, `cp_deleted`) VALUES (
				'superreport', 1, 3, '', '', 1026, 0, 0, '超级报表', 'showroom.png',
				'门店每日营业数据报表', 'superreport*', 'superreport', 'superreport.php', '0.1',
				0, 0, 0, 1, {$timestamp}, 0, 0)");

		return true;
	}

	/**
	 * 导入应用的默认数据
	 * @return boolean
	 */
	protected function _default_data() {

		$this->_db->query("INSERT INTO `oa_superreport_setting` (`key`, `value`, `type`, `comment`, `status`, `created`, `updated`, `deleted`) VALUES
('comment_perpage',	'10',	0,	'',	1,	0,	0,	0),
('placetypeid',	'1',	0,	'',	1,	0,	0,	0),
('report_perpage',	'12',	0,	'',	1,	0,	0,	0),
('reserve_field',	'a:1:{i:0;a:3:{s:9:\"fieldname\";s:9:\"营业额\";s:4:\"unit\";s:3:\"元\";s:4:\"type\";s:3:\"int\";}}',	0,	'后台报表模板保留字段',	1,	0,	0,	0),
('volume',	'6',	0,	'营业额字段在diy_tablecol中的主键值',	1,	0,	0,	0)");

		return true;
	}

	/**
	 * 清理缓存
	 */
	protected function _cache_clear() {

		// 当前站点的缓存目录
		$cachedir = $this->_params['cachedir'];

		// 清理应用信息缓存
		@unlink($cachedir.DIRECTORY_SEPARATOR.'plugin.php');
		// 试图清理培训应用的设置缓存
		@unlink($cachedir.DIRECTORY_SEPARATOR.'plugin.superreport.setting.php');

		// 读取缓存目录下的文件
		$handle = opendir($cachedir);
		// 清理后台菜单缓存文件
		if ($handle) {
			while (false !== ($file = readdir($handle))) {

				// 判断是否是有效的菜单缓存文件
				if ($file == 'cpmenu.php' || preg_match('/^adminergroupcpmenu\.\d+/', $file)) {
					// 删除
					unlink($cachedir.DIRECTORY_SEPARATOR.$file);
					break;
				}
			}
		}

	}


	protected  function _add_diy_data() {

		//添加表格
		$this->_db->query("INSERT INTO `oa_diy_table` (`uid`, `cp_identifier`, `tunique`,
				`tname`, `t_desc`, `status`, `created`, `updated`, `deleted`) VALUES
				(0,	'superreport',	'superreport',	'superreport',	'',	1,	1422323630,	0,	0)");

		$tid = $this->_db->insert_id();
		if (!$tid) {
			$tid = 1;
		}

		//添加字段
		$this->_db->query("INSERT INTO `oa_diy_tablecol` (`tc_id`, `uid`, `tid`, `field`, `fieldname`,
				`placeholder`, `tc_desc`, `ct_type`, `ftype`, `min`, `max`, `reg_exp`, `initval`, `unit`,
				 `orderid`, `required`, `tpladd`, `isuse`, `coltype`, `status`, `created`, `updated`, `deleted`)
				VALUES
				(6,	11,	{$tid},'','营业额','','',	'int',0,0,0,'',	'',	'元',0,	1,'',1,	1,1,1422362228,	0,0),
				(7,	11,	{$tid},'','到店人数',	'',	'',	'int',0,0,0,'',	'',	'个',0,	0,'',1,	2,1,1422362228,	0,	0),
				(8,	11,	{$tid},'','台桌',	'',	'',	'int',0,0,0,'',	'',	'台',2,	0,'',1,	2,	1,1422362228,	0,	0),
				(9,	11,	{$tid},'','开台数','','',	'int',0,0,0,'',	'',	'台',4,	0,'',1,	2,1,1422362228,	0,	0),
				(10,11,	{$tid},'','会员数','','',	'int',0,0,0,'',	'',	'位',5,	0,'',1,	2,1,1422362228,	0,	0),
				(11,11,	{$tid},'','会员消费金额','','','int',0,0,0,'',	'',	'元',6,	0,'',1,	2,1,1422362228,0,0),
				(12,11,	{$tid},'','工作汇报','','','text',	2,0,0,'','','',1,1,'',1,1,1,1422362228,	0,0);");
	}

}
