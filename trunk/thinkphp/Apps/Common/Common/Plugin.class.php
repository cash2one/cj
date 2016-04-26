<?php
/**
 * Plugin.class.php
 * 插件操作
 * $Author$
 */

namespace Common\Common;
use Think\Log;
use Common\Common\Cache;

class Plugin {

	// 插件信息
	public $info = array();
	// 插件配置
	public $setting = array();
	// 插件名称
	public $_name = '';

	// 实例化
	public static function &instance($plugin_name) {

		static $instance;
		// 如果插件名称为空, 则报错
		if (empty($plugin_name)) {
			E(L('_ERR_PLUGIN_NAME_IS_EMPTY'));
			return false;
		}

		$plugin_name = ucfirst($plugin_name);
		$md5 = md5($plugin_name);
		if (empty($instance[$md5])) {
			$instance[$md5] = new self($plugin_name);
		}

		return $instance[$md5];
	}

	// 构造方法
	public function __construct($plugin_name) {

		$this->_name = $plugin_name;
		$this->init();
	}

	// 初始化, 获取插件信息以及插件配置信息
	public function init() {

		// 获取插件列表
		$cache = &\Common\Common\Cache::instance();
		$plugins = $cache->get('Common.plugin');

		try {
			// 获取插件配置信息
			$this->setting = $cache->get($this->_name . '.setting');
		} catch (\Exception $e) {
			// 记录异常
			\Think\Log::record($e->getMessage() . ':' . $e->getCode());
			$this->setting = array();
		}

		// pluginid, agentid
		$pluginid = isset($this->setting['pluginid']) ? $this->setting['pluginid'] : 0;
		$agentid = isset($this->setting['agentid']) ? $this->setting['agentid'] : 0;
		// 如果应用信息不存在
		if (! array_key_exists($pluginid, $plugins) || empty($this->setting) || empty($agentid)) {
			E('_ERR_PLUGIN_IS_LOST');
			return false;
		}

		// 获取应用信息
		$this->info = $plugins[$pluginid];

		// 判断应用是否关闭
		if ($this->info['cp_available'] != \Common\Model\CommonPluginModel::AVAILABLE_OPEN) {
			E('_ERR_PLUGIN_IS_CLOSED_OR_UNOPEN');
			return false;
		}

		return true;
	}

	// 获取 pluginid
	public function get_pluginid() {

		return $this->setting['pluginid'];
	}

	// 获取 agentid
	public function get_agentid() {

		return $this->setting['agentid'];
	}

	// 获取插件名称
	public function get_name() {

		return $this->_name;
	}

}
