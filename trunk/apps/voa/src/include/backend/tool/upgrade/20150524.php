<?php
/**
 * 20150524.php
 * 活动报名应用迭代
 * php -q tool.php -n upgrade -version 20150524 -epid vchangyi_oa_upgrade
 * Create By Deepseath
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
	protected $_settings = array();
	/**
	 * 来自命令行请求的参数
	 */
	protected $_options = array();
	/**
	 * 来自触发此脚本的父级参数
	 */
	protected $_params = array();
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
	private $__plugin = array();

	public function __construct() {
	}

	/**
	 * 初始化环境参数
	 *
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
	 *
	 * @return void
	 */
	public function run() {
		error_reporting(E_ALL);

		$identifier = 'activity';

		// 公共表结构
		$this->_common_table();

		// 获取应用信息
		$query = $this->_db->query("SELECT * FROM `oa_common_plugin` WHERE `cp_identifier`='{$identifier}' LIMIT 1");
		$this->__plugin = $this->_db->fetch_array($query);

		// 判断应用表是否存在
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_{$identifier}'");
		if ($this->_db->fetch_row($query)) {
			// 后台菜单
			$this->_plugin_cpmenu();
			// 应用表结构
			$this->_plugin_table();
			// 应用微信企业号自定义菜单
			$this->_plugin_wxqymenu();
		}

		// 清理缓存
		$this->_cache_clear();
	}

	/**
	 * 后台菜单升级
	 */
	protected function _plugin_cpmenu() {

		// 新增后台菜单
		$this->_db->query("INSERT INTO `oa_common_cpmenu` (`cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_subnavdisplay`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`) VALUES
({$this->__plugin['cp_pluginid']},	0,	'office',	'activity',	'add',	'subop',	0,	'添加活动',	'fa-plus',	1,	104,	1,	1,	1430376810,	0,	0),
({$this->__plugin['cp_pluginid']},	0,	'office',	'activity',	'edit',	'subop',	0,	'编辑活动',	'fa-edit',	1,	105,	0,	1,	1430376810,	0,	0);");

		return true;
	}

	/**
	 * 应用表结构升级
	 */
	protected function _plugin_table() {

		$this->_db->query("ALTER TABLE `oa_activity`
	CHANGE `title` `title` varchar(30)  COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '活动主题' after `acid`,
	CHANGE `address` `address` varchar(100)  COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '活动地点' after `content`,
	CHANGE `np` `np` int(5) unsigned   NOT NULL DEFAULT '0' COMMENT '活动限制人数' after `address`,
	CHANGE `at_ids` `at_ids` varchar(50)  COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '图片：以逗号分隔。' after `np`,
	CHANGE `m_uid` `m_uid` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '活动发起用户UID' after `at_ids`,
	CHANGE `uname` `uname` varchar(20)  COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '发起人名字' after `m_uid`,
	CHANGE `start_time` `start_time` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '活动开始时间' after `uname`,
	CHANGE `end_time` `end_time` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '活动结束时间' after `start_time`,
	CHANGE `cut_off_time` `cut_off_time` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '活动报名截止时间' after `end_time`,
	ADD COLUMN `outsider` tinyint(3) unsigned   NOT NULL DEFAULT '0' COMMENT '是否允许外部人员参与，0.不允许；1.允许' after `cut_off_time`,
	ADD COLUMN `outfield` text  COLLATE utf8_general_ci NOT NULL COMMENT '序列化外部人员需要填写的列表项' after `outsider`,
	CHANGE `status` `status` tinyint(3) unsigned   NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除' after `outfield`,
	CHANGE `created` `created` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '创建时间' after `status`,
	CHANGE `updated` `updated` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '更新时间' after `created`,
	CHANGE `deleted` `deleted` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '删除时间' after `updated`, COMMENT='活动主题表';");

		$this->_db->query("ALTER TABLE `oa_activity_invite`
	CHANGE `aiid` `aiid` int(10) unsigned   NOT NULL COMMENT '活动邀请表（与活动主表关联）' AUTO_INCREMENT first,
	CHANGE `primary_id` `primary_id` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '参与部门或者人员的主键' after `aiid`,
	CHANGE `type` `type` tinyint(2) unsigned   NOT NULL DEFAULT '0' COMMENT '状态值：1=部门，2=人员' after `primary_id`,
	CHANGE `acid` `acid` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '活动表主键' after `type`,
	CHANGE `created` `created` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '创建时间' after `status`,
	CHANGE `updated` `updated` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '更新时间' after `created`,
	CHANGE `deleted` `deleted` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '删除时间' after `updated`, COMMENT='活动邀请表';");

		$this->_db->query("ALTER TABLE `oa_activity_nopartake`
	CHANGE `anpid` `anpid` int(10) unsigned   NOT NULL COMMENT '活动取消参与内容表' AUTO_INCREMENT first,
	CHANGE `apid` `apid` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '活动参与人员表主键' after `anpid`,
	CHANGE `created` `created` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '创建时间' after `status`,
	CHANGE `updated` `updated` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '更新时间' after `created`,
	CHANGE `deleted` `deleted` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '删除时间' after `updated`, COMMENT='活动取消参与内容表';");

		$this->_db->query("CREATE TABLE `oa_activity_outsider`(
	`oapid` int(10) unsigned NOT NULL  auto_increment COMMENT '活动外部参与人员表主键' ,
	`acid` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '活动ID' ,
	`outname` varchar(10) COLLATE utf8_general_ci NOT NULL  DEFAULT '' COMMENT '外部参与人员名称' ,
	`outphone` varchar(11) COLLATE utf8_general_ci NOT NULL  DEFAULT '' COMMENT '外部参与人员手机' ,
	`captcha` int(6) unsigned NOT NULL  DEFAULT '0' COMMENT '手机验证码' ,
	`check` tinyint(3) unsigned NOT NULL  DEFAULT '0' COMMENT '是否签到, 0=未签到， 1=已签到' ,
	`remark` varchar(64) COLLATE utf8_general_ci NOT NULL  DEFAULT '' COMMENT '备注' ,
	`other` text COLLATE utf8_general_ci NOT NULL  COMMENT '其它信息' ,
	`status` tinyint(3) unsigned NOT NULL  DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除' ,
	`created` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '创建时间' ,
	`updated` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '更新时间' ,
	`deleted` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '删除时间' ,
	PRIMARY KEY (`oapid`) ,
	KEY `name`(`outname`) ,
	KEY `outphone`(`outphone`) ,
	KEY `acid`(`acid`)
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COMMENT='活动外部参与人员表';");

		$this->_db->query("ALTER TABLE `oa_activity_partake`
	CHANGE `apid` `apid` int(10) unsigned   NOT NULL COMMENT '活动参与人员表主键' AUTO_INCREMENT first,
	CHANGE `m_uid` `m_uid` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '用户UID' after `apid`,
	CHANGE `name` `name` varchar(20)  COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '参与人员名称' after `m_uid`,
	CHANGE `acid` `acid` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '活动ID' after `name`,
	CHANGE `type` `type` tinyint(3) unsigned   NOT NULL DEFAULT '0' COMMENT '记录状态, 1=参与，2=申请取消，3=同意取消' after `acid`,
	ADD COLUMN `check` tinyint(3) unsigned   NOT NULL DEFAULT '0' COMMENT '是否签到, 0=未签到， 1=已签到' after `type`,
	ADD COLUMN `remark` varchar(64)  COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '人员报名备注' after `check`,
	CHANGE `status` `status` tinyint(3) unsigned   NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除' after `remark`,
	CHANGE `created` `created` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '创建时间' after `status`,
	CHANGE `updated` `updated` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '更新时间' after `created`,
	CHANGE `deleted` `deleted` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '删除时间' after `updated`, COMMENT='活动参与人员表';");

		return true;
	}

	/**
	 * 微信企业号自定义菜单更新
	 */
	protected function _plugin_wxqymenu() {

		return true;

		$api_url = 'http://' . $this->_settings['domain'] . '/api/common/post/updatewxqymenu/';

		$timestamp = startup_env::get('timestamp');

		$result = array();
		$post = array(
			'pluginid' => $this->__plugin['cp_pluginid'],
			'time' => $timestamp,
			'hash' => ''
		);
		if (!$this->__get_json_by_post($result, $api_url, $post)) {
			logger::error($api_url . "||".$this->__plugin['cp_pluginid']."||" . print_r($result, true));
		}

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

		@unlink($cachedir . DIRECTORY_SEPARATOR . $file . 'plugin.activity.setting.php');
		@unlink($cachedir . DIRECTORY_SEPARATOR . $file . 'plugin.php');
	}

	/**
	 * 读取远程api数据
	 *
	 * @param unknown $data
	 * @param unknown $url
	 * @param string $post
	 * @return boolean
	 */
	private function __get_json_by_post(&$data, $url, $post = '') {
		$snoopy = new snoopy();
		// 强制指定本机
		$snoopy->proxy_host = '127.0.0.1';
		$snoopy->proxy_port = 80;
		$snoopy->_isproxy = 1;
		$result = $snoopy->submit($url, $post);
		// 如果读取错误
		if (!$result || 200 != $snoopy->status) {
			logger::error('$snoopy->submit error: ' . $url . '|' . $result . '|' . $snoopy->status);
			return false;
		}

		/**
		 * 解析 json
		 */
		$data = (array) json_decode($snoopy->results, true);
		if (empty($data) || !empty($data['errcode'])) {
			logger::error('$snoopy->submit error: ' . $url . '|' . $snoopy->results . '|' . $snoopy->status);
			return false;
		}

		if ($data['errcode'] == '45009') {
			// 如果接口请求超限，则稍等10秒重试
			echo '[...wait retry...]';
			sleep(mt_rand(6, 12));
			$data = array();
			return $this->__get_json_by_post($data, $url, $post);
		}

		return true;
	}
}
