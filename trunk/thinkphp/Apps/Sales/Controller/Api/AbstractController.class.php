<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Sales\Controller\Api;
use Common\Common\Cache;
use Common\Common\Plugin;

abstract class AbstractController extends \Common\Controller\Api\AbstractController {

	// 获取插件配置
	protected function _get_plugin() {

		// 获取插件信息
		//$this->_plugin = &Plugin::instance('Sales');
		//
		//// 更新 pluginid, agentid 配置
		//cfg('pluginid', $this->_plugin->get_pluginid());
		//cfg('agentid', $this->_plugin->get_agentid());
		return true;
	}
}
