<?php
/**
 * 20150915.php
 * 企业信息脚本(IP,IP地址)
 * Create By gaosong
 * $Author$
 * $Id$
 */
error_reporting(E_ALL);

class execute {

	/**
	 * 数据库操作对象
	 */
	protected $_db = null;

	/**
	 * 表前缀
	 */
	protected $_tablepre = 'cy_';

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

		// 更新表结构（第一次执行，需要更新表结构，以后不需要）
		$this->_plugin_table();

		// 更新数据
		$this->_update_data();

		// 清理缓存
		$this->_cache_clear();
	}

	/**
	 * 表结构升级（新增ip、ip地址）
	 */
	protected function _plugin_table() {

		$this->_db->query("USE vchangyi_admincp");

		$this->_db->query("ALTER TABLE `cy_enterprise_profile` ADD `ep_fromip` VARCHAR(15)
				NOT NULL DEFAULT '0.0.0.0' COMMENT '来源IP' AFTER `ep_ref`");

		$this->_db->query("ALTER TABLE `cy_enterprise_profile` ADD `ep_fromadress` VARCHAR(200)
				 NOT NULL DEFAULT '' COMMENT '来源详细地址' AFTER  `ep_fromip`;");
	}

	protected function _update_data() {

		$this->_db->query("USE vchangyi_admincp");

		// 获取注册企业数量
		$e_count = $this->_db->query("SELECT COUNT(ep_id) as count FROM `cy_enterprise_profile`
				 WHERE ep_fromip = '0.0.0.0' ");
		$count = $this->_db->fetch_row($e_count);
		$count = $count[0];
		// 企业信息
		$company = array();
		// 企业注册ip地址
		$sms = array();
		// IP地址
		$address_list = array();

		// 注册企业数量小于等于100，不批量查询，否则批量查询
		if ($count <= 100) {
			$this->_get_address(1, $company, $address_list);
		} else {
			// 一次100条数据批量查询
			for($i = 1; $i <= ceil($count / 100); $i ++) {
				$this->_get_address($i, $company, $address_list);
			}
		}

		$this->_db->query("USE vchangyi_admincp");
		// 加载ip转换地址类
		$ip2address = new ip2address();

		// 开始更新
		foreach ($company as $_k => $_v) {

			$ep_id = $_v['ep_id'];
			if (! array_key_exists($_v['ep_mobilephone'], $address_list)) {
				continue;
			}
			$ip = $address_list[$_v['ep_mobilephone']];
			$ip = $ip['smscode_ip'];

			if ($ip2address->get($ip)) {
				$address = $ip2address->result['address'];
				// 更新注册用户ip、ip地址
				$this->_db->query("UPDATE `cy_enterprise_profile` SET ep_fromip = '{$ip}',
				ep_fromadress = '{$address}' WHERE ep_id = {$ep_id}");
			}
		}
	}

	/**
	 * 根据ip，获取ip地址
	 *
	 * @param int $i
	 * @param array $company
	 * @param array $address_list
	 */
	protected function _get_address($i, &$company, &$address_list) {

		$temp = array();
		// 获取企业信息
		$this->_db->query("USE vchangyi_admincp");

		$enterise_result = $this->_db->query("SELECT ep_id,ep_mobilephone FROM
						`cy_enterprise_profile` WHERE ep_fromip = '0.0.0.0' limit {$i},100");
		// 重组企业信息数据
		while ($e_list = $this->_db->fetch_array($enterise_result)) {
			$temp[$e_list['ep_id']] = $e_list;
			$company[$e_list['ep_id']] = $e_list;
		}

		// 获取注册用户手机号
		$phones = array();
		foreach ($temp as $_k => $_v) {
			$phones[] = $_v['ep_mobilephone'];
		}
		$this->_db->query("Use vucenter");
		// 根据手机号，查询注册用户ip地址
		$phones_str = implode(',', $phones);
		$ip_result = $this->_db->query("SELECT DISTINCT(smscode_mobile), smscode_ip FROM
				`uc_smscode` WHERE smscode_mobile IN ({$phones_str})");

		while ($ip_list = $this->_db->fetch_array($ip_result)) {
			$address_list[$ip_list['smscode_mobile']] = $ip_list;
		}
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
	}

}
