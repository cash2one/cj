<?php
/**
 * 畅移主站调 开通体验账号 的接口
 * $Author$
 * $Id$
 */

class voa_client_experience extends voa_client_base {

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
	 * 开通 体验账号
	 * @param array $args 相关参数
	 * @param array $error 验证错误信息
	 */
	public function open($args, &$error) {
		/** 读取配置 */
		$cfg = config::get($this->_cfg_path.'.experience');
		if (empty($cfg) || empty($cfg['method']) || empty($cfg['url'])) {
			return false;
		}
		
		/** 发送请求 */
		try {
			$this->url = $cfg['url'];
			$result = $this->_call_method($cfg['method'], array('args' => $args));
			/** 检测返回值为数组 即为错误数组 暂且过滤同手机多次提交 报错文案 */
			if (is_array($result)) {
				$error = $result;
				/** return false; */
			}
		} catch (Exception $e) {
			/** 错误日志 */
			logger::error($e);
			return false;
		}
		return true;
	}
}
