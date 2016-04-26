<?php
/**
 * 20150525.php
 * 新闻公告应用迭代
 * php -q tool.php -n upgrade -version 20150525 -epid vchangyi_oa_upgrade
 * Create By
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

	/** 升级类型 */
	protected $_db_change_type = -1;

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

		$identifier = 'news';

		// 灰度结束时的迭代
		if (in_array($this->_params['dbname'], array('ep_10002', 'ep_10150'))) {
			// 参与灰度，但数据结构与正式版无变化的
			$this->_db_change_type = 1;
		} elseif (in_array($this->_params['dbname'], array('ep_20619', 'ep_13111', 'ep_11657'))) {
			// 参与灰度，但数据结构与正式版有变化的
			$this->_db_change_type = 2;
		} else {
			// 未参与灰度，直接升级到最新版
			$this->_db_change_type = -1;
		}
		///////////

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

		if ($this->_db_change_type >= 0) {
			// 参与了灰度，则不需要升级
			return true;
		}

		// 新增后台菜单
		$this->_db->query("UPDATE `oa_common_cpmenu` SET `ccm_status`= '".voa_d_oa_common_plugin::STATUS_REMOVE."' WHERE `ccm_module`='office' AND `ccm_operation`='news' AND `ccm_subop` in ('add', 'issue', 'templatelist', 'category', 'madd', 'addcategory')");
		$this->_db->query("INSERT INTO `oa_common_cpmenu` (`cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_subnavdisplay`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`) VALUES
		({$this->__plugin['cp_pluginid']}, 0,	'office',	'news', 'issue', 		'subop',	0,	'权限设置',		'fa-gear',	1,	107,	1,	1,	0,	0,	0),
		({$this->__plugin['cp_pluginid']}, 0,	'office',	'news', 'templatelist', 'subop',	0,	'添加公告',		'fa-plus',	1,	107,	1,	1,	0,	0,	0),
		({$this->__plugin['cp_pluginid']}, 0,	'office',	'news', 'category', 	'subop',	0,	'菜单设置',		'fa-gear',	1,	107,	1,	1,	0,	0,	0),
		({$this->__plugin['cp_pluginid']}, 0,	'office',	'news', 'madd',			'subop',	0,	'添加多条公告',	'fa-plus',	1,	107,	0,	1,	0,	0,	0),
		({$this->__plugin['cp_pluginid']}, 0,	'office',	'news', 'addcategory',  'subop',	0,	'菜单修改',		'fa-edit',	1,  107,	0,	1,	0,	0,	0),
		({$this->__plugin['cp_pluginid']}, 0,	'office',	'news', 'add',			'subop',	0,	'添加单条公告',	'fa-plus',	1,	107,	0,	1,	0,	0,	0);");
		return true;
	}

	/**
	 * 应用表结构升级
	 */
	protected function _plugin_table() {

		if ($this->_db_change_type == 1) {
			// 参与了灰度但数据结构与正式版未发生变化，则忽略
			return true;
		} elseif ($this->_db_change_type == 2) {
			// 参与了灰度，且数据结构与正式版有变化，则对应升级

			$this->_db->query("ALTER TABLE `oa_news`
	ADD KEY `is_publish`(`is_publish`),
	ADD KEY `m_uid`(`m_uid`);");

			$this->_db->query("ALTER TABLE `oa_news_category`
	CHANGE `status` `status` tinyint(3) unsigned   NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除' after `orderid`,
	CHANGE `created` `created` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '创建时间' after `status`,
	CHANGE `updated` `updated` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '更新时间' after `created`,
	CHANGE `deleted` `deleted` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '删除时间' after `updated`,
	DROP COLUMN `url`;");

			return true;
		}

		// 正式升级

		$this->_db->query("ALTER TABLE `oa_news`
	ADD COLUMN `m_uid` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '用户id' after `nca_id`,
	CHANGE `title` `title` varchar(64)  COLLATE utf8_general_ci NOT NULL DEFAULT '' after `m_uid`,
	ADD COLUMN `summary` varchar(120)  COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '摘要' after `title`,
	CHANGE `cover_id` `cover_id` int(11)   NOT NULL DEFAULT '0' after `summary`,
	CHANGE `read_num` `read_num` int(11) unsigned   NOT NULL DEFAULT '0' COMMENT '已阅读人数' after `cover_id`,
	CHANGE `is_secret` `is_secret` tinyint(1)   NOT NULL DEFAULT '0' COMMENT '是否保密：0=不保密；1=保密' after `read_num`,
	CHANGE `is_comment` `is_comment` tinyint(1)   NOT NULL DEFAULT '0' COMMENT '是否评论：0=不评论；1=评论' after `is_secret`,
	CHANGE `is_publish` `is_publish` tinyint(1)   NOT NULL DEFAULT '0' COMMENT '是否发布：0=草稿；1=发布' after `is_comment`,
	CHANGE `is_all` `is_all` tinyint(1) unsigned   NOT NULL DEFAULT '0' COMMENT '是否全部人员可见：0=不是；1=是' after `is_publish`,
	ADD COLUMN `is_check` tinyint(1) unsigned   NOT NULL DEFAULT '0' COMMENT '是否审批:0-无审批  1-有审批' after `is_all`,
	ADD COLUMN `check_summary` varchar(120)  COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '预览说明' after `is_check`,
	CHANGE `status` `status` tinyint(3) unsigned   NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除' after `check_summary`,
	CHANGE `created` `created` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '创建时间' after `status`,
	CHANGE `updated` `updated` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '更新时间' after `created`,
	CHANGE `deleted` `deleted` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '删除时间' after `updated`,
	CHANGE `published` `published` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '发布时间' after `deleted`,
	ADD KEY `is_publish`(`is_publish`),
	ADD KEY `m_uid`(`m_uid`);");

		$this->_db->query("ALTER TABLE `oa_news_category`
	ADD COLUMN `nca_id` int(11) unsigned   NOT NULL first,
	CHANGE `name` `name` varchar(30)  COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '类型名' after `nca_id`,
	ADD COLUMN `parent_id` int(11) unsigned   NOT NULL DEFAULT '0' COMMENT '父类型ID' after `name`,
	ADD COLUMN `orderid` int(10) unsigned   NOT NULL DEFAULT '1' COMMENT '排序' after `parent_id`,
	CHANGE `status` `status` tinyint(3) unsigned   NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除' after `orderid`,
	CHANGE `created` `created` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '创建时间' after `status`,
	CHANGE `updated` `updated` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '更新时间' after `created`,
	CHANGE `deleted` `deleted` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '删除时间' after `updated`,
	DROP COLUMN `nc_id`,
	DROP KEY `PRIMARY`, add PRIMARY KEY(`nca_id`);");

		$this->_db->query("REPLACE INTO `oa_news_category` (`nca_id`, `name`, `parent_id`, `orderid`, `status`, `created`, `updated`, `deleted`) VALUES
(1,	'公司动态',	0,	1,	1,	1427790759,	0,	0),
(2,	'通知公告',	0,	2,	1,	1427790759,	0,	0),
(3,	'员工动态',	0,	3,	1,	1427790759,	0,	0);");

$this->_db->query("CREATE TABLE `oa_news_check`(
	`nec_id` int(10) unsigned NOT NULL  auto_increment ,
	`news_id` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '新闻id' ,
	`m_uid` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '审核人id' ,
	`is_check` tinyint(3) unsigned NOT NULL  DEFAULT '1' COMMENT '审核状态:1-审核中 2-审核通过 3-未通过' ,
	`check_note` varchar(140) COLLATE utf8_unicode_ci NOT NULL  DEFAULT '' COMMENT '理由' ,
	`status` tinyint(3) unsigned NOT NULL  DEFAULT '1' COMMENT '状态:1=初始化，2=已更新，3=已删除' ,
	`created` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '创建时间' ,
	`updated` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '修改时间' ,
	`deleted` int(10) unsigned NOT NULL  DEFAULT '0' COMMENT '删除时间' ,
	PRIMARY KEY (`nec_id`) ,
	KEY `news_id`(`news_id`)
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COMMENT='新闻公告审核表';");

		return true;
	}

	/**
	 * 微信企业号自定义菜单更新
	 */
	protected function _plugin_wxqymenu() {

		//return true;

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

		// 更新应用描述文字
		$descriptions = array(
			'project' => '随时随地发起任务，实时查看、推进任务进度，高效的移动任务管理平台',
			'vnote' => '支持文字快速创建备忘，备忘内容一键分享同事',
			'askfor' => '随时随地发起审批，实时微信提醒，自由流程和固定流程结合，享受移动办公乐趣',
			'thread' => '企业内部移动化交流社区，员工在微信端快速发表话题并参与互动',
			'reimburse' => '快速记录报销明细，自动生成报销记录，即时消息提醒，一站式闪电报销',
			'dailyreport' => '自定义报告类型，实时上传图文报告，自由评论，快捷归档及管理',
			'sign' => '自定义签到时间，支持IP与经纬度双重定，外勤人员即时上报地理位置',
			'askoff' => '随时随地发起请假，实时微信提醒，快速审批请假申请',
			'addressbook' => '移动版的企业通讯录，信息永不遗失，动态模式管理成员',
			'inspect' => '针对各类终端门店和专柜量身打造的巡视类应用，巡店人员可以快速对门店情况进行核查打分并生成巡查结果',
			'workorder' => '基于门店管理系统的工单应用，随时随地的发起工单、派发任务，执行人员快速跟进反馈',
			'travel' => '服务号与企业号打通，让售卖更方便。企业号让销售更方便的管理产品，服务号让客户更加方便的购买',
			'train' => '企业内部移动化培训平台，培训资料快速编辑发布，微信端实时提醒，支持可见范围设置',
			'showroom' => '企业快速下发陈列规范，门店人员在微信端接收查看并执行，帮组企业更方便的实施陈列标准化管理',
			'news' => '企业移动化信息发布平台，支持消息保密设置，支持菜单类型自定义和模板选择，发布公告更省心',
			'nvote' => '企业快速调研通道，通过投票了解员工意向，创建公平民主的企业氛围',
			'activity' => '企业组织活动型应用，员工在微信上可以快速发起活动、参与活动，允许分享到外部，支持微信扫一扫签到'
		);
		foreach ($descriptions as $_identifier => $_text) {
			$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='{$_text}' WHERE `cp_identifier`='{$_identifier}'");
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
