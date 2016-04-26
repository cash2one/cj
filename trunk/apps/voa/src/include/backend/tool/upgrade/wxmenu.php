<?php
/**
 * wxmenu.php
 * 用于重建企业站应用微信菜单。
 * 注意！需要将菜单更新接口打开（/api/common/post/wxqymenuupdate.php）
 * php -q APP_PATH/backend/tool.php -n upgrade -version wxmenu -epid vchangyi_oa_upgrade
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

		$api_url = 'http://'.$this->_settings['domain'] . '/api/common/post/updatewxqymenu/';

		$log_filename = rgmdate(time(), 'Y-m-d');

		$this->__log($log_filename, rgmdate(time(), 'Y-m-d H:i:s')."\t".$this->_params['dbname']."\t".$this->_settings['domain']);

		// 读取所有已开启的应用信息
		$query = $this->_db->query("SELECT * FROM `oa_common_plugin` WHERE `cp_available`='".voa_d_oa_common_plugin::AVAILABLE_OPEN."'");
		while ($_p = $this->_db->fetch_array($query)) {

			// 随机等待2秒以内执行
			usleep(mt_rand(500000, 2000000));

			// 连接接口更新
			$result = array();
			$post = array(
				'pluginid' => $_p['cp_pluginid'],
				'time' => time(),
				'hash' => ''
			);
			$r = $this->__get_json_by_post($result, $api_url, $post);
			$_url = $api_url.'?'.http_build_query($post);
			$date = rgmdate(time(), 'Y-m-d H:i:s');
			if ($r !== true) {
				$this->__log($log_filename, "\t".$date."\t{$_p['cp_identifier']}\t".$_url . " - failed\t".$r);
			} else {
				$this->__log($log_filename, "\t".$date."\t{$_p['cp_identifier']}\t - succeed");
			}
		}

	}

	private function __log($filename, $data) {
		$file = APP_PATH."/data/wxqymenuupdate/".$filename.".log";
		$this->_params['upgrade']->rfwrite($file, $data."\n", 'a+');
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
			return 'NULL|'.$result.'|'.$snoopy->status;
		}

		/** 解析 json */
		$data = (array)json_decode($snoopy->results, true);
		if (empty($data) || !empty($data['errcode'])) {
			return $snoopy->results.'|'.$snoopy->status;
		}

		return true;
	}

}
