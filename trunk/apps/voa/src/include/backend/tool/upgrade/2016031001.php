<?php

/**
 * 2016031001.php
 * 通讯录迭代
 * php -q tool.php -n upgrade -version 2016031001 -epid vchangyi_oa
 * Create By lixue
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
	 * @param array  $settings 当前站点的setting
	 * @param array  $options 传输进来的外部参数
	 * @param array  $params 一些环境参数，来自触发执行本脚本
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

		// 后台菜单
		$this->_plugin_cpmenu();

		// 公共表升级
		$this->_common_table();

		// 表数据升级
		$this->_plugin_data();

		// 应用表结构
		$this->_plugin_table();

		$this->_open_train();

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

		return true;
	}

	/**
	 * 启用应用
	 */
	protected function _open_train() {

		/**
		 * 思路，先尝试删除，再进行添加，避免某些站点不存在此应用记录的情况
		 */
		$timestamp = time();
		// 先尝试删除
		$this->_db->query("DELETE FROM `oa_common_plugin` WHERE `cp_identifier`='community'");
		$this->_db->query("DELETE FROM `oa_common_plugin` WHERE `cp_identifier`='banner'");
		// 新增
		$this->_db->query("INSERT INTO `oa_common_plugin` (`cp_pluginid`, `cp_identifier`,
				`cmg_id`, `cpg_id`, `cp_suiteid`, `cp_agentid`, `cp_displayorder`,
				`cp_available`, `cp_adminid`, `cp_name`, `cp_icon`, `cp_description`,
				`cp_datatables`, `cp_directory`, `cp_url`, `cp_version`,
				`cp_lastavailable`, `cp_lastopen`, `cyea_id`, `cp_status`,
				`cp_created`, `cp_updated`, `cp_deleted`) VALUES (39, 'banner', 1, 8, '', '', 1043, 0, 0, '精选', 'banner.png', '微圈精选', 'banner*', 'banner', 'banner.php', '0.1', 0, 0, 0, 1, {$timestamp}, 0, 0),
(40, 'cnvote', 1, 8, '', '', 1039, 0, 0, '投票', 'cnvote.png', '微圈的投票', 'cnvote*', 'cnvote', 'cnvote.php', '0.1', 0, 0, 0, 1, {$timestamp}, 0, 0),
(41, 'event', 1, 8, '', '', 1040, 0, 0, '活动', 'event.png', '微圈活动', 'event*', 'event', 'event.php', '0.1', 0, 0, 0, 1, {$timestamp}, 0, 0),
(42, 'cinvite', 1, 8, '', '', 1041, 0, 0, '邀请', 'cinvite.png', '微圈邀请', 'cinvite*', 'cinvite', 'cinvite.php', '0.1', 0, 0, 0, 1, {$timestamp}, 0, 0),
(43, 'community', 1, 8, '', '', 1042, 0, 0, '话题', 'community.png', '微圈话题', 'community*', 'community', 'community.php', '0.1', 0, 0, 0, 1, {$timestamp}, 0, 0),
(44, 'my', 1, 8, '', '', 1038, 0, 0, '我的', 'my.png', '我的微圈', 'my*', 'my', 'my.php', '0.1', 0, 0, 0, 1, {$timestamp}, 0, 0)");

		return true;
	}

	/**
	 * 表数据升级
	 */
	protected function _plugin_data() {

		/** 更新人员属性规则 */
		// 获取设置表数据
		$query = $this->_db->query("SELECT m_value FROM oa_member_setting WHERE m_key = 'fields'");
		$old_field = $this->_db->fetch_array($query);
		$old_field = unserialize($old_field['m_value']);
		// 新的人员属性规则
		$this->_field($new_field);
		if (empty($old_field)) {
			$new_field = serialize($new_field);
		} else {
			foreach ($old_field as $_key => $_rule) {
				if (is_int($_key) && $_key <= 10) {
					$_rule['priority'] = $_rule['priority'] == 0 ? 1 : $_rule['priority'];
					$new_field['custom']['ext' . $_key] = array(
						'number' => $_rule['priority'],
						'name' => $_rule['desc'],
						'open' => $_rule['status'],
						'required' => 0,
						'view' => 0,
						'level' => 3,
					);
				}
			}
			$new_field = serialize($new_field);
		}
		// 更新规则
		$this->_db->query("UPDATE `oa_member_setting` SET `m_value` = '{$new_field}' WHERE `m_key` = 'fields'");

	}

	/**
	 * 公共表结构升级
	 */
	protected function _common_table() {

		// 人员属性扩展表新增字段
		$this->_db->query("ALTER TABLE `oa_member_field`
ADD COLUMN `mf_nickname` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '' AFTER `mf_ext10`,
ADD COLUMN `mf_years` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '' AFTER `mf_nickname`,
ADD COLUMN `mf_area` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '' AFTER `mf_years`,
ADD COLUMN `mf_mark` TEXT NOT NULL DEFAULT '' COMMENT '' AFTER `mf_area`");

		return true;
	}

	/**
	 * 清理缓存
	 */
	protected function _cache_clear() {

		// 当前站点的缓存目录
		$cachedir = $this->_params['cachedir'];

		$handle = opendir($cachedir);
		if ($handle) {
			while (false !== ($file = readdir($handle))) {
				if (false === stripos($file, 'dbconf.inc.php')) {
					@unlink($cachedir . '/' . $file);
				}
			}
			closedir($handle);
		}

	}

	/**
	 * 默认人员属性规则
	 * @param $field
	 * @return bool
	 */
	protected function _field(&$field) {

		$field = array(
			'fixed' => array(
				'name' => array(
					'number' => 1,
					'name' => '姓名',
					'open' => 1,
					'required' => 1,
					'view' => 1,
					'level' => 0,
				),
				'userid' => array(
					'number' => 2,
					'name' => '账号',
					'open' => 1,
					'required' => 0,
					'view' => 1,
					'level' => 1,
				),
				'gender' => array(
					'number' => 3,
					'name' => '性别',
					'open' => 1,
					'required' => 1,
					'view' => 1,
					'level' => 1,
				),
				'mobile' => array(
					'number' => 4,
					'name' => '手机号',
					'open' => 1,
					'required' => 0,
					'view' => 1,
					'level' => 1,
				),
				'weixinid' => array(
					'number' => 5,
					'name' => '微信',
					'open' => 1,
					'required' => 0,
					'view' => 1,
					'level' => 1,
				),
				'email' => array(
					'number' => 6,
					'name' => '邮箱',
					'open' => 1,
					'required' => 0,
					'view' => 1,
					'level' => 1,
				),
				'department' => array(
					'number' => 7,
					'name' => '部门',
					'open' => 1,
					'required' => 1,
					'view' => 1,
					'level' => 1,
				),
			),
			'custom' => array(
				'leader' => array(
					'number' => 1,
					'name' => '直属领导',
					'open' => 1,
					'required' => 0,
					'view' => 0,
					'level' => 2,
				),
				'birthday' => array(
					'number' => 2,
					'name' => '生日',
					'open' => 1,
					'required' => 0,
					'view' => 0,
					'level' => 2,
				),
				'address' => array(
					'number' => 3,
					'name' => '地址',
					'open' => 1,
					'required' => 0,
					'view' => 0,
					'level' => 2,
				),
				'position' => array(
					'number' => 4,
					'name' => '职位',
					'open' => 1,
					'required' => 0,
					'view' => 0,
					'level' => 2,
				),
				'nickname' => array(
					'number' => 5,
					'name' => '昵称',
					'open' => 1,
					'required' => 0,
					'view' => 0,
					'level' => 2,
				),
				'years' => array(
					'number' => 6,
					'name' => '出生年代',
					'open' => 1,
					'required' => 0,
					'view' => 0,
					'level' => 2,
				),
				'area' => array(
					'number' => 7,
					'name' => '地区',
					'open' => 1,
					'required' => 0,
					'view' => 0,
					'level' => 0,
				),
				'mark' => array(
					'number' => 8,
					'name' => '兴趣标签',
					'open' => 1,
					'required' => 0,
					'view' => 0,
					'level' => 0,
				)
			),
		);

		return true;
	}


	/**
	 * 微信企业号自定义菜单更新
	 */
	protected function _plugin_wxqymenu() {

		$api_url = config::get('voa.oa_http_scheme') . $this->_settings['domain'] . '/api/common/post/updatewxqymenu/';

		$timestamp = startup_env::get('timestamp');
		//$crypt_xxtea = new crypt_xxtea();
		//$hash = rbase64_encode($crypt_xxtea->encrypt(md5($timestamp)));

		$result = array();
		$post = array(
			'pluginid' => $this->__plugin['cp_pluginid'],
			'time' => $timestamp,
			'hash' => '',
		);
		if (!$this->__get_json_by_post($result, $api_url, $post)) {
			logger::error($api_url . "||" . print_r($result, true));
		}

		return true;
	}

	/**
	 * 读取远程api数据
	 * @param unknown $data
	 * @param unknown $url
	 * @param string  $post
	 * @return boolean
	 */
	private function __get_json_by_post(&$data, $url, $post = '') {
		$snoopy = new snoopy();
		$result = $snoopy->submit($url, $post);
		/** 如果读取错误 */
		if (!$result || 200 != $snoopy->status) {
			logger::error('$snoopy->submit error: ' . $url . '|' . $result . '|' . $snoopy->status);

			return false;
		}

		/** 解析 json */
		$data = (array)json_decode($snoopy->results, true);
		if (empty($data) || !empty($data['errcode'])) {
			logger::error('$snoopy->submit error: ' . $url . '|' . $snoopy->results . '|' . $snoopy->status);

			return false;
		}

		return true;
	}

}
