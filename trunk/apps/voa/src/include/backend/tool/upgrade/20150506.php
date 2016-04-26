<?php
/**
 * 20150506.php
 * 通讯录迭代
 * php -q tool.php -n upgrade -version 20150506 -epid vchangyi_oa_upgrade
 * Create By luck
 * $Author$
 * $Id$
 */
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

		$this->_plugin_table();
		// 公共表结构
		$this->_common_table();
		// 清理缓存
		$this->_cache_clear();
	}

	/**
	 * 后台菜单升级
	 */
	protected function _plugin_cpmenu() {
		return true;
	}

	/**
	 * 应用表结构升级
	 */
	protected function _plugin_table() {
		// 一期
		$fields = array();
		$q = $this->_db->query("SHOW FULL COLUMNS FROM `oa_member`");
		while ($row = $this->_db->fetch_array($q)) {
			$fields[$row['Field']] = $row['Field'];
		}
		unset($row, $q);

		if (isset($fields['cab_id'])) {
			$this->_db->query("ALTER TABLE `oa_member` DROP `cab_id`;");
		}
		if (isset($fields['m_pluginverify'])) {
			$this->_db->query("ALTER TABLE `oa_member` DROP `m_pluginverify`;");
		}
		if (isset($fields['m_adminid'])) {
			$this->_db->query("ALTER TABLE `oa_member`
CHANGE `m_adminid` `m_admincp` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为管理员，0=否，1=是' AFTER `m_number`,
CHANGE `m_groupid` `m_groupid` mediumint(9) unsigned NOT NULL DEFAULT '0' COMMENT '用户组ID' AFTER `m_admincp`;");
		}

		if (!isset($fields['m_displayorder'])) {
			$this->_db->query('ALTER TABLE `oa_member` ADD COLUMN `m_displayorder` TINYINT(3) NOT NULL DEFAULT 0 COMMENT \'排序\' AFTER `m_salt`;');
		}
		if (isset($fields['m_qywxstatus'])) {
			$this->_db->query('ALTER TABLE oa_member CHANGE COLUMN `m_qywxstatus` `m_qywxstatus` tinyint(1) UNSIGNED NOT NULL DEFAULT \'0\' COMMENT \'企业微信关注状态：1已关注，2已冻结， 4=未关注\';');
		} else {
			$this->_db->query('ALTER TABLE `oa_member` ADD `m_qywxstatus` tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'企业微信关注状态：1已关注，2已冻结， 4=未关注\' AFTER `m_displayorder`;');
		}
		if (!isset($fields['m_weixin'])) {
			$this->_db->query('ALTER TABLE oa_member ADD COLUMN m_weixin VARCHAR(64) NOT NULL DEFAULT \'\' COMMENT \'微信id\' AFTER `m_uid`;');
		}
		if (!isset($fields['m_facetime'])) {
			$this->_db->query("ALTER TABLE `oa_member` ADD `m_facetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '头像更新时间' AFTER `m_face`;");
		}
		$this->_db->query("ALTER TABLE `oa_member`
				CHANGE `m_groupid` `m_groupid` mediumint(9) unsigned NOT NULL DEFAULT '0' COMMENT '用户组ID' AFTER `m_admincp`,
				CHANGE `m_facetime` `m_facetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '头像更新时间' AFTER `m_face`;");

		$mfields = array();
		$q = $this->_db->query("SHOW FULL COLUMNS FROM `oa_member_field`");
		while ($row = $this->_db->fetch_array($q)) {
			$mfields[$row['Field']] = $row['Field'];
		}
		unset($row, $q);

		if (!isset($mfields['mf_address'])) {
			$this->_db->query("ALTER TABLE `oa_member_field` ADD `mf_address` varchar(255) NOT NULL DEFAULT '' COMMENT '住址' AFTER `m_uid`;");
		}
		if (!isset($mfields['mf_idcard'])) {
			$this->_db->query("ALTER TABLE `oa_member_field` ADD `mf_idcard` varchar(20) COLLATE 'utf8_general_ci' NOT NULL DEFAULT '' COMMENT '身份证号码' AFTER `mf_address`;");
		}
		if (!isset($mfields['mf_telephone'])) {
			$this->_db->query("ALTER TABLE `oa_member_field` ADD `mf_telephone` varchar(64) COLLATE 'utf8_general_ci' NOT NULL DEFAULT '' COMMENT '固定电话' AFTER `mf_idcard`;");
		}
		if (!isset($mfields['mf_qq'])) {
			$this->_db->query("ALTER TABLE `oa_member_field` ADD `mf_qq` varchar(12) COLLATE 'utf8_general_ci' NOT NULL DEFAULT '' COMMENT 'QQ号码' AFTER `mf_telephone`;");
		}
		if (isset($mfields['mf_weixinid'])) {
			$this->_db->query('UPDATE oa_member,oa_member_field SET m_weixin = mf_weixinid WHERE oa_member.m_uid = oa_member_field.m_uid;');
		} else {
			$this->_db->query("ALTER TABLE `oa_member_field` ADD `mf_weixinid` varchar(64) COLLATE 'utf8_general_ci' NOT NULL DEFAULT '' COMMENT '微信号' AFTER `mf_qq`;");
		}
		if (!isset($mfields['mf_birthday'])) {
			$this->_db->query("ALTER TABLE `oa_member_field` ADD `mf_birthday` date NOT NULL DEFAULT '0000-00-00' COMMENT '生日' AFTER `mf_weixinid`;");
		}
		if (!isset($mfields['mf_remark'])) {
			$this->_db->query("ALTER TABLE `oa_member_field` ADD `mf_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '其他备注' AFTER `mf_birthday`;");
		}
		if (!isset($mfields['mf_devicetype'])) {
			$this->_db->query("ALTER TABLE `oa_member_field` ADD `mf_devicetype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '最后登陆的设备类型, 1=h5, 2=pc, 3=android, 4=ios' AFTER `mf_deleted`;");
		} else {
			$this->_db->query("ALTER TABLE `oa_member_field` CHANGE `mf_devicetype` `mf_devicetype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '最后登陆的设备类型, 1=h5, 2=pc, 3=android, 4=ios' AFTER `mf_deleted`;");
		}
		if (!isset($mfields['mf_notificationtotal'])) {
			$this->_db->query("ALTER TABLE `oa_member_field` ADD `mf_notificationtotal` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息数目统计' AFTER `mf_devicetype`;");
		} else {
			$this->_db->query("ALTER TABLE `oa_member_field` CHANGE `mf_notificationtotal` `mf_notificationtotal` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息数目统计' AFTER `mf_devicetype`;");
		}

		if (!isset($mfields['mf_ext1'])) {
			$this->_db->query('ALTER TABLE oa_member_field ADD COLUMN mf_ext1 VARCHAR(500) NOT NULL DEFAULT \'\' COMMENT \'扩展字段1\';');
			$this->_db->query('ALTER TABLE oa_member_field ADD COLUMN mf_ext2 VARCHAR(500) NOT NULL DEFAULT \'\' COMMENT \'扩展字段2\';');
			$this->_db->query('ALTER TABLE oa_member_field ADD COLUMN mf_ext3 VARCHAR(500) NOT NULL DEFAULT \'\' COMMENT \'扩展字段3\';');
			$this->_db->query('ALTER TABLE oa_member_field ADD COLUMN mf_ext4 VARCHAR(500) NOT NULL DEFAULT \'\' COMMENT \'扩展字段4\';');
			$this->_db->query('ALTER TABLE oa_member_field ADD COLUMN mf_ext5 VARCHAR(500) NOT NULL DEFAULT \'\' COMMENT \'扩展字段5\';');
			$this->_db->query('ALTER TABLE oa_member_field ADD COLUMN mf_ext6 VARCHAR(500) NOT NULL DEFAULT \'\' COMMENT \'扩展字段6\';');
			$this->_db->query('ALTER TABLE oa_member_field ADD COLUMN mf_ext7 VARCHAR(500) NOT NULL DEFAULT \'\' COMMENT \'扩展字段7\';');
			$this->_db->query('ALTER TABLE oa_member_field ADD COLUMN mf_ext8 VARCHAR(500) NOT NULL DEFAULT \'\' COMMENT \'扩展字段8\';');
			$this->_db->query('ALTER TABLE oa_member_field ADD COLUMN mf_ext9 VARCHAR(500) NOT NULL DEFAULT \'\' COMMENT \'扩展字段9\';');
			$this->_db->query('ALTER TABLE oa_member_field ADD COLUMN mf_ext10 VARCHAR(500) NOT NULL DEFAULT \'\' COMMENT \'扩展字段10\';');
		}
		$this->_db->query('REPLACE INTO `oa_common_cpmenu` (`cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_subnavdisplay`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`) VALUES
                                    (0,	1,	\'manage\',	\'member\',	\'impmem\',	\'subop\',	0,	\'批量导入\',	\'fa-plus\',	1,	3,	0,	1,	0,	0,	0);');
		$this->_db->query('UPDATE oa_common_cpmenu SET ccm_status = 4 ,ccm_deleted = UNIX_TIMESTAMP() WHERE ccm_module = \'manage\' AND ccm_operation = \'member\' AND ccm_subop IN (\'search\', \'impqywx\', \'edit\', \'delete\', \'dump\');');
		$this->_db->query('REPLACE INTO `oa_member_setting` (`m_key`, `m_value`, `m_type`, `m_comment`, `m_status`, `m_created`, `m_updated`, `m_deleted`) VALUES
        (\'fields\', \'a:5:{s:2:"qq";a:3:{s:8:"priority";i:1;s:4:"desc";s:2:"QQ";s:6:"status";i:2;}s:7:"address";a:3:{s:8:"priority";i:2;s:4:"desc";s:6:"地址";s:6:"status";i:2;}s:6:"idcard";a:3:{s:8:"priority";i:3;s:4:"desc";s:9:"身份证";s:6:"status";i:2;}s:9:"telephone";a:3:{s:8:"priority";i:4;s:4:"desc";s:6:"电话";s:6:"status";i:2;}s:8:"birthday";a:3:{s:8:"priority";i:5;s:4:"desc";s:6:"生日";s:6:"status";i:2;}}\', 1, \'扩展字段设置\', 1, 0, 0, 0);');

		// 二期
		$this->_db->query("INSERT INTO `oa_common_cpmenu` (`cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_subnavdisplay`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`) VALUES
        (0, 1,  'manage',   'member',   'position', 'subop',    0,  '职务管理', 'fa-list',  1,  3,  1,  1,  0,  0,  0);");
		$this->_db->query("ALTER TABLE `oa_member_department` ADD COLUMN `mp_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '职务id' AFTER `m_uid`;");
		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_member_position` (
                            `mp_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                              `mp_name` varchar(500) NOT NULL COMMENT '职务',
                              `mp_parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '职务父级id',
                              `mp_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
                              `mp_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                              `mp_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
                              `mp_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
                              PRIMARY KEY (`mp_id`)
                            ) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COMMENT='用户职务表';");
		$this->_db->query("REPLACE INTO `oa_member_position` VALUES ('1', '总经理', '0', '1', '0', '0', '0'),
                             ('2', '经理', '1', '1', '0', '0', '0'),
                            ('3', '主管', '2', '1', '0', '0', '0'), ('4', '员工', '3', '1', '0', '0', '0');");

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
		$cachedir = $this->_params['cachedir'];

		// 读取缓存目录下的文件
		$handle = opendir($cachedir);
		// 清理后台菜单缓存文件
		if ($handle) {
			while (false !== ($file = readdir($handle))) {

				// 判断是否是有效的菜单缓存文件
				if ($file == 'cpmenu.php' || preg_match('/^adminergroupcpmenu\.\d+/', $file)) {
					// 删除
					unlink($cachedir . DIRECTORY_SEPARATOR . $file);
				}
			}
		}
	}
}
