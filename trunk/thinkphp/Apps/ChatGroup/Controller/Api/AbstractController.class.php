<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace ChatGroup\Controller\Api;

use Common\Common\Plugin;

abstract class AbstractController extends \Common\Controller\Api\AbstractController {

	// 获取插件配置
	protected function _get_plugin() {

		// 获取插件信息
		$this->_plugin = &Plugin::instance('ChatGroup');

		// 更新 pluginid, agentid 配置
		cfg('PLUGIN_ID', $this->_plugin->get_pluginid());
		cfg('AGENT_ID', $this->_plugin->get_agentid());
		cfg('PLUGIN_IDENTIFIER', $this->_plugin->get_name());
		return true;
	}

	/**
	 * 如果用户在群组里, 则返回 true, 否则返回 false
	 * @param int $uid 用户ID
	 * @param int $cgid 群组ID
	 * @return boolean
	 */
	protected function _is_in_chatgroup($uid, $cgid) {

		$serv = D('ChatGroup/ChatgroupMember', 'Service');
		return $serv->is_in_chatgroup($uid, $cgid);
	}

}
