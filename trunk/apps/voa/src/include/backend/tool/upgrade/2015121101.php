<?php
/**
 * Created by PhpStorm.
 * 审批 旧数据迭代 去除老数据里重复出现的审批人
 * User: zhoutao
 * php -q tool.php -n upgrade -version 2015121101 -epid vchangyi_oa
 * Time: 下午3:56
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
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_askfor'");
		if ($this->_db->fetch_row($query)) {

			// 读取 审批中并且 没有删除的审批
			$query = $this->_db->query("SELECT `af_id`,`aft_id` FROM `oa_askfor` WHERE `af_condition` = 1 AND `af_status` < 3");
			while ($row = $this->_db->fetch_array($query)) {
				// 查询关于 审批的 进度
				$query_proc = $this->_db->query("SELECT `m_uid` FROM `oa_askfor_proc` WHERE `af_id` = {$row['af_id']}");
				// 进度中出现的uid
				$proc_uids = array();
				while ($rows = $this->_db->fetch_array($query_proc)) {
					if (in_array($rows['m_uid'], $proc_uids)) {
						$this->_db->query("UPDATE `oa_askfor_proc` SET `afp_status` = 3 WHERE `afp_condition` = 5 AND `m_uid` = {$rows['m_uid']} AND `af_id` = {$row['af_id']}");
					}
					$proc_uids[] = $rows['m_uid'];
				}

				// 如果是固定审批, 把进度中转审批 改为已经同意
				if (!empty($row['aft_id'])) {
					$this->_db->query("UPDATE `oa_askfor_proc` SET `afp_condition` = 2 WHERE `afp_condition` = 3 AND `af_id` = {$row['af_id']}");
				}
			}

		}
	}

}
