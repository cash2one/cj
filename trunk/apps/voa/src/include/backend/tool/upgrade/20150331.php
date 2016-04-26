<?php
/**
 * 20150331.php
 * 同事社区上线
 * php -q tool.php -n upgrade -version 20150331 -epid oa_thread
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
	/** 应用唯一标志符 */
	protected $__identifier = '';

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

		/*
		$this->_sql_logfile = $this->_params['cachedir'].DIRECTORY_SEPARATOR.$this->_options['version'].'_sql.txt';
		$this->_sql_restore_logfile = $this->_params['cachedir'].DIRECTORY_SEPARATOR.$this->_options['version'].'_sql_restore.txt';

		// 避免重复运行覆盖，随机一个附加的文件名
		$mt_rand = time().'.'.mt_rand(100, 999);
		// 如果已经执行语句文件存在则重命名
		if (is_file($this->_sql_logfile)) {
			rename($this->_sql_logfile, $this->_sql_logfile.'.'.$mt_rand);
		}
		// 重命名存在了的sql_restore.sql
		if (is_file($this->_sql_restore_logfile)) {
			rename($this->_sql_restore_logfile, $this->_sql_restore_logfile.'.'.$mt_rand);
		}
		*/
	}

	/**
	 * 脚本执行的主方法，不同的升级脚本具体操作动作不相同
	 * @return void
	 */
	public function run() {

		error_reporting(E_ALL);

		$this->__identifier = 'thread';

		$steps = array(
			'open_plugin',// 启用应用
			'cache_clear', // 清理cpmenu菜单缓存
		);

		foreach ($steps as $_step) {
			$classname = '_'.$_step;
			$this->$classname();
		}

	}



	/**
	 * 启用应用
	 */
	protected function _open_plugin() {

		// 套件组ID
		$cpg_id = 5;
		// 应用ID
		$cp_pluginid = 8;
		// 应用名
		$plugin_name = '同事社区';
		// 描述
		$plugin_description = '企业内部论坛，微信端发布话题，同事间点赞、评论或吐槽';

		/**
		 * 思路，先尝试删除，再进行添加，避免某些站点不存在此应用记录的情况
		 */
		$timestamp = time();
		// 先尝试删除
		$this->_db->query("DELETE FROM `oa_common_plugin` WHERE `cp_identifier`='{$this->__identifier}'");
		// 新增
		$this->_db->query("INSERT INTO `oa_common_plugin` (`cp_pluginid`,`cp_identifier`,
				`cmg_id`, `cpg_id`, `cp_suiteid`, `cp_agentid`, `cp_displayorder`,
				`cp_available`, `cp_adminid`, `cp_name`, `cp_icon`, `cp_description`,
				`cp_datatables`, `cp_directory`, `cp_url`, `cp_version`,
				`cp_lastavailable`, `cp_lastopen`, `cyea_id`, `cp_status`,
				`cp_created`, `cp_updated`, `cp_deleted`) VALUES (
				$cp_pluginid,
				'{$this->__identifier}', 1, {$cpg_id}, '', '', 100{$cp_pluginid}, 0, 0, '{$plugin_name}', '{$this->__identifier}.png',
				'{$plugin_description}', '{$this->__identifier}*', '{$this->__identifier}', '{$this->__identifier}.php', '0.1', 0, 0, 0,
				1, {$timestamp}, 0, 0)");

		return true;
	}

	/**
	 * 清理缓存
	 */
	protected function _cache_clear() {

		// 当前站点的缓存目录
		$cachedir = $this->_params['cachedir'];

		// 清理应用信息缓存
		@unlink($cachedir.DIRECTORY_SEPARATOR.'plugin.php');
		// 试图清理培训应用的设置缓存
		@unlink($cachedir.DIRECTORY_SEPARATOR.'plugin.'.$this->__identifier.'.setting.php');

		// 读取缓存目录下的文件
		$handle = opendir($cachedir);
		// 清理后台菜单缓存文件
		if ($handle) {
			while (false !== ($file = readdir($handle))) {

				// 判断是否是有效的菜单缓存文件
				if ($file == 'cpmenu.php' || preg_match('/^adminergroupcpmenu\.\d+/', $file)) {
					// 删除
					unlink($cachedir.DIRECTORY_SEPARATOR.$file);
					break;
				}
			}
		}

	}

	/**
	 * 带储存执行语句记录的数据query方法
	 * 执行非查询类的操作使用，查询类操作使用$this->_db->query()
	 * @param string $sql 需要实际执行的语句
	 * @param string $sql_restore 恢复执行状态的语句
	 */
	protected function _rquery($sql = '', $sql_restore = '') {

		if (is_array($sql)) {
			$sql = implode(' ', $sql);
		}
		if (is_array($sql_restore)) {
			$sql_restore = implode(' ', $sql_restore);
		}

		$sql = trim($sql);

		// 执行
		$ret = $this->_db->query($sql);

		// 非查询类的操作则忽略写日志
		$write_cmd = array('UPDATE', 'INSERT', 'DELETE', 'REPLACE');
		if (!preg_match('/^['.implode('|', $write_cmd).']/i', $sql)) {
			return $ret;
		}

		// 整理sql语句，加入;和换行
		if (substr($sql, -1) != ';') {
			$sql .= ';';
		}
		$sql .= "\r\n";
		// 写入已执行SQL
		file_put_contents($this->_sql_logfile, $sql, FILE_APPEND);

		// 整理sql语句，加入;和换行
		$sql_restore = trim($sql_restore);
		if ($sql_restore) {
			if (substr($sql_restore, -1) != ';') {
				$sql_restore .= ';';
			}
			$sql_restore .= "\r\n";
			// 写入当前执行SQL的恢复语句
			file_put_contents($this->_sql_restore_logfile, $sql_restore, FILE_APPEND);
		}

		return $ret;
	}

}
