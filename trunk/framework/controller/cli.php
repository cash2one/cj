<?php
/**
 * controller_cli
 *
 * $Author$
 * $Id$
 */

class controller_cli {

	/**
	 *  _instance
	 *  当前类的实例
	 *
	 *  @var object
	 */
	protected static $_instance = null;

	/**
	 * _controllers
	 *
	 * @var array
	 */
	protected $_controllers = array();

	/**
	 * _default_method
	 * 默认方法
	 *
	 * @var string
	 */
	protected $_default_method = 'main';

	/**
	 * _stdout
	 *
	 * @var stream
	 */
	protected $_stdout;

	/**
	 * _stdin
	 *
	 * @var stream
	 */
	protected $_stdin;

	/**
	 * _stderr
	 *
	 * @var stream
	 */
	protected $_stderr;

	/**
	 * _opts
	 *
	 * @var mixed
	 */
	protected $_opts;

	/**
	 * get_instance 获取一个实例
	 *
	 * @params array $controllers
	 * @return void
	 */
	public static function get_instance($controllers = array()) {

		if (null === self::$_instance) {
			self::$_instance = new self($controllers);
		}

		return self::$_instance;
	}

	/**
	 * __construct
	 * 构造函数
	 *
	 * @params array $controllers
	 * @return void
	 */
	public function __construct($controllers = array()) {

		$this->_init_iostream();
		$this->_opts = $this->_parse_parameters();

		if ($controllers && is_array($controllers)) {
			$this->_controllers = array_merge($this->_controllers, $controllers);
		}
	}

	/**
	 * _init_iostream
	 *
	 * @return void
	 */
	protected function _init_iostream() {

		if (!defined('STDOUT')) {
			$this->_stdout = fopen('php://stdout', 'w');
		} else {
			$this->_stdout = STDOUT;
		}

		if (!defined('STDIN')) {
			$this->_stdin = fopen('php://stdin', 'r');
		} else {
			$this->_stdin = STDIN;
		}

		if (!defined('STDERR')) {
			$this->_stderr = fopen('php://stderr', 'w');
		} else {
			$this->_stderr = STDERR;
		}
	}

	/**
	 * handle_request
	 * 执行
	 *
	 * @return mixed
	 */
	public function handle_request() {

		$controller_name = $this->get_controller_name();
		$method_name = $this->_default_method;

		if (!$controller_name) {
			throw new controller_exception('controller is not found.');
		}

		if (!class_exists($controller_name)) {
			throw new controller_exception('class '.$controller_name.' is not exists');
		}

		$object = new $controller_name($this->_opts);

		if (!method_exists($object, $method_name)) {
			throw new controller_exception($controller_name.'::'.$method_name.' is not exists');
		}

		call_user_func_array(array($object, $method_name), array());
	}

	/**
	 * get_controller_name
	 * 获得当前请求的程序
	 *
	 * @return void
	 */
	public function get_controller_name() {

		$alias = $this->_opts['n'];

		if (!$alias) {
			$this->_help();
			return false;
		}

		if (!$this->_controllers[$alias]) {
			return false;
		}

		return $this->_controllers[$alias];
	}

	/**
	 * add_controller
	 * 设置controller
	 *
	 * @param string $alias
	 * @param string $controller_name
	 * @return void
	 */
	public function add_controller($alias, $controller_name) {

		$this->_controllers[$alias] = $controller_name;
	}

	/**
	 * output
	 * 输出
	 *
	 * @param string $string
	 * @param boolean $newline
	 * @return void
	 */
	public function output($string, $newline = true) {

		if ($newline) {
			return fwrite($this->_stdout, $string."\n");
		} else {
			return fwrite($this->_stdout, $string);
		}
	}

	/**
	 * input
	 * 输入
	 *
	 * @param  string $string
	 * @return string
	 */
	public function input() {

		return fgets($this->_stdin);
	}

	/**
	 * error
	 * 输出错误
	 *
	 * @param  string $string
	 * @param boolean $newline
	 * @return void
	 */
	public function error($string, $newline = true) {

		fwrite($this->_stderr, "ERROR: ");

		if ($newline) {
			return fwrite($this->_stderr, $string."\n");
		} else {
			return fwrite($this->_stderr, $string);
		}
	}

	/**
	 * stop
	 * 停止
	 *
	 * @param  integer $status
	 * @return void
	 */
	public function stop($status = 0) {

		exit($status);
	}

	/**
	 * _help
	 * 打印帮助
	 *
	 * @return void
	 */
	protected function _help() {
		print <<<EOT
Usage: command.php [OPTION...]
 Input/Output format specification:
  -n name, indicates backend programme which will be invoked

To run a command, type 'php command.php -n name [args]'
To get help on a specific command, type 'php command.php'


EOT;

	}

	/**
	 * _parse_parameters
	 * 分析从命令行传入的参数
	 *
	 * 支持的类型：
	 * -e
	 * -e <value>
	 * --long-param
	 * --long-param=<value>
	 * --long-param <value>
	 * <value>
	 *
	 * @return array
	 */
	protected function _parse_parameters() {

		$result = array();
		$params = $GLOBALS['argv'];

		reset($params);

		while (list($tmp, $p) = each($params)) {
			if ($p{0} == '-') {
				$pname = substr($p, 1);
				$value = true;

				if ($pname{0} == '-') {

					/** long-opt (--<param>) */
					$pname = substr($pname, 1);

					/** 使用'='链接的long-opt: --<param>=<value> */
					if (strpos($p, '=') !== false) {
						list($pname, $value) = explode('=', substr($p, 2), 2);
					}
				}

				/** 下一个参数 */
				$next_parm = current($params);
				if ($value === true && $next_parm !== false && $next_parm{0} != '-') {
					list($tmp, $value) = each($params);
				}

				$result[$pname] = $value;
			} else {
				/** 不以'-'或'--'开头的opt */
				$result[] = $p;
			}
		}

		return $result;
	}

}
