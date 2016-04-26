<?php
/**
 * update.php
 * 微信企业自定义菜单更新
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_application_wxqymenu_update extends voa_uda_frontend_application_base {

	/** 请求的字段 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 其他参数 */
	private $__options = array();
	/** 当前操作的应用信息 */
	private $__plugin = array();

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 自定义方式更新微信企业号自定义菜单
	 * @param array $request 请求的数据
	 * + data 自定义菜单定义数据，可参看/cfg.bak/application/xxx.php 关于微信菜单的配置数据
	 * + pluginid 应用的ID，来自oa_common_plugin表的cp_pluginid字段
	 * @param array $result 无
	 * @param array $options 无
	 * @throws Exception
	 * @return bool
	 */
	public function doit($request, &$result = array(), $options = array()) {

		$this->__options = $options;

		// 字段规则定义
		$fields = array(
			'data' => array(
				'data',
				parent::VAR_ARR,
				array(),
				null,
				false
			),
			'pluginid' => array(
				'pluginid',
				parent::VAR_INT,
				array(),
				null,
				false
			)
		);
		// 字段检查
		if (!$this->extract_field($this->__request, $fields, $request)) {
			throw new Exception($this->errmsg, $this->errcode);
			return false;
		}

		if (!$this->__get_plugin()) {
			return false;
		}

		// 初始化微信菜单类
		$qywx_menu = new voa_wxqy_menu();
		if (!$qywx_menu->create($this->__plugin['cp_agentid'], $this->__request['data'], $this->__request['pluginid'])) {
			$error = empty($qywx_menu->errmsg) ? '更新应用菜单发生未知错误' : $qywx_menu->errmsg;
			$errcode = empty($qywx_menu->errcode);
			//throw new help_exception($error, $errcode);
			return voa_h_func::throw_errmsg($errcode.':'.$error);
		}

		return true;
	}

	/**
	 * 获取应用信息
	 * @return bool
	 */
	private function __get_plugin() {
		$sever_plugin = new voa_s_oa_common_plugin();
		$this->__plugin = $sever_plugin->fetch_by_cp_pluginid($this->__request['pluginid']);
		if (!$this->__plugin) {
			return voa_h_func::throw_errmsg('1002:应用信息不存在');
		}

		return true;
	}

}
