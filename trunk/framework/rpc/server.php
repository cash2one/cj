<?php
/**
 * rpc_server
 * $Author$
 * $Id$
 */

abstract class rpc_server {

	/** 服务名称 */
	protected $_server_name;
	/** 环境变量 */
	protected $_env = array();

	/**
	 * __construct
	 *
	 * @param  string $server_name 根据些名子对应相应的配置文件
	 * @return void
	 */
	public function __construct($server_name) {

		$this->_server_name = $server_name;
	}

	/**
	 * get_parameters(子类实现)
	 * 获取客户端提交参数
	 *
	 * @return array
	 */
	abstract protected function _get_parameters();

	/**
	 * get_format(子类实现)
	 *
	 * @return string
	 */
	abstract protected function _get_format();

	/**
	 * authentication(子类实现)
	 * 签名检查
	 *
	 * @param array $request
	 * @return boolean
	 */
	abstract protected function _authentication($request);

	/**
	 * get_execute_data（子类实现）
	 * 返回执行参数array($class, $method, $args)
	 *
	 * @return array
	 */
	abstract protected function _get_execute_data();

	/**
	 * _execute
	 * 执行方法
	 *
	 * @param  string $method 执行方法名 eg:friend.get
	 * @param  array $args 方法参数, 在 $_POST['args'] 中
	 * @throw rpc_exception
	 * @return void
	 */
	protected function _execute($class_name, $method, $args) {

		$env = $this->get_env();
		$app_name = startup_env::get('app_name');
		$cfg_path = $app_name.'.rpc.'.strtolower($this->_server_name).'.'.strtolower($class_name);
		$clazz = config::get($cfg_path.'.classname');
		try {
			if (!$clazz) {
				$e = new rpc_exception('unknown method 4', 503);
				return $this->_output_error($e);
			}

			$args = $this->_build_params($class_name, $method, $args);
			$this->_before_exec();

			/** 将环境变量传递给实现类 */
			$obj = new $clazz($env);
			$result = call_user_func_array(array($obj, $method), $args);

			$this->_after_exec();
		} catch (rpc_exception $e) {
			logger::error($e);
			return $this->_output_error($e);
		} catch (Exception $e) {
			logger::error($e);
			$e = new rpc_exception('An unknown error occurred. Please resubmit the request.', 3);
			return $this->_output_error($e);
		}

		return $this->_output_result($result);
	}

	/**
	 * set_env
	 * 设置环境变量
	 *
	 * @param  string $key
	 * @param  mixed $value
	 * @return void
	 */
	public function set_env($key, $value = null) {
		if (is_array($key)) {
			foreach ($key as $k => $v) {
				$this->set_env($k, $v);
			}
		} else {
			$this->_env[$key] = $value;
		}
	}

	/**
	 * get_env
	 * 获取环境变量, 如果不指定参数返回全部环境变量
	 *
	 * @param  string $key
	 * @return mixed|array
	 */
	public function get_env($key = null) {
		if ($key == null) {
			return $this->_env;
		}

		if (!array_key_exists($key, $this->_env)) {
			return null;
		}

		return $this->_env[$key];
	}

	/**
	 * handle
	 * 执行（入口）
	 *
	 * @return void
	 */
	public function handle() {

		$request = $this->_get_parameters();
		if (!$this->_authentication($request)) {
			$e = new rpc_exception('Incorrect signature. ', 104);
			return $this->_output_error($e);
		}

		list($class, $method, $args) = $this->_get_execute_data($request);
		$this->_execute($class, $method, $args);
	}

	/**
	 * _before_exec (子类实现)
	 * 方法执行前
	 *
	 * @return void
	 */
	protected function _before_exec() {

		startup_env::set_benchmark('handle_started');
		return true;
	}

	/**
	 * _after_exec(子类实现)
	 * 方法执行后
	 *
	 * @return void
	 */
	protected function _after_exec() {

		return true;
	}

	/**
	 * _build_params
	 * 根据配置对参数数据验证
	 *
	 * @param  string $class_name 具体执行类名
	 * @param  string $method 具体执行类中的方法名
	 * @param  array $args 参数数组
	 * @throw  rpc_exception
	 * @return void
	 */
	protected function _build_params($class_name, $method, $args) {

		$key = startup_env::get('cfg_name').'.rpc.'.$this->_server_name.'.'.strtolower($class_name).'.'.strtolower($method).'.args';
		$allowed_args = config::get($key);

		/** 允许参数 */
		if (empty($allowed_args) || !is_array($allowed_args)) {
			return $args;
		}

		foreach ($allowed_args as $name => $def) {
			/** 检查必要参数 */
			if ($def['required'] && (!$args || !array_key_exists($name, $args) || !$args[$name])) {
				throw new rpc_exception("One of the parameters specified was missing or invalid. parameters name $name", 100);
			}

			if (!$args || !array_key_exists($name, $args)) {
				$args[$name] = $this->_default_value($def['type']);
				continue;
			}

			$value = $args[$name];
			/** 检查类型 */
			switch ($def['type']) {
				case 'int':
					$value = $this->_build_int_param($value);
					break;
				case 'string':
					break;
				case 'array':
					$value = $this->_build_array_param($value, $def);
					break;
			}

			if ($def['required'] && !$value) {
				throw new rpc_exception("One of the parameters specified was missing or invalid. parameters name $name", 100);
			}

			$args[$name] = $value;
		}

		return $args;
	}

	protected function _build_int_param($value) {
		return intval($value);
	}

	protected function _build_array_param($value, $def) {
		if ($def['element_type'] == 'int') {
			$value = array_filter($value, 'intval');
		}

		if (array_key_exists('max_size', $def)) {
			$value = array_slice($value, 0, $def['max_size']);
		}

		return $value;
	}

	/**
	 * _output_error
	 *
	 * @param  mixed $e
	 * @return void
	 */
	protected function _output_error($e) {

		$result = array();
		$result['errno'] = $e->getCode();
		$result['errmsg'] = $e->getMessage();
		$result['result'] = '';

		return $this->_output($result);
	}

	/**
	 * _output_result
	 *
	 * @param  mixed $res
	 * @return void
	 */
	protected function _output_result($res) {

		$result = array();
		$result['errno'] = 0;
		$result['errmsg'] = 'OK';
		$result['result'] = $res;

		return $this->_output($result);
	}

	/**
	 * _output
	 *
	 * @param  mixed $result
	 * @return void
	 */
	protected function _output($result) {
		$format = strtolower($this->_get_format());
		switch ($format) {
			case 'json':
				$output = json_encode($result);
				break;
			case 'xml':
				$output = vxml::array2xml($result);
				break;
			case 'php':
			default:
				$output = serialize($result);
				break;
		}

		$this->_log($output);
		echo $output;
		exit;
	}

	/**
	 * _default_value
	 *
	 * @param  mixed $type
	 * @return void
	 */
	protected function _default_value($type) {
		$data = array(
			'string' => '',
			'array' => array(),
			'enum' => '',
			'int' => 0
		);

		return $data[$type];
	}

	/**
	 * log
	 * 记录日志
	 *
	 * @return void
	 */
	protected function _log($output) {

		$req = controller_request::get_instance();
		$postx = $req->postx();
		$method = $postx['method'];

		$log_info = array(
			'app_key' => $postx['api_key'],
			'ts' => $postx['ts'],
			'sig' => $postx['sig'],
			'format' => $postx['format'],
		);

		if (is_array($postx['args'])) {
			$log_info['args'] = http_build_query($postx['args']);
		}

		if (!$method) {
			$method = 'unknown';
		}

		/** 计算消耗时间 */
		$end_time = microtime(true);
		$benchmark = startup_env::get('benchmark');

		foreach ($benchmark as $k => $v) {
			if (strpos($k, '.ended') !== false) {
				continue;
			}

			$tmp_end_time = $end_time;
			if ($benchmark[$k.'.ended']) {
				$tmp_end_time = $benchmark[$k.'.ended'];
				unset($benchmark[$k.'.ended']);
			}

			$benchmark[$k] = $tmp_end_time - $v;
		}

		$log_info['benchmark'] = json_encode($benchmark);
		/** 记录所有输出信息 */
		$log_info['output'] = $output;

		if (defined('SERVER_LOG')) {
			logger::writeln('rpc_server/'.date('Y-m-d').'/'. $method.'.log', join("\t", $log_info));
		}
	}
}
