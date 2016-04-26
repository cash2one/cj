<?php
/**
 * rpc_client
 * 调用客户端方法
 *
 * $Author$
 * $Id$
 */

class rpc_client {
	/** 密钥相关 */
	var $auth_key;
	/** 错误号/消息 */
	var $errno;
	var $errmsg;
	var $format = 'PHP';
	/** 客户端 url */
	var $url;
	var $debug = false;

	public function __construct($auth_key) {
		$this->auth_key = $auth_key;
	}

	/* profile_set_vcy
	 * 客户端方法示示例
	 *
	 * <code>
	 * function profile_set_vcy($myml, $uid = 0) {
	 *     return $this->_call_method('profile.set_vcy', array('myml'=> $myml, 'uid' => $uid));
	 * }
	 * </code>
	 */

	/**
	 * _init_params
	 * 必要参数初始化
	 *
	 * @param  mixed $method
	 * @return void
	 */
	protected function _init_params($method) {
		$params = array();
		$params['method'] = $method;
		$params['format'] = strtoupper($this->format);

		return $params;
	}

	/**
	 * _call_method
	 * 回调方法
	 *
	 * @param  mixed $method
	 * @param  mixed $args
	 * @return void
	 */
	protected function _call_method($method, $args, $host_ip = '') {
		$this->errno = 0;
		$this->errmsg = '';
		$url = $this->url;

		/** 初始化参数 */
		$params = $this->_init_params($method);
		/** 生成 post 信息 */
		$data = $this->_create_post_data($params, $args);
		/**
		 * 发送请求
		 * $status http 状态值
		 * $errmsg 错误信息
		 * $result 读取是否成功
		 * $body 返回的内容
		 */
		list($status, $errmsg, $result, $body) = $this->post_request($url, $data, $host_ip);
		logger::error(var_export($params, true));
		logger::error(var_export($args, true));
		logger::error('URL: ' . $url . '; ip:' . $host_ip);
		if ($this->debug) {
			$this->message('receive data ='.var_export($result, true)."\n\n\n\n".var_export($body, true)."\n\n");
		}

		$arr = @unserialize($body);
		//logger::error($host_ip.' => '.$url);
		//logger::error(var_export($data, true));
		/** 如果返回值为 false 或者返回的状态不为 200 或者反序列化失败 */
		if (false === $result || 200 != $status || $arr === false) {
			$this->errno = 509;
			$this->errmsg = 'request error.';
			logger::error($this->errmsg.'[1.'.(false===$result).'][2.'.(200!=$status).'][3.'.($arr===false).'][4.'.$status.'][5.'.$errmsg.'][6.'.$body.']', $this->errno);
			logger::error($url.var_export($data, true).$host_ip);
			throw new rpc_exception($this->errmsg.'[1.'.(false===$result).'][2.'.(200!=$status).'][3.'.($arr===false).'][4.'.$status.'][5.'.$errmsg.'][6.'.$body.']', $this->errno);
		}

		/** 如果返回了错误 */
		if ($arr['errno']) {
			$this->errno = $arr['errno'];
			$this->errmsg = $arr['errmsg'];
			logger::error($this->errmsg.'[1.'.(false===$result).'][2.'.(200!=$status).'][3.'.($arr===false).'][4.'.$status.'][5.'.$errmsg.'][6.'.$body.']', $this->errno);
			throw new rpc_exception($arr['errmsg'], $arr['errno']);
		}

		return $arr['result'];
	}

	/**
	 * _generate_sig
	 * sig生成方法
	 *
	 * @param  mixed $args
	 * @return void
	 */
	protected function _generate_sig($args) {
		$str = $this->_rhttp_build_query($args);
		if ($this->debug) {
			$this->message('sig string :'.$str.$this->auth_key."\n\n");
		}

		return md5($str.$this->auth_key);
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
		/** 生成验证字串 */
		$pas['sig'] = $this->_generate_sig($pas);
		return $pas;
	}

	/**
	 * 重写 http_build_query, 使其按顺序排列
	 * @param array $args 参数
	 */
	protected function _rhttp_build_query($args) {
		foreach ($args as &$v) {
			if (is_array($v)) {
				$v = $this->_rhttp_build_query($v);
			}
		}

		sort($args, SORT_STRING);
		return http_build_query($args);
	}

	/**
	 * post_request
	 *
	 * @param  mixed $url
	 * @param  mixed $data
	 * @param string $host_ip 指定代理主机IP
	 * @return void
	 */
	public function post_request($url, $data, $host_ip = '') {
		if ($this->debug) {
			$this->message('post params'.print_r($data, true). "\n\n");
		}

		$snoopy = new snoopy();

		// 使用代理主机IP
		if ($host_ip) {
			if ('https' == substr($url, 0, 5)) {
				$snoopy->host_ip = $host_ip;
				$snoopy->port = 443;
			} else {
				$snoopy->proxy_host = $host_ip;
				$snoopy->proxy_port = 80;
				$snoopy->_isproxy = 1;
			}
		}
		$result = $snoopy->submit($url, $data);
		return array($snoopy->status, $snoopy->error, $result, $snoopy->results);
	}

	/**
	 * message
	 *
	 * @param  mixed $msg
	 * @return void
	 */
	public function message($msg) {
		echo $msg;
	}

}
