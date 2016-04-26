<?php

/**
 * 2016011501.php
 * 通讯录迭代
 * php -q tool.php -n upgrade -version 2016011501 -epid vchangyi_oa
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

		// 判断应用表是否存在
		$identifier = 'addressbook';

		// 获取应用信息
		$query = $this->_db->query("SELECT * FROM `oa_common_plugin` WHERE `cp_identifier`='{$identifier}' LIMIT 1");
		$this->__plugin = $this->_db->fetch_array($query);

		// 后台菜单
		$this->_plugin_cpmenu();

		// 公共表升级
		$this->_common_table();

		// 表数据升级
		$this->_plugin_data();

		// 应用表结构
		$this->_plugin_table();

		//开启则升级
		if ($this->__plugin['cp_available'] == 4) {

			// 应用微信企业号自定义菜单
			$this->_plugin_wxqymenu();
		}

		// 邀请人员 (涉及自定义字段)
		$query = $this->_db->query("SELECT * FROM `oa_common_plugin` WHERE `cp_identifier`='invite' LIMIT 1");
		$yaoqing = $this->_db->fetch_array($query);
		if ($yaoqing['cp_available'] > 0) {
			$this->_yaoqing_data();
		}

		// 清理缓存
		$this->_cache_clear();
	}

	/**
	 * 后台菜单升级
	 */
	protected function _plugin_cpmenu() {

		//去掉职务管理菜单
		$this->_db->query("UPDATE oa_common_cpmenu SET ccm_status = 3 WHERE ccm_module = 'manage' AND ccm_operation = 'member' AND ccm_subop = 'position'");

		return true;
	}

	/**
	 * 应用表结构升级
	 */
	protected function _plugin_table() {

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

		// 部门负责人表
		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_common_department_connect` (
  `dcid` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `cd_id` int(10) NOT NULL DEFAULT '0' COMMENT '部门id',
  `m_uid` int(10) NOT NULL DEFAULT '0' COMMENT '负责人id',
  `m_username` varchar(255) NOT NULL COMMENT '负责人姓名',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`dcid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='部门负责人关联表'");

		//新建部门权限表
		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_common_department_permission` (
  `dpid` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `cd_id` int(10) NOT NULL DEFAULT '0' COMMENT '部门id',
  `per_id` int(10) NOT NULL DEFAULT '0' COMMENT '权限部门id',
  `per_name` varchar(255) NOT NULL COMMENT '权限部门名称',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`dpid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='部门权限关联表'");

		//新建标签表
		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_common_label` (
  `laid` int(10) NOT NULL AUTO_INCREMENT COMMENT '标签自增id',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '标签名',
  `displayorder` int(10) NOT NULL DEFAULT '0' COMMENT '排序号',
  `lastordertime` varchar(255) NOT NULL COMMENT '最后一次更改排序时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`laid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='通讯录标签表'");

		//新建标签人员表
		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_common_label_member` (
  `lamid` int(10) NOT NULL AUTO_INCREMENT COMMENT '标签人员自增id',
  `laid` int(10) NOT NULL DEFAULT '0' COMMENT '标签id',
  `m_uid` int(10) NOT NULL DEFAULT '0' COMMENT '标签里的人员id',
  `m_username` varchar(255) NOT NULL DEFAULT '0' COMMENT '标签里的人姓名',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`lamid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签人员关联表'");

		// 人员设置表新增敏感成员
		$this->_db->query("INSERT INTO `oa_member_setting` (`m_key`, `m_value`, `m_type`, `m_comment`, `m_status`, `m_created`, `m_updated`, `m_deleted`) VALUES
('sensitive',	'',	1,	'敏感成员标签可见字段设置',	1,	0,	0,	0)");

		// 人员属性扩展表新增字段
		$this->_db->query("ALTER TABLE `oa_member_field`
ADD `mf_leader` varchar(255) COLLATE 'utf8_general_ci' NOT NULL DEFAULT '0' COMMENT '直属领导'");

		// 人员搜索表 加上默认值
		$this->_db->query("ALTER TABLE `oa_member_search`
CHANGE `ms_created` `ms_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
CHANGE `ms_updated` `ms_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
CHANGE `ms_deleted` `ms_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间'");

		//department表新增字段
		$this->_db->query("ALTER TABLE oa_common_department ADD cd_permission int(10) NOT NULL DEFAULT 0 COMMENT '部门查看权限，1仅本部门，0全公司，2指定部门' AFTER cd_name");

		$this->_db->query("ALTER TABLE oa_common_department ADD cd_lastordertime int(10) NOT NULL DEFAULT 1 COMMENT '上次排序时间' AFTER cd_name");

		$this->_db->query("ALTER TABLE `oa_member_field`
CHANGE `mf_devicetype` `mf_devicetype` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '最后登陆的设备类型, 1=h5, 2=pc, 3=android, 4=ios' AFTER `mf_deleted`,
CHANGE `mf_birthday` `mf_birthday` varchar(10) NOT NULL DEFAULT '' COMMENT '生日' AFTER `mf_weixinid`");

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
			),
		);

		return true;
	}

	/**
	 * 更新邀请人员自定义字段属性
	 */
	protected function _yaoqing_data() {

		$member_fields = array();

		// 获取
		$query = $this->_db->query("SELECT m_value FROM oa_member_setting WHERE m_key = 'fields'");
		$old_field = $this->_db->fetch_array($query);
		$setting = unserialize($old_field['m_value']);

		if (!empty($setting['custom'])) {
			// 获取自定义字段ao
			foreach ($setting['custom'] as $_key => $_rule) {
				if (substr($_key, 0, 3) == 'ext') {
					$member_fields[substr($_key, 3)] = array(
						'desc' => $_rule['name'],
						'required' => 1,
					);
				}
			}

			$member_fields = serialize($member_fields);
			$this->_db->query("UPDATE `oa_invite_setting` SET `value` = '{$member_fields}' WHERE `key` = 'custom'");
		}

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
