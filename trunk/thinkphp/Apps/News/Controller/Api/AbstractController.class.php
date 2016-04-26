<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace News\Controller\Api;
use Common\Common\Cache;
use Common\Common\Plugin;

abstract class AbstractController extends \Common\Controller\Api\AbstractController {

	public function before_action($action = '') {

		return parent::before_action();
	}

	/*
	 * 获取插件配置
	 */
	protected function _get_plugin() {

		// 获取插件信息
		$this->_plugin = &Plugin::instance('News');

		// 更新 pluginid, agentid 配置
		cfg('PLUGIN_ID', $this->_plugin->get_pluginid());
		cfg('AGENT_ID', $this->_plugin->get_agentid());
		cfg('PLUGIN_IDENTIFIER', $this->_plugin->get_name());
		return true;
	}

	public function match_url($url){
		if (!$url) {
			return false;
		}

		$url = trim($url);

		return strlen($url) < 4096 && preg_match('/^(http)s?:\/\/[\w]+\.[\w]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/i', $url);
	}

	public function after_action($action = '') {

		return parent::after_action();
	}

	/**
	 * 根据应用名称 获取应用相关信息
	 * @param $cp_identifier
	 * @return string
	 */
	public function get_plugin_id($cp_identifier) {

		// 字符串小写
		$cp_identifier = strtolower($cp_identifier);

		// 获取插件列表
		$cache = &Cache::instance();
		$plugins = $cache->get('Common.plugin');

		foreach($plugins as $_k => $_v) {
			if ($_v['cp_identifier'] == $cp_identifier) {
				return $plugins[$_k];
			}
		}

		return array();
	}

	/**
	 * 详情URL地址
	 * @param $ne_id
	 * @return string
	 */
	protected function _view_url($ne_id) {

		// 插件应用信息
		$plugins = $this->get_plugin_id('news');

		$cache = &\Common\Common\Cache::instance();
		$sets = $cache->get('Common.setting');
		$face_base_url = cfg('PROTOCAL') . $sets ['domain'];

		$pluginid = cfg('PLUGIN_ID');
		$url = $face_base_url . "/frontend/news/view?newsId=" . $ne_id . "&action=view&pluginid=" . $pluginid;

		return $url;
	}
}
