<?php

/**
 * 2015120901.php
 * 审批应用迭代
 * php -q tool.php -n upgrade -version 2015120901 -epid vchangyi_oa
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
			// 表数据升级
			$this->_plugin_data_proc();
			// 更新模板审批人头像
//			$this->_add_old_template_approver_face();
		}

		// 清理缓存
		$this->_cache_clear();
	}

	/**
	 * 表数据升级
	 */
	protected function _plugin_data_proc() {

		//自由流程is_active迭代
		$q = $this->_db->query("SELECT * FROM oa_askfor WHERE aft_id = 0 AND af_status < 3");
		$free_list = array();
		while ($row = $this->_db->fetch_array($q)) {
			$free_list[] = $row;
		}
		if (!empty($free_list)) {
			$af_id = array();
			foreach ($free_list as $val) {
				$af_id[] = $val['af_id'];
			}
			$str_afid = '(';
			$str_afid .= implode(',', $af_id);
			$str_afid .= ')';
			//更改proc表is_avtive为1
			$proc_list = array();
			$this->_db->query("UPDATE oa_askfor_proc SET is_active = 1 WHERE af_id in $str_afid AND afp_condition =1 AND afp_status < 3");
		}
		//固定流程is_active迭代
		$q = $this->_db->query("SELECT * FROM oa_askfor WHERE aft_id NOT IN (0) AND af_status < 3");
		$fixed_list = array();
		while ($row = $this->_db->fetch_array($q)) {
			$fixedid_list[] = $row;
		}
		$af_ids = array();
		if (!empty($fixedid_list)) {
			foreach ($fixedid_list as $_val) {
				$af_ids[] = $_val['af_id'];
			}
			$str_afids = '(';
			$str_afids .= implode(',', $af_ids);
			$str_afids .= ')';
			$q = $this->_db->query("SELECT * FROM oa_askfor_proc Where af_id IN $str_afids AND afp_condition IN (1, 2, 3, 4) AND afp_status < 3");
			while ($row_proc = $this->_db->fetch_array($q)) {
				$fixed_list[] = $row_proc;
			}
			$new_fixed = array();
			foreach ($fixed_list as $_isact) {
				$new_fixed[$_isact['af_id']][] = $_isact;
			}
			foreach ($new_fixed as $_af_id => $_new_f) {
				foreach ($_new_f as $_record) {
					if ($_record['afp_condition'] == 1) {
						$afpid = 0;
						$afpid = $_record['afp_id'];
						$this->_db->query("UPDATE oa_askfor_proc SET is_active = 1 WHERE afp_id = $afpid");
						break;
					}
				}
			}
		}
	}

	/**
	 * 更新模板审批人头像
	 */
	protected function _add_old_template_approver_face() {

		// 读取模板
		$query = $this->_db->query("SELECT * FROM `oa_askfor_template` WHERE `aft_status` < 3");
		while ($row = $this->_db->fetch_array($query)) {
			// 获取审批人数据
			$approvers = $row['approvers'];
			if (empty($row['approvers'])) {
				continue;
			}

			// 反序列化审批人数据
			$un_approvers = unserialize($approvers);
			// 遍历等级
			foreach ($un_approvers as $_level => &$_user_list) {
				// 遍历该等级下的审批人
				foreach ($_user_list as &$_user_data) {
					if (!empty($_user_data['m_face'])) {
						continue;
					}
					// 获取用户数据(头像)
					$query_mem = $this->_db->query("SELECT * FROM `oa_member` WHERE `m_uid` = {$_user_data['m_uid']}");
					$row_mem = $this->_db->fetch_array($query_mem);
					$_user_data['m_face'] = $row_mem['m_face'];
				}
			}

			// 重新序列化审批人数据
			$new_approvers = serialize($un_approvers);
			// 更新审批人数据
			$this->_db->query("UPDATE `oa_askfor_template` SET `approvers` = '{$new_approvers}' WHERE `aft_id` = {$row['aft_id']}");
		}
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

}
