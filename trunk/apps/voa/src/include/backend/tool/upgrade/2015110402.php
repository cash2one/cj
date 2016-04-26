<?php
/**
 * 投票调研升级脚本.2015110402
 * User: Muzhitao
 * Date: 2015/11/3 0003
 * Time: 15:33
 * Email：muzhitao@vchangyi.com
 */

error_reporting(E_ALL);
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
	 * @return void
	 */
	public function run() {

		error_reporting(E_ALL);
		// 获取应用信息
		$query = $this->_db->query("SELECT * FROM `oa_common_plugin` WHERE `cp_identifier`='nvote' LIMIT 1");
		$this->__plugin = $this->_db->fetch_array($query);

		// 判断应用表是否存在
		if ($this->__plugin) {

			// 更新表结构
			$this->_plugin_table();
			// 微信企业号菜单更新
			$this->_plugin_wxqymenu();
		}

		// 清理缓存
		$this->_cache_clear();
	}

	/**
	 * 表结构升级
	 */
	protected function _plugin_table() {

		$this->_db->query("INSERT INTO `oa_nvote_setting` (`key`, `value`, `type`, `comment`, `status`, `created`, `updated`, `deleted`) VALUES
('all', '1', 0, '发布权限   0=>非全部 1=>全部', 2, 1436264847, 1446433160, 0),
('cd_ids', '', 0, '部门数据列表json', 2, 0, 1446433160, 0),
('m_uids', '', 0, '用户数据列表json', 2, 0, 1446433160, 0);");

		$this->_db->query("INSERT INTO `oa_common_cpmenu` (`ccm_id`, `cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_subnavdisplay`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`) VALUES
('', {$this->__plugin['cp_pluginid']}, 0, 'office', 'nvote', 'issue', 'subop', 0, '设置权限', 'fa-gear', 105, 1030, 1, 1, 1439173071, 1439173071, 0);");

		// 删除唯一性索引
		$this->_db->query("ALTER TABLE oa_nvote_mem_option DROP INDEX nvote;");

		// 建立普通索引
		$this->_db->query("ALTER TABLE `oa_nvote_mem_option` ADD INDEX( `nvote_id`, `nvote_option_id`, `m_uid`);");
	}

	/**
	 * 通过查询数据库更新菜单
	 */
	protected function _plugin_wxqymenu() {

		$domain = $this->_settings['domain'];

		$api_url = 'http://' . $domain . '/api/common/post/updatewxqymenu/';

		$post = array(
			'pluginid' =>  $this->__plugin['cp_pluginid'],
			'time' => startup_env::get('timestamp'),
			'hash' => '',
		);

		$result = array();
		if (!$this->__get_json_by_post($result, $api_url, $post)) {
			logger::error($api_url . "||".$this->__plugin['cp_pluginid']."||" . print_r($result, true));
		}

		return true;
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
		$result = $snoopy->submit($url, $post);
		// 如果读取错误
		if (!$result || 200 != $snoopy->status) {
			logger::error('$snoopy->submit error: ' . $url . '|' . $result . '|' . $snoopy->status);
			return false;
		}

		$data = (array) json_decode($snoopy->results, true);
		if (empty($data) || !empty($data['errcode'])) {
			logger::error('$snoopy->submit error: ' . $url . '|' . $snoopy->results . '|' . $snoopy->status);
			return false;
		}

		if ($data['errcode'] == '45009') {
			// 如果接口请求超限，则稍等10秒重试
			echo '[...wait retry...]';
			sleep(mt_rand(6, 12));
			$data = array();
			return $this->__get_json_by_post($data, $url, $post);
		}

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
		@unlink($cachedir.DIRECTORY_SEPARATOR.'plugin.'.$this->__plugin['cp_identifier'].'.setting.php');

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
}

//end
