<?php
/**
 * AbstractController.class.php
 * $author$
 */
namespace Askfor\Controller\Apicp;

use Common\Common\Plugin;
use Common\Common\Cache;
use Askfor\Model\AskforProcModel;
use Askfor\Model\AskforModel;

abstract class AbstractController extends \Common\Controller\Apicp\AbstractController {

	const ACTIVE = 1; // 达到状态
	const UNACTIVE = 0; // 没有达到状态

	protected $_serv_askfor = null; // 审批表
	protected $_serv_proc = null; // 审批进度表
	protected $_serv_custom = null; // 自定义数据
	protected $_serv_customcols = null; // 自定义字段结构
	protected $_serv_template = null; // 模板
	protected $_serv_att = null; // 附件

	protected $_operator_proc = ''; // 当前操作人
	protected $_level = ''; // 当前等级

	public function before_action() {

		return parent::before_action();
	}

	public function after_action() {

		return parent::after_action();
	}

	// 获取插件配置
	protected function _get_plugin() {

		// 获取插件信息
		$this->_plugin = &Plugin::instance('askfor');

		// 更新 pluginid, agentid 配置
		cfg('PLUGIN_ID', $this->_plugin->get_pluginid());
		cfg('AGENT_ID', $this->_plugin->get_agentid());
		cfg('PLUGIN_IDENTIFIER', $this->_plugin->get_name());

		return true;
	}

	/**
	 * 获取部门列表方法
	 * @return mixed 部门列表
	 */
	public function department_list() {

		$cache = &\Common\Common\Cache::instance();
		$list = $cache->get('Common.department');

		return $list;
	}

	/**
	 * 从缓存获取模板列表的方法
	 * @return mixed 模板列表
	 */
	public function askfor_templist() {

		$cache = &\Common\Common\Cache::instance();
		$tmplist = $cache->get('Askfor.template');

		return $tmplist;
	}
}
