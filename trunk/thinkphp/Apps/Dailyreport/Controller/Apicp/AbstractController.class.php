<?php
/**
 * AbstractController.class.php
 * $author$
 */
namespace Dailyreport\Controller\Apicp;
use Common\Common\Plugin;
abstract class AbstractController extends \Common\Controller\Apicp\AbstractController {
	public function before_action($action = '') {

		if (!parent::before_action($action)) {
			return false;
		}
		return true;
	}
        // 获取插件配置
	protected function _get_plugin() {
		$this->_plugin = &Plugin::instance('dailyreport');
		// 更新 pluginid, agentid 配置
		//cfg('PLUGIN_ID', $this->_plugin->get_pluginid());
		//cfg('AGENT_ID', $this->_plugin->get_agentid());

		return false;
	}
}
