<?php
/**
 * 20150820.php
 * 微信考勤
 * cd C:\wamp\www\h5\trunk\apps\voa\backend\
 * php -q tool.php -n upgrade -version 20150820 -epid vchangyi_oa
 * Create By LI
 * $Author$
 * $Id$
 */
class execute {
	
	/**
	 * 数据库操作对象
	 */
	protected $_db = null;
	/**
	 * 表前缀
	 */
	protected $_tablepre = 'oa_';
	/**
	 * 当前站点系统设置
	 */
	protected $_settings = array ();
	/**
	 * 来自命令行请求的参数
	 */
	protected $_options = array ();
	/**
	 * 来自触发此脚本的父级参数
	 */
	protected $_params = array ();
	/**
	 * 储存已执行的SQL语句，文件路径
	 */
	protected $_sql_logfile = '';
	/**
	 * 储存已执行SQL语句的恢复语句，文件路径
	 */
	protected $_sql_restore_logfile = '';
	
	/**
	 * 当前升级的应用信息
	 */
	private $__plugin = array ();
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
		error_reporting ( E_ALL );
		//判断应用表是否存在
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_record'");

		if($this->_db->fetch_row($query)){
			
			$this->_plugin_table ();
			$this->_plugin_cpmenu();
		}	
		// 公共表结构
		$this->_common_table ();
		// 清理缓存
		$this->_cache_clear ();
	}
	
	/**
	 * 后台菜单升级
	 */
	protected function _plugin_cpmenu() {
		// oa_common_cpmenu表添加数据
		$this->_db->query ( "INSERT INTO `oa_common_cpmenu` (`ccm_id`, `cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_subnavdisplay`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`) VALUES
(null,	14,	0,	'office',	'sign',	'blist',	'subop',	0,	'班次安排',	'fa-gear',	103,	1014,	1,	1,	1436771561,	1436771561,	0),
(null,	14,	1,	'office',	'sign',	'badd',	'subop',	0,	'添加班次',	'fa-gear',	103,	1014,	0,	1,	1436771561,	1436771561,	0),
(null,	14,	1,	'office',	'sign',	'bdelete',	'subop',	0,	'删除班次',	'fa-times',	103,	1014,	0,	1,	1436771561,	1436771561,	0),
(null,	14,	1,	'office',	'sign',	'updetail',	'subop',	0,	'外勤详情',	'fa-eye',	103,	1014,	0,	1,	1436771561,	1436771561,	0);" );
		return true;
	}
	
	/**
	 * 应用表结构升级
	 */
	protected function _plugin_table() {
	
		//获取用户旧数据
		//$serv_set = &service::factory ( 'voa_s_oa_sign_setting' );
		$sql = "select * from oa_sign_setting";
		$query = $this->_db->query($sql);
		//$info = $serv_set->list_all ();
		$info = array();
		while ($row = $this->_db->fetch_array($query)) {
			$info[$row['ss_key']] = $row;
		}
	
		$work_begin_hi = $info ['work_begin_hi'] ['ss_value'];
		$work_end_hi = $info ['work_end_hi'] ['ss_value'];
		$late_range = ceil($info ['late_range'] ['ss_value']/60);
		$leave_early_range = ceil($info ['leave_early_range'] ['ss_value']/60);
		$work_days = $info ['work_days'] ['ss_value'];
		$start_begin = startup_env::get('timestamp');
		// 格式时间 09:30 -> 930
		$work_begin_hi = (int)implode ( '', explode ( ':', $work_begin_hi ) );
		$work_end_hi = (int)implode ( '', explode ( ':', $work_end_hi ) );
		//获取最顶级部门id
		$q = $this->_db->query("SELECT * FROM `oa_common_department` WHERE cd_upid = 0 AND cd_status<".voa_d_oa_common_department::STATUS_REMOVE);
			$top_id = 0;
			while ($row = $this->_db->fetch_array($q)) {
				$top_id = $row['cd_id'];
			}
			
		//新建外出考勤附件表
		$this->_db->query ( "CREATE TABLE `oa_sign_attachment` (
  `said` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `outid` int(10) unsigned NOT NULL COMMENT '外部考勤记录id',
  `atid` varchar(255) NOT NULL COMMENT '附件id',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`said`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='外出考勤附件表';" );
		// 新建班次表
		$this->_db->query ( "CREATE TABLE `oa_sign_batch` (
  `sbid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '班次id',
  `name` varchar(255) NOT NULL COMMENT '班次名称',
  `work_begin` int(10) unsigned NOT NULL COMMENT '工作开始时间',
  `work_end` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '工作结束时间',
  `work_days` varchar(255) NOT NULL COMMENT '工作日',
  `start_begin` int(10) unsigned NOT NULL COMMENT '启用时间',
  `start_end` int(10) unsigned NOT NULL COMMENT '截止时间',
  `longitude` double NOT NULL COMMENT '经度',
  `latitude` double NOT NULL COMMENT '纬度',
  `address` varchar(255) NOT NULL COMMENT '考勤地点',
  `address_range` int(10) unsigned NOT NULL COMMENT '考勤范围',
  `sb_set` int(10) unsigned NOT NULL COMMENT '上下班打卡设置,1上班，2下班，3上下班',
  `late_range` int(10) unsigned NOT NULL COMMENT '晚退多久算加班',
  `enable` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用,1启用,0停用',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`sbid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='班次表';" );
		// 默认班次信息
		$this->_db->query ( "INSERT INTO `oa_sign_batch` (`sbid`, `name`, `work_begin`, `work_end`, `work_days`, `start_begin`, `start_end`, `longitude`, `latitude`, `address`, `address_range`, `sb_set`, `late_range`, `enable`, `status`, `created`, `updated`, `deleted`) VALUES
(1,	'默认班次', '{$work_begin_hi}', '{$work_end_hi}', '{$work_days}','{$start_begin}',	'0',	0,	0,	'',	23,	3,	23,	0,	2,	0,	1440039196,	0);" );
		// 新建班次关联部门表
		$this->_db->query ( "CREATE TABLE `oa_sign_department` (
  `sdid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `sbid` int(10) unsigned NOT NULL COMMENT '班次id',
  `department` int(10) unsigned NOT NULL COMMENT '部门d',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`sdid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='班次部门关联表';
			" );
		// 班次部门默认数据
		$this->_db->query ( "INSERT INTO `oa_sign_department` (`sdid`, `sbid`, `department`, `status`, `created`, `updated`, `deleted`) VALUES
(null, 1, '{$top_id}', 1, 1437127210, 1437127210, 0);" );
		// oa_sign_setting表更新字段
		$this->_db->query ( "INSERT INTO `oa_sign_setting` (`ss_key`, `ss_value`, `ss_type`, `ss_comment`, `ss_status`, `ss_created`, `ss_updated`, `ss_deleted`) VALUES
('permission', '0', 0, '', 1,  1399187776, 1402304362, 0); " );
		// oa_sign_record表更改字段
		$this->_db->query ( "alter table oa_sign_record add sr_batch int(10) NOT NULL COMMENT '所属班次';" );
		$this->_db->query ( "alter table oa_sign_record add sr_sign int(10)  NOT NULL COMMENT '考勤状态';
				" );
		$this->_db->query ( "update oa_sign_record set sr_batch = 1;" );
		$this->_db->query ( "update oa_sign_record set sr_sign = sr_status;" );
		$this->_db->query ( "update oa_sign_record set sr_status = 1;" );
		$this->_db->query ( "alter table oa_sign_record add sr_overtime int(10) NOT NULL COMMENT '加班时长';" );
		return true;
	}
	
	/**
	 * 公共表结构升级
	 */
	protected function _common_table() {
		return true;
	}
	
	/**
	 * 清理缓存
	 */
	protected function _cache_clear() {
		
		// 当前站点的缓存目录
		$cachedir = $this->_params ['cachedir'];
		
		// 读取缓存目录下的文件
		$handle = opendir ( $cachedir );
		// 清理后台菜单缓存文件
		if ($handle) {
			while ( false !== ($file = readdir ( $handle )) ) {
				
				// 判断是否是有效的菜单缓存文件
				if ($file == 'cpmenu.php' || preg_match ( '/^adminergroupcpmenu\.\d+/', $file )) {
					// 删除
					unlink ( $cachedir . DIRECTORY_SEPARATOR . $file );
				}
			}
		}
	}
}
