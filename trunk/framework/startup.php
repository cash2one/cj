<?php
/**
 * 启动
 * $Author$
 * $Id$
 */

class startup {

	/**
	 * _options
	 * 当前应用配置
	 *
	 * @var array
	 */
	protected $_options = array();

	/**
	 * _instance
	 *
	 * @var object
	 */
	static $_instance = null;

	/**
	 * factory
	 * 获得一个实例
	 *
	 * @params array $options 选项
	 *  + interface 运行环境：web/cli/auto
	 *  + session 是否开启session
	 *  + autoload 自定义自动加载
	 *  + exception_handler 自定义异常回调
	 * @return object
	 */
	public static function &factory($options = array()) {

		if (!(self::$_instance instanceof self)) {

			if ($options['interface'] == 'web') {
				$interface = 'web';
			} else if ($options['interface'] == 'cli') {
				$interface = 'cli';
			} else if ($options['interface'] == 'runtime') {
				$interface = 'runtime';
			} else {
				if (php_sapi_name() == 'cli') {
					$interface = 'cli';
				} else {
					$interface = 'web';
				}
			}

			require_once(dirname(__FILE__).'/startup/'.$interface.'.php');
			$classname = 'startup_'.$interface;
			self::$_instance = new $classname($options);
		}

		return self::$_instance;
	}

	/**
	 * __construct
	 * 构造函数
	 *
	 * @param  array $options
	 * @return void
	 */
	protected function __construct($options = array()) {

		/** 时区设定为标准时间(格林威治时间) */
		date_default_timezone_set('UTC');

		$this->set_option($options);
		$this->_regist_autoload();
		$this->_regist_exception_handler();
		$this->_regist_functions();
		// 设置配置前缀
		startup_env::set('cfg_name', $this->get_option('childapp') ? $this->get_option('childapp') : basename(APP_PATH));

		/** 使用Benchmark */
		benchmark::mark('total_execution_time_start');

		if (!empty($options['profiler'])) {
			startup_env::set('profiler', true);
		}
	}

	/**
	 * __destruct
	 * 析构函数，用于在程序末尾执行
	 *
	 * @return void
	 */
	public function __destruct() {
	}

	/**
	 * run
	 * 执行
	 *
	 * @return void
	 */
	public function run() {

		throw new startup_exception('Bad invoke');
	}

	/**
	 * set_option
	 * 设置配置
	 *
	 * @param  string $key
	 * @param  mixed $value
	 * @return void
	 */
	public function set_option($key, $value = null) {

		if (!$key) {
			return false;
		}

		if (is_array($key)) {
			$this->_options = array_merge($this->_options, $key);
		} else {
			$this->_options[$key] = $value;
		}

		if ($key == 'profiler') {
			startup_env::set('profiler', true);
		}

		return true;
	}

	/**
	 * get_option
	 * 获取配置
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function get_option($key = null) {

		if (!$key) {
			return $this->_options;
		}

		return isset($this->_options[$key]) ? $this->_options[$key] : '';
	}

	/**
	 * _regist_autoload
	 * 注册自动加载函数
	 *
	 * @return void
	 */
	protected function _regist_autoload() {

		if (function_exists('__autoload')) {
			return false;
		}

		/** 系统函数 */
		spl_autoload_register(array('startup', '_autoload'));

		/** 自定义函数 */
		$autoload = $this->get_option('autoload');
		if ($autoload) {
			spl_autoload_register($autoload);
		}
	}

	/**
	 * _autoload
	 * 自动加载
	 *
	 * @param  string $classname 类名
	 * @return void
	 */
	protected static function _autoload($classname) {

		/** 此列表可根据 bin/createLibarayIndex.php 生成 */
		$libraries = array(
			'cache_abstract' => 'cache/abstract.php',
			'cache_memcached' => 'cache/memcached.php',
			'cache_memory' => 'cache/memory.php',
			'cache_redis' => 'cache/redis.php',
			'config_exception' => 'config/exception.php',
			'controller_route_abstract' => 'controller/route/abstract.php',
			'controller_route_default' => 'controller/route/default.php',
			'controller_route_exception' => 'controller/route/exception.php',
			'controller_route_route' => 'controller/route/route.php',
			'controller_cli' => 'controller/cli.php',
			'controller_exception' => 'controller/exception.php',
			'controller_front' => 'controller/front.php',
			'controller_request' => 'controller/request.php',
			'controller_response' => 'controller/response.php',
			'controller_route' => 'controller/route.php',
			'controller_runtime' => 'controller/runtime.php',
			'crypt_xxtea' => 'crypt/xxtea.php',
			'dao_exception' => 'dao/exception.php',
			'dao_mysql' => 'dao/mysql.php',
			'dao_pdo' => 'dao/pdo.php',
			'db_shard_exception' => 'db/shard/exception.php',
			'db_exception' => 'db/exception.php',
			'db_help' => 'db/help.php',
			'db_pdo' => 'db/pdo.php',
			'db_safecheck' => 'db/safecheck.php',
			'db_shard' => 'db/shard.php',
			'db_table' => 'db/table.php',
			'dnspod' => 'lib/dnspod.php',
			'FirePHP' => 'lib/FirePHPCore/FirePHP.class.php',
			'help_exception' => 'help/exception.php',
			'http_request_exception' => 'http/request/exception.php',
			'http_request' => 'http/request.php',
			'http_response' => 'http/response.php',
			'rpc_client' => 'rpc/client.php',
			'rpc_exception' => 'rpc/exception.php',
			'rpc_server' => 'rpc/server.php',
			'service_exception' => 'service/exception.php',
			'stratup_cli' => 'startup/cli.php',
			'startup_env' => 'startup/env.php',
			'startup_exception' => 'startup/exception.php',
			'startup_runtime' => 'startup/runtime.php',
			'startup_web' => 'startup/web.php',
			'vo_exception' => 'vo/exception.php',
			'vxml_parse' => 'vxml/parse.php',
			'bbcode' => 'bbcode.php',
			'benchmark' => 'benchmark.php',
			'cache' => 'cache.php',
			'config' => 'config.php',
			'controller' => 'controller.php',
			'dao' => 'dao.php',
			'db' => 'db.php',
			'excel' => 'excel.php',
			'language' => 'language.php',
			'logger' => 'logger.php',
			'mailcloud' => 'lib/mailcloud.php',
			'msgpush' => 'lib/msgpush.php',
			'mtc' => 'mtc.php',
			'pager' => 'pager.php',
			'pdodb' => 'pdodb.php',
			'view' => 'view.php',
			'pinyin' => 'lib/pinyin/pinyin.php',
			'profiler' => 'profiler.php',
			'qywx_callback' => 'lib/tx/QywxCallback.php',
			'service' => 'service.php',
			'session' => 'session.php',
			'sms' => 'lib/sms.php',
			'snoopy' => 'snoopy.php',
			'startup' => 'startup.php',
			'thumb' => 'thumb.php',
			'ueditor' => 'ueditor.php',
			'uda' => 'uda.php',
			'uda_exception' => 'uda/exception.php',
			'upload' => 'upload.php',
			'validator' => 'validator.php',
			'view' => 'view.php',
			'vo' => 'vo.php',
			'vxml' => 'vxml.php',
			'XingeApp' => 'lib/tx/XingeApp.php',
			'orm' => 'orm.php',
			'db_mo' => 'db/mo.php',
			'map' => 'lib/map.php',
			'ip2address' => 'lib/ip2address.php',
			// weopen
			'WXBizMsgCrypt' => 'lib/weopen/wxBizMsgCrypt.php',
			// wepay
			'wepay_exception' => 'lib/wepay/SDKRuntimeException.php',
			'Notify_pub' => 'lib/wepay/WxPayPubHelper.php',
			'JsApi_pub' => 'lib/wepay/WxPayPubHelper.php',
			'UnifiedOrder_pub' => 'lib/wepay/WxPayPubHelper.php',
			'DownloadBill_pub' => 'lib/wepay/WxPayPubHelper.php',
			'OrderQuery_pub' => 'lib/wepay/WxPayPubHelper.php',
			'RefundQuery_pub' => 'lib/wepay/WxPayPubHelper.php',
			'Refund_pub' => 'lib/wepay/WxPayPubHelper.php',
			'WxPayConf_pub' => 'lib/wepay/WxPay.pub.config.php',
			'seccode' => 'lib/seccode.php'
		);

		/** 加载类库中的类 */
		if (array_key_exists($classname, $libraries)) {
			$file = ROOT_PATH.'/framework/'.$libraries[$classname];
			return require_once($file);
		}

		/** 加载APP下的类 */
		$items = explode('_', $classname);

		$startup = &startup::factory();
		if ($startup->get_option('childapp')) { // 判断是否子项目文件
			$file = APP_PATH.'/src/'.join('/', array_slice($items, 1)).'.php';
			if (!is_file($file)) {
				$file = APP_PATH.'/'.join('/', array_slice($items, 1)).'.php';
			}
		} elseif ($items[0] == basename(APP_PATH)) {
			$file = APP_PATH.'/src/include/'.join('/', array_slice($items, 1)).'.php';
		} else {
			$file = ROOT_PATH.'/apps/'.$items[0].'/src/include/'.join('/', array_slice($items, 1)).'.php';
		}

		if (is_file($file)) {
			include_once($file);
		}

		//$result = include_once($file);
	}

	/**
	 * _regist_exception_handler
	 * 注册异常回调
	 *
	 * @return void
	 */
	protected function _regist_exception_handler() {

		$exception_handler = $this->get_option('exception_handler');

		if ($exception_handler) {
			set_exception_handler($exception_handler);
		} else {
			set_exception_handler(array('startup', 'exception_handler'));
		}
	}

	/**
	 * exception_handler
	 * 异常回调函数
	 *
	 * @param  object $exception Exception对象
	 * @return void
	 */
	public static function exception_handler($exception) {

		$msg = date('[Y-m-d H:i:s]')."Uncaught exception: ".$exception->getMessage()."\n";
		file_put_contents(APP_PATH.'/tmp/exception/exception_'.date('Y_m-d').'.log', $msg, FILE_APPEND);
	}

	/**
	 * _regist_functions
	 * 注册自定义函数
	 *
	 * @return boolean
	 */
	protected function _regist_functions() {

		$path = ROOT_PATH.'/framework/function';
		if (!is_dir($path)) {
			return false;
		}

		$files = glob($path.'/*.php');
		foreach ($files as $file) {
			require_once($file);
		}

		return true;
	}

	/**
	 * _regist_time
	 * 注册时间相关
	 *
	 * @return void
	 */
	protected function _regist_time() {

		startup_env::set('starttime', microtime(true));
	}

}
