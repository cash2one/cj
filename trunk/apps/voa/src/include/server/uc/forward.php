<?php
/**
 * ucenter_server_forward
 * 接口服务基类
 *
 * $Author$
 * $Id$
 */

class voa_server_uc_forward extends rpc_server {

	/**
	 * _format
	 * 返回数据格式
	 *
	 * @var string
	 */
	protected $_format = 'JSON';

	/**
	 * _version
	 * 接口版本号
	 *
	 * @var string
	 */
	protected $_version = '1.0';

	/**
	 * _config_path
	 * 配置文件路径
	 *
	 * @var string
	 */
	protected $_config_path = '';

	/**
	 * _request
	 * 请求参数
	 *
	 * @var array
	 */
	protected $_request = array();


	/**
	 * __construct
	 * 接口服务构造方法
	 *
	 * @param  mixed $serverName
	 * @return void
	 */
	public function __construct($server_name) {

		parent::__construct($server_name);

		/** 记录请求日志 */
		$this->_log();
	}

	/**
	 * _get_parameters
	 * 获取客户端提交参数
	 *
	 * @return array
	 */
	protected function _get_parameters() {
		if (!$this->_request) {
			$req = controller_request::get_instance();
			if ($req->post('data')) {
				$tea = new crypt_xxtea(config::get(startup_env::get('app_name').'.rpc.server.auth_key'));
				/**
				 * 必须有的参数
				 * method 调用方法
				 * ts 时间戳
				 * args 方法的参数
				 * format 返回数据格式
				 */
				$data = $tea->decrypt($req->post('data'));
				$this->_request = (array)unserialize($data);
			}

			if ($this->_request['format']) {
				$this->_format = strtolower($this->_request['format']);
			}

			if ($this->_request['ver']) {
				$this->_version = floatval($this->_request['ver']);
			}

			/** 配置路径 */
			$app_name = startup_env::get('app_name');
			$this->_config_path = $app_name.'.rpc.'.strtolower($this->_server_name);
		}

		return $this->_request;
	}

	/**
	 * _get_format
	 * 获取数据格式
	 *
	 * @return string
	 */
	protected function _get_format() {

		return $this->_format;
	}

	/**
	 * authentication
	 * 签名检查
	 *
	 * @param  mixed $request
	 * @return void
	 */
	protected function _authentication($request) {
		return true;
	}

	/**
	 * _get_execute_data
	 * 返回执行参数array($class, $method, $args)
	 *
	 * @return void
	 */
	protected function _get_execute_data() {

		$request = $this->_get_parameters();
		list($class, $method) = explode('.', $request['method']);
		$args = $request['args'];
		return array($class, $method, $args);
	}

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
		$clazz = config::get($this->_config_path.'.'.strtolower($class_name).'.classname');
		$methods = config::get($this->_config_path.'.'.strtolower($class_name).'.methods' );
		$cache = config::get($this->_config_path.'.'.strtolower($class_name).'.cache');
		try {
			/** 判断调用方法是否存在 */
			if (!$clazz || !in_array($method, $methods)) {
				$e = new rpc_exception('unknown method 2', 501);
				return $this->_output_error($e);
			}

			$args = $this->_build_params($class_name, $method, $args);
			/** 检查 cache */
			$cache_key = null;
			if ($cache && $cache['ttl'] > 0) {
				/** 计算缓存键值 */
				/**$cache_key = md5(startup_env::get('wbs_uid').serialize($args));
				try {
					$serv =& service::factory('vchangyi_s_cache_open', array('pluginid' => 0));
					$cache = $serv->get($cache_key);
					if (null !== $cache) {
						$result['result'] = $cache;
						return $result;
					}
				} catch (Exception $e) {}*/
			}

			$this->_before_exec();

			/** 将环境变量传递给实现类 */
			$obj = new $clazz($env);
			$result = call_user_func(array($obj, $method), $args);

			/** 写入 cache */
			if ($cache_key) {
				/**try {
					$serv->set($cache_key, $result['result'], $cache['ttl']);
				} catch (Exception $e) {}*/
			}

			$this->_after_exec();
		} catch (rpc_exception $e) {
			logger::error($e);
			return $this->_output_error($e);
		} catch (Exception $e) {
			logger::error($e);
			$e = new rpc_exception('An unknown error occurred. Please resubmit the request. ', 1);
			return $this->_output_error($e);
		}

		return $result;
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
			$e = new rpc_exception('Incorrect signature. ', 4);
			return $this->_output_error($e);
		}

		/** 处理开始时间 */
		$time_start = microtime(true);
		$cm = rstrtolower($request['method']);
		list($c_n, $m_n) = explode('.', rstrtolower($cm));
		$result = $this->_execute($c_n, $m_n, $request['args']);

		/** 处理结束时间 */
		$time_end = microtime(true);
		$this->__log_timeout(intval(1000 * ($time_end - $time_start)), $request);

		return $this->_output_result($result);
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
				// 输出json头 (apache做gzip压缩，暂无不输出json的头信息)
				// header('Content-type:application/json');
				$output = json_encode($result);
				break;
			case 'xml':

				break;
			case 'php':
			default:
				$output = serialize($result);
				break;
		}

		/** 输出日志 */
		$this->_log($output);
		echo $output;
		exit;
	}

	/**
	 * __version_compare
	 * 版本比较
	 *
	 * @param  string $a
	 * @param  strign $b
	 * @return int
	 */
	protected function __version_compare($a, $b) {

		$a = explode('.', rtrim($a, '.0'));
		$b = explode('.', rtrim($b, '.0'));
		foreach ($a as $depth => $a_val) {
			if (isset($b[$depth])) {
				if ($a_val > $b[$depth]) return 1;
				else if ($a_val < $b[$depth]) return -1;
			} else {
				return 1;
			}
		}

		return (count($a) < count($b)) ? -1 : 0;
	}

	/**
	 * log
	 * 记录日志
	 *
	 * @return void
	 */
	protected function _log() {

		$request = $this->_get_parameters();
		if (!$request['method']) {
			$request['method'] = 'unknown';
		}

		if (is_array($request['args'])) {
			$request['args'] = urldecode(http_build_query($request['args']));
		}

		logger::writeln('server/'.$request['method'].'.'.date('Y-m-d').'.log', join("\t", $request));
	}

	/**
	 * __log_timeout
	 * 记录超时日志
	 *
	 * @param int $timeout
	 * @param array $request
	 *
	 * @return void
	 */
	private function __log_timeout($timeout, $request) {

		/** 检查配置 */
		$config = config::get(startup_env::get('app_name').'.rpc.server.timeout_logs');
		$ranges = $config['ranges'];
		if (!$config['enabled'] || !is_array($ranges)) {
			return;
		}

		/** 如果处理时间在需要记录的时间区间内，记录日志 */
		foreach ($ranges as $range) {
			if ($timeout >= $range[0] && $timeout <= $range[1]) {
				$name = 'server_timeout/'.$range[0].'_'.$range[1].'-'.date('Ym').'.log';
				logger::writeln($name, implode("\t", array($timeout.'ms', http_build_query($request))));
			}
		}
	}
}
