<?php

/**
 * 2015120101.php
 * 审批应用迭代
 * php -q tool.php -n upgrade -version 2015120101 -epid vchangyi_oa
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

		$identifier = 'askfor';

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
			// 表数据升级
			$this->_plugin_data();
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

		// 新建记录上次审批人抄送人表
		$this->_db->query("CREATE TABLE `oa_askfor_draft` (
  `afd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) NOT NULL COMMENT '用户id',
  `last_afid` text NOT NULL COMMENT '默认审批人id',
  `last_csid` text NOT NULL COMMENT '默认抄送人id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`afd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='记录上次审批人抄送人表'");

		$this->_db->query("CREATE TABLE `oa_askfor_proc_record` (
  `rafp_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `af_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请主题ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审批人UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '审批人名称',
  `rafp_note` varchar(255) NOT NULL DEFAULT '' COMMENT '备注进度',
  `rafp_condition` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '操作状态, 1=审核中，2=审核通过, 3=转审批, 4=审核不通过, 5=抄送，6=已催办，7=已撤销',
  `re_m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '转审批人ID',
  `re_m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '转审批人名称',
  `rafp_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1:初始化2:已更新3:已删除',
  `rafp_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `rafp_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `rafp_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`rafp_id`),
  KEY `af_id` (`af_id`,`rafp_status`),
  KEY `m_uid` (`m_uid`,`rafp_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='审批进度记录表'");

		$this->_db->query("ALTER TABLE `oa_askfor`
ADD `af_condition` tinyint(3) NOT NULL DEFAULT '0' COMMENT '记录状态, 1=审批申请中，2=审批通过, 3=转审批, 4=审批不通过, 5=草稿，6=已催办，7=已撤销' AFTER `afp_id`");

		$this->_db->query("UPDATE `oa_askfor` SET `af_condition` = `af_status`");

		$this->_db->query("UPDATE `oa_askfor` SET `af_status` = 1 WHERE af_status NOT IN (8)");

		$this->_db->query("UPDATE `oa_askfor` SET `af_status` = 3 WHERE af_status = 8");

		$this->_db->query("ALTER TABLE `oa_askfor_proc` ADD `afp_level` tinyint(3) NOT NULL DEFAULT '0' COMMENT '几级审批人' AFTER `afp_note`");

		$this->_db->query("ALTER TABLE `oa_askfor_proc`
ADD `afp_condition` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '记录状态, 1=审核中，2=审核通过, 3=转审批, 4=审核不通过, 5=抄送，6=已催办，7=已撤销' AFTER `is_active`");

		$this->_db->query("UPDATE `oa_askfor_proc` SET afp_condition = afp_status");

		$this->_db->query("UPDATE `oa_askfor_proc` SET `afp_status` = 1 WHERE afp_status NOT IN (8)");

		$this->_db->query("UPDATE `oa_askfor_proc` SET `afp_status` = 3 WHERE afp_status = 8");

		$this->_db->query("ALTER TABLE `oa_askfor_proc`
ADD `re_m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '转审批人ID' AFTER `afp_condition`");

		$this->_db->query("ALTER TABLE `oa_askfor_proc`
ADD `re_m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '转审批人名称' AFTER `re_m_uid`");

		$this->_db->query("ALTER TABLE `oa_askfor_template`
ADD `bu_id` varchar(255) NOT NULL DEFAULT '-1' COMMENT '适用部门ID, -1:全公司' AFTER `creator`");

		$this->_db->query("ALTER TABLE `oa_askfor_template` ADD `custom` text NOT NULL COMMENT '自定义字段'");

		$this->_db->query("ALTER TABLE `oa_askfor_template`
ADD `sbu_id` text COLLATE 'utf8_general_ci' NOT NULL DEFAULT '' COMMENT '使用部门 序列化' AFTER `bu_id`");

		$this->_db->query("ALTER TABLE `oa_askfor_customdata`
ADD `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '字段类型：1文本 2数字 3日期 4时间 5日期时间' AFTER `value`");

		$this->_db->query("ALTER TABLE `oa_askfor_template`
ADD `copy` text COLLATE 'utf8_general_ci' NOT NULL COMMENT '系列化数组, 抄送人列表',
ADD `create_id` int(10) NOT NULL COMMENT '创建者uid'");
		return true;
	}

	/**
	 * 表数据升级
	 */
	protected function _plugin_data() {

		// oa_askfor_template 模板旧数据迭代
		$old_list = array();
		$q = $this->_db->query("SELECT * FROM `oa_askfor_template` WHERE `aft_status` < '99'");
		while ($row = $this->_db->fetch_array($q)) {
			$old_list[] = $row;
		}

		//不为空则升级
		if (!empty($old_list)) {
			//审批人数据升级部分
			$updated_data = array();
			foreach ($old_list as $key => $_data) {
				$tmp_da = unserialize($_data['approvers']);
				$temp = array();
				foreach ($tmp_da as $_key => $_list) {
					$_list['m_face'] = '';
					$_list['selelcted'] = (bool)true;
					$temp[$_key][] = $_list;
					unset($_list);
				}
				$serialize_data = serialize($temp);

				$updated_data[$_data['aft_id']] = $serialize_data;
			}
			foreach ($updated_data as $_aft_id => $_updated_data) {
				$this->_db->query("UPDATE `oa_askfor_template` SET approvers = '{$_updated_data}' WHERE aft_id = $_aft_id");
			}
			//自定义字段升级部分
			$old_customcols = array();
			$q = $this->_db->query("SELECT * FROM `oa_askfor_customcols` WHERE `afcc_status` < '3'");
			while ($row = $this->_db->fetch_array($q)) {
				$old_customcols[] = $row;
			}
			$new_customcols = array();
			$temp = array();
			foreach ($old_customcols as $_value) {
				$temp['name'] = $_value['name'];
				$temp['required'] = $_value['required'];
				$temp['type'] = $_value['type'];
				$new_customcols[$_value['aft_id']][] = $temp;
				unset($temp);
			}
			foreach ($new_customcols as $_aft_id => $_cols) {
				$_cols = serialize($_cols);
				$this->_db->query("UPDATE `oa_askfor_template` SET `custom` = '{$_cols}' WHERE `aft_id` = $_aft_id");
			}
		}

		//proc表旧数据level迭代
		$q = $this->_db->query("SELECT * FROM oa_askfor WHERE aft_id NOT IN (0) AND af_status < 3");
		$fixed_list = array();
		while ($row = $this->_db->fetch_array($q)) {
			$fixed_list[] = $row;
		}
		//不为空则升级
		if (!empty($fixed_list)) {
			$q = $this->_db->query("SELECT * FROM oa_askfor_proc WHERE afp_level = 0 AND afp_status < 3");
			if ($row = $this->_db->fetch_row($q)) {
				$af_list = array();
				if (!empty($fixed_list)) {
					foreach ($fixed_list as $_fix) {
						$af_list[] = $_fix['af_id'];
					}
				}
				$str_afid = '(';
				$str_afid .= implode(',', $af_list);
				$str_afid .= ')';
				$q = $this->_db->query("SELECT * FROM `oa_askfor_proc` WHERE af_id IN $str_afid AND afp_condition IN (1, 2, 3, 4) AND `afp_status` < 3");
				//审批人数据
				while ($row = $this->_db->fetch_array($q)) {
					$old_splist[$row['af_id']][] = $row;
				}
				//更新等级
				foreach ($old_splist as $af_id => $_sp) {
					$num_level = 0;
					foreach ($_sp as $num => $_level) {
						$afp_id = $_level['afp_id'];
						$num_level = $num + 1;
						$this->_db->query("UPDATE oa_askfor_proc SET afp_level = $num_level WHERE afp_id = $afp_id");
					}
				}
			}
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

		$handle = opendir($cachedir);
		if ($handle) {
			while (false !== ($file = readdir($handle))) {
				if (false === stripos($file, 'dbconf.inc.php')) {
					@unlink($cachedir.'/'.$file);
				}
			}
			closedir($handle);
		}

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
