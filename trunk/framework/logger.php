<?php
/**
 * logger 日志类
 *
 * $Author$
 * $Id$
 */

class logger {

	/**
	 * 错误级别
	 */
	const LOGGER_LEVEL_TRACE = 1;
	const LOGGER_LEVEL_DEBUG = 2;
	const LOGGER_LEVEL_WARNING = 3;
	const LOGGER_LEVEL_ERROR = 4;
	const LOGGER_LEVEL_FATAL = 5;

	/**
	 * crlf
	 * 换行符
	 *
	 * @var string
	 */
	public static $crlf = PHP_EOL;

	/**
	 * level
	 * 当前错误级别
	 */
	public $level = null;

	/**
	 * writeln
	 *
	 * @param  mixed $fpath
	 * @param  mixed $message
	 * @access public
	 * @return void
	 */
	public static function writeln($fpath, $message) {

		$dir = config::get(startup_env::get('cfg_name').".logger.output_dir");
		$dir = rtrim($dir, '/').'/'.ltrim($fpath, '/');

		/** 自动创建不存在的目录 */
		$directory = dirname($dir);
		if (!is_dir($directory)) {
			mkdir($directory, 0777, true);
		}

		$fd = fopen($dir, "a+");
		if (!$fd) {
			error_log("Logger: Cannot open file ($dir)");
			return false;
		}

		fwrite($fd, "[".date('Y-m-d H:i:s')."]\t".$message.self::$crlf);
		fclose($fd);
	}

	/**
	 * debug
	 *
	 * @param  mixed $message
	 * @access public
	 * @return void
	 */
	public static function debug($message) {
		return logger::log($message, self::LOGGER_LEVEL_DEBUG);
	}

	/**
	 * warning
	 *
	 * @param  mixed $message
	 * @access public
	 * @return void
	 */
	public static function warning($message) {
		return logger::log($message, self::LOGGER_LEVEL_WARNING);
	}

	/**
	 * trace
	 *
	 * @param  mixed $message
	 * @access public
	 * @return void
	 */
	public static function trace($message) {
		return logger::log($message, self::LOGGER_LEVEL_TRACE);
	}

	/**
	 * fatal
	 *
	 * @param  mixed $message
	 * @access public
	 * @return void
	 */
	public static function fatal($message) {
		return logger::log($message, self::LOGGER_LEVEL_FATAL);
	}

	/**
	 * error
	 *
	 * @param  string $message
	 * @access public
	 * @return void
	 */
	public static function error($message) {
		return logger::log($message, self::LOGGER_LEVEL_ERROR);
	}

	/**
	 * log
	 *
	 * @param  mixed $message
	 * @param  mixed $level
	 * @access public
	 * @return void
	 */
	public static function log($message, $level) {

		$code = 0;
		/** 如果 $message 是异常对象 */
		if ($message instanceof Exception) {
			$code = $message->getCode();
			$message = $message->getMessage();
		}

		// 记录请求 url
		$message = startup_env::get('boardurl')."\n".$message;
		/** 错误信息回溯 */
		$backtrace = debug_backtrace();
		array_shift($backtrace);
		array_shift($backtrace);
		$traceinfo = array_shift($backtrace);
		$traceinfo['className'] = $traceinfo['class'];

		/** 读取类的 logger 配置 */
		$cfg_name = startup_env::get('cfg_name');
		$conf = config::get($cfg_name.".logger.".$traceinfo['className']);
		/** log 等级 */
		$log_level = self::get_instance()->level;
		if (!$log_level) {
			$conf['level'] = $conf["level"] ? $conf["level"] : config::get($cfg_name.".logger.level");
		} else {
			$conf['level'] = $log_level;
		}

		/** 如果没有配置, 则取默认主配置 */
		$conf['output'] = !empty($conf["output"]) ? $conf["output"] : config::get($cfg_name.".logger.output");
		$conf['output_dir'] = !empty($conf["output_dir"]) ? $conf["output_dir"] : config::get($cfg_name.".logger.output_dir");
		$conf['use_arg'] = !empty($conf["use_arg"]) ? $conf["use_arg"] : config::get($cfg_name.".logger.use_arg");

		switch($level) {
			case self::LOGGER_LEVEL_TRACE:
				$my_level = 'TRACE';
				break;
			case self::LOGGER_LEVEL_DEBUG:
				$my_level = 'DEBUG';
				break;
			case self::LOGGER_LEVEL_WARNING:
				$my_level = 'WARNING';
				break;
			case self::LOGGER_LEVEL_ERROR:
				$my_level = 'ERROR';
				break;
			case self::LOGGER_LEVEL_FATAL:
				$my_level = 'FATAL';
				break;
			default:
				$my_level = 'N/A';
		}

		/** 判断 logger 等级 */
		if ($conf['level'] <= $level) {
			if ($traceinfo['className']) {
				$out = sprintf ("[%s] [%s::%s %d] [%s] %s:%s".self::$crlf, date('Y-m-d H:i:s'), $traceinfo['className'], $traceinfo['function'],  $traceinfo['line'], $my_level, $code, $message);
				if ($conf['output'] == 'stdout') {
					echo $out;
				} else if ($conf['output'] == 'file') {
					if (php_sapi_name() == 'cli') {
						$outfile = $conf['output_dir']."/cli/".date('Y-m-d')."/{$traceinfo['className']}.log";
					} else {
						$outfile = $conf['output_dir']."/web/".date('Y-m-d')."/{$traceinfo['className']}";
						$arg1 = isset($_SERVER['argv']) && isset($_SERVER['argv'][1]) ? trim($_SERVER['argv'][1]) : '';

						/** fix: 浏览器有get参数时创建怪异的文件名 */
						if ($conf['use_arg'] && $arg1 && !$_SERVER['SERVER_SOFTWARE']) {
							$outfile .= "_{$arg1}.log";
						} else {
							$outfile .= ".log";
						}
					}

					$log_directory = dirname($outfile);
					if (!is_dir($log_directory)) {
						mkdir($log_directory, 0777, true);
					}

					if ($fd = @fopen($outfile, 'a+')) {
						fwrite($fd, $out);
						fclose($fd);
					} else {
						error_log("Logger: Cannot open file ($outfile)");
					}
				} else {
					error_log("Logger: unrecognized output type ({$conf['output']})");
				}
			} else if ($traceinfo['function']) {
				printf ("[%s] [%s %d] %s".self::$crlf, date('Y-m-d H:i:s'), $traceinfo['function'],  $traceinfo['line'],  $message);
			} else {
				printf ("[%s] [main] %s".self::$crlf, date('Y-m-d H:i:s'), $message);
			}
		}

		return $outfile;
	}

	/**
	 * set_level
	 *
	 * @param  mixed $level
	 * @access public
	 * @return void
	 */
	public function set_level($level) {
		$logger =& logger::get_instance();
		$logger->level = $level;
	}

	/**
	 * set_log
	 *
	 * @param  string $tmp
	 * @access public
	 * @return void
	 */
	public function set_log($tmp = '') {
		static $name;
		$tmp && $name = $tmp;
		return $name;
	}

	/**
	 * &get_instance
	 *
	 * @access public
	 * @return void
	 */
	public static function &get_instance() {
		static $instance;
		if (!$instance) {
			$instance = new logger();
		}

		return $instance;
	}
}
