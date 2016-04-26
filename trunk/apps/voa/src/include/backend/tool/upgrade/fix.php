<?php
/**
 * fix.php
 * 用于修复升级检查的脚本，检查升级过程中有未升级或者升级失败的
 * php -q APP_PATH/backend/tool.php -n upgrade -version fix -epid vchangyi_oa_upgrade
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
	/** PHP 所在位置 */
	private $__php = '';

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

		$appname = basename(APP_PATH);
		$this->__php = config::get($appname.'.crontab.php');
	}

	/**
	 * 脚本执行的主方法，不同的升级脚本具体操作动作不相同
	 *
	 * @return void
	 */
	public function run() {
		error_reporting(E_ALL);

		// 检查已升级了的字段是否存在，不存在则表明之前升级失败
		$table_fields = array(
			'20150317' => array('oa_meeting', 'oa_meeting_room', 'mr_floor'),// 版本号 => array(主表, 表名, 待检查的字段名)
			'20150311' => array('oa_askfor', 'oa_askfor_proc', 'is_active'),
		);

		foreach ($table_fields as $_version => $_tf) {
			// 不存在主表，则未安装
			$query = $this->_db->query("SHOW TABLES LIKE '{$_tf[0]}'");
			if (!$this->_db->fetch_row($query)) {
				continue;
			}
			$r = $this->_db->fetch_first("SHOW COLUMNS FROM `{$_tf[1]}` LIKE '{$_tf[2]}'");
			if (!empty($r)) {
				// 存在则忽略
				continue;
			}

			$file = APP_PATH."/data/fix.log";
			$this->_params['upgrade']->rfwrite($file, "{$_version}\t{$_tf[1]}\t{$this->_params['dbname']}\t{$this->_settings['domain']}\n", 'a+');

			// 不存在，则执行升级脚本
			//echo $this->__php." -q tool.php -n upgrade -version {$_version} -epid {$this->_params['dbname']} true";
			$this->__execute($this->__php." tool.php -n upgrade -version {$_version} -epid {$this->_params['dbname']} true");
		}
	}

	/**
	 * 命令行启动方式
	 * @param unknown $cmd
	 */
	private function __execute($cmd) {
		if (stripos(PHP_OS, 'win') === 0){
			pclose(popen("start /B ". $cmd, "r"));
		} else {
			exec($cmd . " > /dev/null &");
		}
	}

}
