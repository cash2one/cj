<?php
/**
 * 请求外部接口测试
 * $Author$
 * $Id$
 */

class voa_client_test extends voa_client_base {

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
	 * 测试
	 * @param array $args 相关参数
	 */
	public function test($args) {
		/** 读取配置 */
		$cfg = config::get($this->_cfg_path.'.test');
		if (empty($cfg) || empty($cfg['method']) || empty($cfg['url'])) {
			return false;
		}

		/** 发送请求 */
		try {
			$this->url = $cfg['url'];
			$result = $this->_call_method($cfg['method'], array('args' => $args));
		} catch (Exception $e) {
			logger::error($e);
			return false;
		}

		return $result;
	}
}
