<?php
/**
 * 重建站点配置信息以及更新站点微信菜单
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

		// 新数据库主机地址
		$this->__dbhost = '182.254.149.180';

		$steps = array(
			'recreate_setting', // 重建用户站点目录、更新setting表数据库信息、写入数据库配置文件
			'update_wxqymenu' // 更新微信菜单
		);

		foreach ($steps as $_step) {
			$classname = '_' . $_step;
			$this->$classname();
		}
	}

	/**
	 * 重建用户站点目录、更新setting表数据库信息、写入数据库配置文件
	 */
	public function _recreate_setting() {

		/*

		// 当前站点缓存目录
		$sitedir = voa_h_func::get_sitedir(voa_h_func::get_domain($this->_settings['domain']));
		if (! is_dir($sitedir)) {
			rmkdir($sitedir, 0777, true);
		}

		// 写入文件缓存
		$file = $sitedir . 'dbconf.inc.php';
		$conf = array(
			'host' => $this->__dbhost,
			'dbname' => 'ep_' . $this->_settings['ep_id'],
			'user' => 'ep_' . $this->_settings['ep_id'],
			'pw' => $this->_settings['ep_id'] . '@vChangYi'
		);
		rfwrite($file, "<?php\n//wbs! cache file, DO NOT modify me!\n//Created on "
				. rgmdate("M j, Y, G:i") . "\n\n\$conf = " . rvar_export($conf) . ";\n\n");

		// 更新setting表数据库主机地址
		$this->_db->query("UPDATE `oa_common_setting` SET `cs_value`='{$conf['host']}' WHERE `cs_key`='domain'");

		*/
	}

	/**
	 * 更新应用菜单
	 */
	public function _update_wxqymenu() {

		// 更新微信菜单的接口
		$api_url = 'http://'.$this->_settings['domain'] . '/api/common/post/updatewxqymenu/';
		echo $api_url."\n";
		// 时间戳
		$timestamp = startup_env::get('timestamp');
		// 读取所有应用列表
		$query = $this->_db->query("SELECT * FROM `oa_common_plugin`");
		while ($_p = $this->_db->fetch_array($query)) {

			if ($_p['cp_available'] != voa_d_oa_common_plugin::AVAILABLE_OPEN) {
				// 未开启则忽略
				continue;
			}

			echo $_p['cp_pluginid'].'->';

			// 连接接口更新
			$result = array();
			$post = array(
				'pluginid' => $_p['cp_pluginid'],
				'time' => $timestamp,
				'hash' => ''
			);
			if (!$this->__get_json_by_post($result, $api_url, $post)) {
				logger::error($api_url . "||" . print_r($post, true) . "||" . print_r($result, true) . "\n");
			}
		}

		// 随机间隔1秒以内的时间执行
		usleep(mt_rand(1, 1000000));
	}

	/**
	 * 读取远程api数据
	 * @param unknown $data
	 * @param unknown $url
	 * @param string $post
	 * @return boolean
	 */
	private function __get_json_by_post(&$data, $url, $post = '') {
		$snoopy = new snoopy();
		$snoopy->proxy_host = '127.0.0.1';
		$snoopy->proxy_port = 80;
		$snoopy->_isproxy = 1;
		$result = $snoopy->submit($url, $post);
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

		return true;
	}

}
