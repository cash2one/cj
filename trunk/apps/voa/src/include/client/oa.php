<?php
/**
 * 畅移主站调 voa 的接口相关
 * $Author$
 * $Id$
 */

class voa_client_oa extends voa_client_base {

	/**
	 * __construct
	 * 接口服务构造方法
	 *
	 * @param  mixed $serverName
	 * @return void
	 */
	public function __construct($auth_key) {

		parent::__construct($auth_key);
	}

	/**
	 * 自动开启企业 oa
	 * @param array $args 注册相关参数
	 */
	public function auto_open($args) {
		/** 读取配置 */
		$cfg = config::get($this->_cfg_path.'.voa_open');
		if (empty($cfg) || empty($cfg['method']) || empty($cfg['url'])) {
			return false;
		}

		/** 发送请求 */
		try {
			$this->url = $cfg['url'];
			$result = $this->_call_method($cfg['method'], $args);
		} catch (Exception $e) {
			logger::error($e);
		}

		return $result;
	}
}
