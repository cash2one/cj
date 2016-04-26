<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Exam\Controller\Frontend;
use Common\Common\Cache;
use Common\Common\Plugin;

abstract class AbstractController extends \Common\Controller\Frontend\AbstractController {

	public function before_action($action = '') {

		return parent::before_action($action);
	}

	public function after_action($action = '') {

		return parent::after_action($action);
	}

	/**
	 * 获取插件配置
	 */
	protected function _get_plugin() {
		// 获取插件信息
		$this->_plugin = &Plugin::instance('Exam');

		// 更新 pluginid, agentid 配置
		cfg('PLUGIN_ID', $this->_plugin->get_pluginid());
		cfg('AGENT_ID', $this->_plugin->get_agentid());
		cfg('PLUGIN_IDENTIFIER', $this->_plugin->get_name());
		return true;
	}

}
