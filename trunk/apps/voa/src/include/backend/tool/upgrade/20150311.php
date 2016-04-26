<?php
/**
 * 20150311.php
 * 审批迭代
 * php -q APP_PATH/backend/tool.php -n upgrade -version 20150311 -epid vchangyi_oa_upgrade
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

	/** 当前升级的应用信息 */
	private $__plugin = array();

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

		// 获取应用信息
		$query = $this->_db->query("SELECT * FROM `oa_common_plugin` WHERE `cp_identifier`='askfor' LIMIT 1");
		$this->__plugin = $this->_db->fetch_array($query);

		// 判断应用表是否存在
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_askfor'");
		if ($this->_db->fetch_row($query)) {
			$file = APP_PATH."/data/upgrade/".$this->_options['version'].".log";
			$this->_params['upgrade']->rfwrite($file, "{$this->_params['dbname']}\t{$this->_settings['domain']}\n", 'a+');
			// 后台菜单
			$this->_plugin_cpmenu();
			// 应用表结构
			$this->_plugin_table();
			// 应用微信企业号自定义菜单
			$this->_plugin_wxqymenu();
		}

		// 公共表结构
		if (empty($this->_options['common_table_no_run'])) {
			$this->_common_table();
		}

		// 清理缓存
		$this->_cache_clear();
	}

	/**
	 * 后台菜单升级
	 */
	protected function _plugin_cpmenu() {

		// 删除旧有的菜单
		$this->_db->query("DELETE FROM `oa_common_cpmenu` WHERE ccm_operation='{$this->__plugin['cp_identifier']}'");
		$cp_pluginid = $this->__plugin['cp_pluginid'];
		// 添加新的菜单
		$this->_db->query("INSERT INTO `oa_common_cpmenu` (`cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_subnavdisplay`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`) VALUES
({$cp_pluginid}, 0, 'office', 'askfor', '', 'operation', 1, '审批', '', 1, 106, 1, 1, 1416879084, 0, 0),
({$cp_pluginid}, 0, 'office', 'askfor', 'list', 'subop', 1, '审批列表', 'fa-list', 1, 1001, 1, 1, 1416879084, 0, 0),
({$cp_pluginid}, 0, 'office', 'askfor', 'template', 'subop', 0, '审批流程', 'fa-list', 1, 1002, 1, 1, 1416879084, 0, 0),
({$cp_pluginid}, 0, 'office', 'askfor', 'view', 'subop', 0, '审批详情', 'fa-eye', 1, 1003, 0, 1, 1416879084, 0, 0),
({$cp_pluginid}, 0, 'office', 'askfor', 'delete', 'subop', 0, '删除审批', 'fa-trash-o', 1, 1004, 0, 1, 1416879084, 0, 0),
({$cp_pluginid}, 0, 'office', 'askfor', 'addtemplate', 'subop', 0, '添加审批流程', 'fa-add', 1, 1005, 0, 1, 1416879084, 0, 0),
({$cp_pluginid}, 0, 'office', 'askfor', 'edittemplate', 'subop', 0, '编辑审批流程', 'fa-edit', 1, 1006, 0, 1, 1416879084, 0, 0),
({$cp_pluginid}, 0, 'office', 'askfor', 'deletetemplate', 'subop', 0, '删除审批流程', 'fa-trash-o', 1, 1007, 0, 1, 1416879084, 0, 0)");

		return true;
	}

	/**
	 * 数据库表更新
	 */
	protected function _plugin_table() {

		// 新增模板自定义字段表
		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_askfor_customcols`(
`afcc_id` int(10) unsigned NOT NULL  auto_increment COMMENT '自增ID' ,
`aft_id` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '审批模板ID' ,
`field` char(30) COLLATE utf8_general_ci NOT NULL  DEFAULT '' COMMENT '字段' ,
`name` char(30) COLLATE utf8_general_ci NOT NULL  DEFAULT '' COMMENT '字段名' ,
`required` tinyint(1) unsigned NOT NULL  DEFAULT '0' COMMENT '是否必填：0，不是；1：是' ,
`type` tinyint(1) unsigned NOT NULL  DEFAULT '1' COMMENT '字段类型：1，单行文本；3，数字；2，多行文本；4，图片' ,
`orderid` tinyint(3) unsigned NOT NULL  DEFAULT '0' COMMENT '排序' ,
`afcc_status` tinyint(3) unsigned NOT NULL  DEFAULT '1' COMMENT '记录状态, 1=正常; 2=更新; 99=已删除' ,
`afcc_created` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '创建时间' ,
`afcc_updated` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '更新时间' ,
`afcc_deleted` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '删除时间' ,
PRIMARY KEY (`afcc_id`) ,
KEY `aft_id`(`aft_id`)
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COMMENT='审批模板自定义字段表'");

		// 新增 审批模板自定义字段值表
		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_askfor_customdata`(
`afcd_id` int(10) unsigned NOT NULL  auto_increment ,
`af_id` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '审批ID' ,
`field` varchar(30) COLLATE utf8_general_ci NOT NULL  DEFAULT '' COMMENT '字段' ,
`name` varchar(30) COLLATE utf8_general_ci NOT NULL  DEFAULT '' COMMENT '字段名' ,
`value` text COLLATE utf8_general_ci NOT NULL  COMMENT '字段值' ,
`afcd_status` tinyint(3) unsigned NOT NULL  DEFAULT '1' COMMENT '记录状态, 1=正常; 2=更新; 99=已删除' ,
`afcd_created` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '创建时间' ,
`afcd_updated` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '更新时间' ,
`afcd_deleted` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '删除时间' ,
PRIMARY KEY (`afcd_id`)
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COMMENT='审批模板自定义字段值表'");

		// 新增审批模板表
		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_askfor_template`(
`aft_id` int(11) unsigned NOT NULL  auto_increment COMMENT '自增ID' ,
`name` varchar(50) COLLATE utf8_general_ci NOT NULL  DEFAULT '' COMMENT '审批模板名' ,
`creator` varchar(30) COLLATE utf8_general_ci NOT NULL  DEFAULT '' COMMENT '创建人' ,
`approvers` text COLLATE utf8_general_ci NOT NULL  COMMENT '系列化数组，审批人列表' ,
`is_use` tinyint(1) NOT NULL  DEFAULT '1' COMMENT '是否启用：0，不启用；1，启用' ,
`upload_image` tinyint(1) unsigned NOT NULL  DEFAULT '0' COMMENT '是否上传图片：0，不上传；1，上传' ,
`orderid` tinyint(3) unsigned NOT NULL  DEFAULT '1' COMMENT '排序' ,
`aft_status` tinyint(3) unsigned NOT NULL  DEFAULT '1' COMMENT '记录状态, 1=正常; 2=更新; 99=已删除' ,
`aft_created` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '创建时间' ,
`aft_updated` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '更新时间' ,
`aft_deleted` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '删除时间' ,
PRIMARY KEY (`aft_id`)
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COMMENT='审批模板表'");

		// 审批表新增审批流程ID
		$this->_db->query("ALTER TABLE `oa_askfor`
ADD COLUMN `aft_id` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '审批模板ID' after `af_message`,
CHANGE `afp_id` `afp_id` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '当前进度ID' after `aft_id`,
CHANGE `af_status` `af_status` tinyint(3) unsigned   NOT NULL DEFAULT '1' COMMENT '记录状态, 1=审批申请中，2=审批通过, 3=转审批, 4=审批不通过, 5=草稿，6=已催办，7=已撤销，8=已删除' after `afp_id`,
CHANGE `af_created` `af_created` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '创建时间' after `af_status`,
CHANGE `af_updated` `af_updated` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '更新时间' after `af_created`,
CHANGE `af_deleted` `af_deleted` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '删除时间' after `af_updated`");

		// 附件表数据状态字段更改
		$this->_db->query("ALTER TABLE `oa_askfor_attachment`
CHANGE `afat_status` `afat_status` tinyint(3) unsigned   NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常; 2=更新; 99=已删除' after `m_username`");

		// 进程表字段更改
		$this->_db->query("ALTER TABLE `oa_askfor_proc`
ADD COLUMN `is_active` tinyint(1) unsigned   NOT NULL DEFAULT '0' COMMENT '用于转审批（是否审批到达此人）：0，未到达；1，到达' after `afp_note`,
CHANGE `afp_status` `afp_status` tinyint(3) unsigned   NOT NULL DEFAULT '1' COMMENT '记录状态, 1=审核中，2=审核通过, 3=转审批, 4=审核不通过, 5=抄送，6=已催办，7=已撤销，8=已删除' after `is_active`,
CHANGE `afp_created` `afp_created` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '创建时间' after `afp_status`,
CHANGE `afp_updated` `afp_updated` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '更新时间' after `afp_created`,
CHANGE `afp_deleted` `afp_deleted` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '删除时间' after `afp_updated`");

		return true;
	}

	/**
	 * 更新微信企业号的自定义菜单
	 */
	protected function _plugin_wxqymenu() {

		$api_url = config::get(startup_env::get('app_name') . '.oa_http_scheme').$this->_settings['domain'].'/api/common/post/updatewxqymenu/';

		$timestamp = startup_env::get('timestamp');
		$crypt_xxtea = new crypt_xxtea();
		$hash = rbase64_encode($crypt_xxtea->encrypt(md5($timestamp)));

		$result = array();
		$post = array(
			'pluginid' => $this->__plugin['cp_pluginid'],
			'time' => $timestamp,
			'hash' => $hash
		);
		if (!$this->__get_json_by_post($result, $api_url, $post)) {
			logger::error($api_url."||".print_r($result, true));
		}

		return true;
	}

	/**
	 * 清理缓存
	 */
	protected function _cache_clear() {

		// 当前站点的缓存目录
		$cachedir = $this->_params['cachedir'];

		// 读取缓存目录下的文件
		$handle = opendir($cachedir);
		// 清理后台菜单缓存文件
		if ($handle) {
			while (false !== ($file = readdir($handle))) {

				// 判断是否是有效的菜单缓存文件
				if ($file == 'cpmenu.php' || preg_match('/^adminergroupcpmenu\.\d+/', $file)) {
					// 删除
					unlink($cachedir.DIRECTORY_SEPARATOR.$file);
				}
			}
		}

	}

	/**
	 * 读取远程api数据
	 * @param unknown $data
	 * @param unknown $url
	 * @param string $post
	 * @return boolean
	 */
	private function __get_json_by_post(&$data, $url, $post = '') {
		$snoopy = new snoopy();
		$result = $snoopy->submit($url, $post);
		/** 如果读取错误 */
		if (!$result || 200 != $snoopy->status) {
			logger::error('$snoopy->submit error: '.$url.'|'.$result.'|'.$snoopy->status);
			return false;
		}

		/** 解析 json */
		$data = (array)json_decode($snoopy->results, true);
		if (empty($data) || !empty($data['errcode'])) {
			logger::error('$snoopy->submit error: '.$url.'|'.$snoopy->results.'|'.$snoopy->status);
			return false;
		}

		return true;
	}

	/**
	 * 加入场所管理相关数据表
	 */
	protected function _common_table() {

		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_common_place` (
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

		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_common_place_member` (
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

		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_common_place_region` (
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

		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_common_place_setting` (
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

		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_common_place_type` (
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

		$this->_db->query("REPLACE INTO `oa_common_place_setting` (`key`, `value`, `type`, `comment`, `status`, `created`, `updated`, `deleted`) VALUES
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

		$this->_db->query("REPLACE INTO `oa_common_place_type` (`placetypeid`, `name`, `levels`, `status`, `created`, `updated`, `deleted`) VALUES
(1,	'门店',	'a:2:{i:1;s:9:\"负责人\";i:2;s:9:\"相关人\";}',	1,	1417177966,	0,	0)");

		return true;

	}

}
