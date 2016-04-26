<?php
/**
 * 获取菜单未更新的站点
 * php -q tool.php -n upgrade -version 20150416 -epid vchangyi_oa_upgrade
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
	 *
	 * @return void
	 */
	public function run() {
		error_reporting(E_ALL);

		$steps = array(
			'getdomain', // 获取未更新的站点
		);

		foreach ($steps as $_step) {
			$classname = '_' . $_step;
			$this->$classname();
		}
	}

	/**
	 * 获取未更新的站点
	 */
	public function _getdomain() {

		// 1个月没更新的则暂时忽略该站的读取
		if (!$this->_db->result_first("SELECT COUNT(`m_updated`) FROM `oa_member` WHERE `m_updated`>".(time() - 86400 * 30))) {
			return;
		}

		// 更新微信菜单的接口
		$api_url = 'http://' . $this->_settings['domain'] . '/api/common/get/wxqymenu/';
		// 时间戳
		$timestamp = startup_env::get('timestamp');
		// 读取所有应用列表
		$query = $this->_db->query("SELECT * FROM `oa_common_plugin`");
		while ($_p = $this->_db->fetch_array($query)) {

			if ($_p['cp_available'] != voa_d_oa_common_plugin::AVAILABLE_OPEN) {
				// 未开启则忽略
				continue;
			}

			$api_url .= '?pluginid='.$_p['cp_pluginid'];
			// 读取菜单
			$result = array();
			$r = $this->__get_json_by_get($result, $api_url);
			if ($r === null) {
				// 存在未更新的菜单，则退出
				// 记录未更新菜单的站点
				$file = APP_PATH.'/data/_domain.txt';
				$_wdata = "|".$this->_settings['domain']."|";
				if (stripos(file_get_contents($file), $_wdata) === false) {
					rfwrite($file, $this->_settings['ep_id'].$_wdata.$_p['cp_pluginid']."\n", 'ab');
				}
				break;
			}
			// 读取菜单出错
			if (!$r) {
				logger::error($api_url . "||" . $_p['cp_pluginid'] . "||" . print_r($result, true) . "\n");
			}
		}

		// 随机间隔1秒以内的时间执行
		usleep(mt_rand(1, 1000000));
	}

	/**
	 * 读取远程api数据
	 * @param unknown $data
	 * @param unknown $url
	 * @return boolean
	 */
	private function __get_json_by_get(&$data, $url) {
		$snoopy = new snoopy();
		$snoopy->proxy_host = '127.0.0.1';
		$snoopy->proxy_port = 80;
		$snoopy->_isproxy = 1;
		$result = $snoopy->fetch($url);
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

		// 存在未更新的菜单
		if (stripos($snoopy->results, 'https://open.weixin.qq.com') !== false) {
			return null;
		}

		return true;
	}

}
