<?php
/**
 * Created by PhpStorm.
 * User: Muzhitao
 * Date: 2015/11/2 0002
 * Time: 09:56
 * Email：muzhitao@vchangyi.com
 */

class voa_c_api_common_post_databasewxqymenu extends voa_c_api_common_abstract {
	/** 当前请求更新的应用信息 */
	private $__plugin = array();

	/** 是否开启该接口 */
	private $__is_open = true;

	protected function _before_action($action) {
		$this->_require_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		// 需要传入的参数
		$fields = array(
			// 要更新的本地应用ID
			'pluginid' => array('type' => 'int', 'required' => false),
			// 要更新的微信应用ID
			'agentid' => array('type' => 'string_trim', 'required' => false),
			// 应用的唯一标识符
			'identifier' => array('type' => 'string_trim', 'required' => false),
			// 提交的数据
			'data' => array('type' => 'array', 'required' => 'true')
		);

		// 参数数值基本检查验证
		$this->_check_params($fields);

		if (!$this->__is_open) {
			$this->_set_errcode('100:远程更新微信企业号自定义菜单接口未开启');
			return false;
		}

		if (!$this->__get_plugin()) {
			return false;
		}

		//$agent_menu = config::get(startup_env::get('app_name').'.application.'.$this->__plugin['cp_identifier'].'.menu.qywx');
		$agent_menu = $this->_params['data'];
		if (empty($agent_menu)) {
			$this->_set_errcode('1012:应用未配置微信企业号自定义菜单');
			return false;
		}

		// 加载企业微信应用型代理菜单接口类
		$qywx_menu = new voa_wxqy_menu();
		if (!$qywx_menu->create($this->__plugin['cp_agentid'], $agent_menu, $this->__plugin['cp_pluginid'])) {
			$error = empty($qywx_menu->menu_error) ? '更新应用菜单发生未知错误' : $qywx_menu->menu_error;
			$this->_set_errcode('1013:'.$error);
			return false;
		}

		return true;
	}

	/**
	 * 获取应用信息
	 * @param int $pluginid
	 * @param string $agentid
	 * @param string $identifier
	 * @return boolean
	 */
	private function __get_plugin() {

		$s_plugin = new voa_s_oa_common_plugin();

		if ($this->_params['pluginid']) {
			// 通过应用本地ID获取

			$this->__plugin = $s_plugin->fetch_by_cp_pluginid($this->_params['pluginid']);

		} elseif ($this->_params['identifier']) {
			// 通过应用唯一标识符获取

			$this->__plugin = $s_plugin->fetch_by_identifier($this->_params['identifier']);

		} else{

			$this->_set_errcode('1001:应用ID 或 唯一标识符指定错误');
			return false;
		}

		if (empty($this->__plugin)) {

			$this->_set_errcode('1002:指定应用不存在');

			return false;
		}

		return true;
	}
}
