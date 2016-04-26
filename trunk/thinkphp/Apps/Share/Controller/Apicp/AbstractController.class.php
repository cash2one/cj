<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Share\Controller\Apicp;


abstract class AbstractController extends \Common\Controller\Apicp\AbstractController {

	public function before_action($action = '') {

		$this->_require_login = true;
		return parent::before_action($action);
	}

	public function after_action($action = '') {

		return parent::after_action($action);
	}

    // 获取插件配置
    protected function _get_plugin() {
        return;
/*        // 获取插件信息
        $this->_plugin = &Plugin::instance('jobtrain');

        // 更新 pluginid, agentid 配置
        cfg('PLUGIN_ID', $this->_plugin->get_pluginid());
		cfg('AGENT_ID', $this->_plugin->get_agentid());
		cfg('PLUGIN_IDENTIFIER', $this->_plugin->get_name());

        return true;*/
    }





}