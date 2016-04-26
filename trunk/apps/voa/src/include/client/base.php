<?php
/**
 * voa 调外部接口基类
 * $Author$
 * $Id$
 */

class voa_client_base extends rpc_client {
	/** client 接口配置路径 */
	protected $_cfg_path = '';

	/**
	 * __construct
	 * 接口服务构造方法
	 *
	 * @param  mixed $serverName
	 * @return void
	 */
	public function __construct($auth_key) {

		$this->_cfg_path = startup_env::get('app_name').'.rpc.client';
		parent::__construct($auth_key);
	}

	/**
	 * _create_post_string
	 * 创建sig 及 post 数据串
	 *
	 * @param  mixed $params
	 * @param  mixed $args
	 * @return void
	 */
	protected function _create_post_data($params, $args) {
		$pas = array_merge($params, $args);
		/** 加密 */
		$tea = new crypt_xxtea($this->auth_key);
		$data = $tea->encrypt(serialize($pas));
		return array('data' => $data);
	}

	/**
	 * 调用接口
	 * @param string $url api地址
	 * @param string $method 调用的方法
	 * @param array $args 参数
	 * @param string $host_ip 指定主机IP，默认为空
	 * @return unknown
	 */
	public function call($url, $method, $args, $host_ip = '') {
		$result = array();

		try {
			$this->url = $url;
			$this->host_ip = $host_ip;
			$result = $this->_call_method($method, array('args' => $args), $host_ip);
		} catch (Exception $e) {
			logger::error($url.'|'.$host_ip.'|'.$method.'|'.var_export($args, true));
			logger::error($e);
		}

		return $result;
	}

}
