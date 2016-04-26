<?php

namespace Jobtrain\Controller\Api;
use Common\Common\Cache;
use Common\Common\Plugin;

abstract class AbstractController extends \Common\Controller\Api\AbstractController {

	public function before_action($action = '') {

		if (parent::before_action($action)) {
			$this->check_right();
			return true;
		}

		return false;
	}

	/**
	 * 获取插件配置
	 */
	protected function _get_plugin() {

		// 获取插件信息
		$this->_plugin = &Plugin::instance('Jobtrain');

		// 更新 pluginid, agentid 配置
		cfg('PLUGIN_ID', $this->_plugin->get_pluginid());
		cfg('AGENT_ID', $this->_plugin->get_agentid());
		cfg('PLUGIN_IDENTIFIER', $this->_plugin->get_name());

		return true;
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
	 * 后加入的用户获取权限
	 */
	protected function check_right() {

	}
}
