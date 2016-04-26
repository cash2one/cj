<?php
/**
 * 20150317.php
 * 会议记录应用迭代
 * php -q tool.php -n upgrade -version 20150317 -epid vchangyi_oa_upgrade
 * Create By Deepseath
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

		$identifier = 'meeting';

		// 获取应用信息
		$query = $this->_db->query("SELECT * FROM `oa_common_plugin` WHERE `cp_identifier`='{$identifier}' LIMIT 1");
		$this->__plugin = $this->_db->fetch_array($query);

		// 判断应用表是否存在
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_{$identifier}'");
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
		return true;
	}

	/**
	 * 应用表结构升级
	 */
	protected function _plugin_table() {
		$this->_db->query("ALTER TABLE `oa_meeting_mem`
	ADD COLUMN `mm_confirm` tinyint(1)   NULL DEFAULT '0' COMMENT '签到' after `mm_reason`,
	CHANGE `mm_status` `mm_status` tinyint(3) unsigned   NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化, 2=确认参加, 3=不参加, 4=已取消, 5=已删除' after `mm_confirm`,
	CHANGE `mm_created` `mm_created` int(10) unsigned   NOT NULL COMMENT '创建时间' after `mm_status`,
	CHANGE `mm_updated` `mm_updated` int(10) unsigned   NOT NULL COMMENT '更新时间' after `mm_created`,
	CHANGE `mm_deleted` `mm_deleted` int(10) unsigned   NOT NULL COMMENT '删除时间' after `mm_updated`");
		$this->_db->query("ALTER TABLE `oa_meeting_room`
	ADD COLUMN `mr_floor` tinyint(3) unsigned   NULL DEFAULT '1' COMMENT '楼层' after `mr_address`,
	CHANGE `mr_galleryful` `mr_galleryful` varchar(255)  COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '容纳人数' after `mr_floor`,
	CHANGE `mr_device` `mr_device` varchar(255)  COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设备' after `mr_galleryful`,
	CHANGE `mr_volume` `mr_volume` tinyint(3) unsigned   NOT NULL DEFAULT '2' COMMENT '会议室容积, 1:小; 2:中; 3:大' after `mr_device`,
	CHANGE `mr_timestart` `mr_timestart` time   NOT NULL DEFAULT '09:00:00' COMMENT '可预定时间，开始时间' after `mr_volume`,
	CHANGE `mr_timeend` `mr_timeend` time   NOT NULL DEFAULT '18:00:00' COMMENT '可预定时间，结束时间' after `mr_timestart`,
	ADD COLUMN `mr_code` tinyint(3)   NULL DEFAULT '0' COMMENT '微信二维码code,小于100' after `mr_timeend`,
	CHANGE `mr_status` `mr_status` tinyint(3) unsigned   NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新，3=已删除' after `mr_code`,
	CHANGE `mr_created` `mr_created` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '创建时间' after `mr_status`,
	CHANGE `mr_updated` `mr_updated` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '更新时间' after `mr_created`,
	CHANGE `mr_deleted` `mr_deleted` int(10) unsigned   NOT NULL DEFAULT '0' COMMENT '删除时间' after `mr_updated`");

		return true;
	}

	/**
	 * 微信企业号自定义菜单更新
	 */
	protected function _plugin_wxqymenu() {

		$api_url = config::get(startup_env::get('app_name') . '.oa_http_scheme').$this->_settings['domain'].'/api/common/post/updatewxqymenu/';

		$timestamp = startup_env::get('timestamp');
		//$crypt_xxtea = new crypt_xxtea();
		//$hash = rbase64_encode($crypt_xxtea->encrypt(md5($timestamp)));

		$result = array();
		$post = array(
			'pluginid' => $this->__plugin['cp_pluginid'],
			'time' => $timestamp,
			'hash' => ''
		);
		if (!$this->__get_json_by_post($result, $api_url, $post)) {
			logger::error($api_url."||".print_r($result, true));
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

}
