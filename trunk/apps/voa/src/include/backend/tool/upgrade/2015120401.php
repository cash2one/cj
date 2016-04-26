<?php
/**
 * 矫正考勤签到出错的部门ID数据
 * php -q tool.php -n upgrade -version 2015120401 -epid vchangyi_oa
 * Create By zhoutao
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

	/** 新的数据库主机地址 */
	private $__dbhost = '';

	public function __construct() {
	}

	/**
	 * 初始化环境参数
	 *
	 * @param object $db
	 *        	数据库操作对象
	 * @param string $tablepre
	 *        	表前缀
	 * @param array $settings
	 *        	当前站点的setting
	 * @param array $options
	 *        	传输进来的外部参数
	 * @param array $params
	 *        	一些环境参数，来自触发执行本脚本
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
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_record'");
		if ($this->_db->fetch_row($query)) {

			// 读取签到 部门ID为1 的记录
			$wrong_data_uid = array();
			$query = $this->_db->query("SELECT DISTINCT m_uid FROM `oa_sign_record` WHERE `cd_id` = 1");
			while ($row = $this->_db->fetch_array($query)) {
				$wrong_data_uid[] = $row['m_uid'];
			}

			// 获取用户的cd_id
			if (!empty($wrong_data_uid)) {
				$str_uid = implode(',', $wrong_data_uid);
				$query = $this->_db->query("SELECT * FROM `oa_member` WHERE `m_uid` IN ({$str_uid})");
				$uid_cdid = array();
				while ($row = $this->_db->fetch_array($query)) {
					$uid_cdid[$row['m_uid']] = $row['cd_id'];
				}
			}

			// 更新数据
			foreach ($uid_cdid as $_uid => $_cdid) {
				$this->_db->query("UPDATE `oa_sign_record` SET `cd_id` = '{$_cdid}' WHERE `m_uid` = '{$_uid}' AND `cd_id` = '1'");
			}
		}

	}

}
