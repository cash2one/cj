<?php
/**
 * agentmenu.php
 * 命令行方式管理企业微信应用型代理菜单
 * @uses php tool.php -n agentmenu
 * -domain (二级域名, 如:url地址为 demo.vhcnagyi.com 时, 该值为 demo)
 * -plugin (插件唯一标识符，如：askoff)
 * -pluginid (企业应用型代理id，如：3)
 * -action [操作动作：create|delete|get]
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_backend_tool_agentmenu extends voa_backend_base {

	private $__opts = array();
	/** 动作方法前缀名 */
	protected $__prefix = '___';
	/** 企业微信应用型代理菜单处理类 */
	private $_qywx_menu = null;

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
		startup_env::set('domain', $opts['domain']);
		voa_h_conf::init_db();
	}

	public function main() {

		return;
		// 要执行的动作名
		$action = isset($this->__opts['action']) ? $this->__opts['action'] : '';

		// 动作所要执行的方法
		$class_name = $this->__prefix.$action;

		if (!method_exists($this, $class_name)) {
			// 指定的动作不存在
			$msg = '-action not defined.';
			if ($action) {
				$msg .= PHP_EOL.'Class "'.get_class().'" method "'.$class_name.'" not exists.';
			}
			return $this->___help($msg);
		}

		$this->_qywx_menu = new voa_wxqy_menu();

		// 执行具体的动作
		return call_user_func(array($this, $class_name));
	}

	/**
	 * 帮助指令
	 * @param string $message 额外显示的信息
	 * @return boolean
	 */
	private function ___help($message = '') {

		// 获取当前类的所有方法名
		$methods = get_class_methods(get_class());

		// 分析当前类内的所有已定义的动作名
		$actions = array();
		foreach ($methods as $method_name) {
			if (stripos($method_name, $this->__prefix) !== 0) {
				continue;
			}
			$actions[] = "\t".'-action '.str_ireplace($this->__prefix, '', $method_name);
		}

		// 列表所有动作指令
		$actions_list = implode(PHP_EOL, $actions);

		$msg = $message ? 'ERROR: '.$message.PHP_EOL.PHP_EOL : '';
		$msg .= <<<EOF
Options:
{$actions_list}
EOF;
		return $this->__output($msg, false, false);
	}

	/**
	 * 为指定的插件唯一标识创建菜单
	 * 需要提供两个参数其一：
	 * -agentid 应用型代理id
	 * -plugind 插件的id
	 * @return boolean
	 */
	private function ___create() {

		// 当前的插件信息
		$plugin = array();
		if (!$this->__get_plugin($plugin)) {
			return false;
		}

		$agentid = $plugin['cp_agentid'];
		$identifier = $plugin['cp_identifier'];

		// 获取应用菜单数据
		$agent_menu = config::get(startup_env::get('app_name').'.application.'.$identifier.'.menu.qywx');
		if (empty($agent_menu)) {
			// 模块的菜单文件不存在
			return $this->__output('ERROR: application "'.$identifier.'" config file not exists or weixin aggent menu config empty.');
		}

		if (!$this->_qywx_menu->create($agentid, $agent_menu, $plugin['cp_pluginid'])) {
			return $this->__output('ERROR: create failed "'.$this->_qywx_menu->menu_error.'"');
		} else {
			return $this->__output('Create agent menu success', true);
		}
	}

	/**
	 * 删除指定的应用型代理的菜单
	 * 需要提供两个参数其一：
	 * -agentid 应用型代理id
	 * -plugind 插件的id
	 * @return boolean
	 */
	private function ___delete() {

		if (isset($this->__opts['agentid'])) {
			// 提供了应用型代理id，则直接使用

			$agentid = $this->__opts['agentid'];
		} else {
			// 根据环境参数来获取当前插件
			// 当前的插件信息
			$plugin = array();
			if (!$this->__get_plugin($plugin)) {
				return false;
			}
			$agentid = $plugin['cp_agentid'];
		}

		if (!$this->_qywx_menu->delete($agentid)) {
			return $this->__output('DELETE ERROR: '.$qywx_menu->menu_error);
		} else {
			return $this->__output('Delete agent menu success', true);
		}
	}

	private function ___get() {

		if (isset($this->__opts['agentid'])) {
			// 提供了应用型代理id，则直接使用

			$agentid = $this->__opts['agentid'];
		} else {
			// 根据环境参数来获取当前插件
			// 当前的插件信息
			$plugin = array();
			if (!$this->__get_plugin($plugin)) {
				return false;
			}
			$agentid = $plugin['cp_agentid'];
		}

		$data = array();
		if (!$this->_qywx_menu->get($agentid, $data)) {
			return $this->__output('GET ERROR: '.$qywx_menu->menu_error);
		} else {
			$msg[] = 'Get agent menu success';
			$msg[] = "=============================================";
			$msg[] = print_r($data, true);
			$msg[] = "=============================================";
			return $this->__output($msg, true);
		}

	}

	/** 重新更新菜单 */
	private function ___recreate() {

		if (0 < $this->__opts['pluginid']) {
			$this->___delete();
			$this->___create();
			return true;
		}

		/** 更新整个菜单 */
		$serv = &service::factory('voa_s_oa_common_plugin');
		$list = $serv->fetch_all();
		foreach ($list as $plugin) {
			if (0 >= $plugin['cp_agentid']) {
				continue;
			}

			$this->__opts['agentid'] = $plugin['cp_agentid'];
			$this->__opts['pluginid'] = $plugin['cp_pluginid'];
			$this->___delete();
			$this->___create();
		}

		return true;
	}

	/**
	 * 根据环境参数来获取要执行的插件信息
	 * @param array $plugin <strong style="color:red">(引用结果)</strong>获取到的插件信息数组
	 * @return boolean
	 */
	private function __get_plugin(&$plugin = array()) {
		if (!isset($this->__opts['pluginid']) && !isset($this->__opts['agentid'])) {
			// 插件的cp_pluginid 和 应用型代理id 必须提供其中一个
			$this->__output('-pluginid or -agentid must give one.');
			return false;
		}

		$serv = &service::factory('voa_s_oa_common_plugin', array());
		//cp_agentid
		if (isset($this->__opts['pluginid'])) {
			// 提供了插件id则找到插件
			$plugin = $serv->fetch_by_cp_pluginid($this->__opts['pluginid']);
			$null_msg = 'ERROR: -pluginid = '.$this->__opts['pluginid'].' not exists';
		} else {
			// 提供了应用型代理id
			$plugin = $serv->fetch_by_cp_agentid($this->__opts['agentid']);
			$null_msg = 'ERROR: -agentid = '.$this->__opts['agentid'].' not exists';
		}

		if (empty($plugin)) {
			// 插件不存在
			$this->__output($null_msg);
			return false;
		}

		return true;
	}

}
