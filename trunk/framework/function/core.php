<?php
/**
 * 全局函数
 *
 * $Author$
 * $Id$
 */

// 获取页面的唯一标识
function url_uniqueid() {

	static $uniqueid = '';
	// 如果已经生成
	if (! empty($uniqueid)) {
		return $uniqueid;
	}

	$str = startup_env::get('boardurl') . random(16) . startup_env::get('timestamp');
	$uniqueid = md5($str);
	return $uniqueid;
}

/**
 * 记录SQL日志
 * @param string $sql SQL 语句
 * @param array $params 参数数组
 */
function sql_record($sql, $params = array()) {

	// 临时去除sql日志, by zhuxun
	return true;
	// 如果是自身, 则不记录
	if (preg_match("/common_sqlrecord/i", $sql)) {
		return true;
	}

	$front = controller_front::get_instance();
	$route = $front->get_route();
	$class = '';
	if (!method_exists($route, 'get_module')) {
		return true;
	}

	// 根据控制器链接不同的库
	if (in_array($route->get_module(), array('frontend', 'api', 'admincp'))) {
		$class = 'voa_s_oa_common_sqlrecord';
	} else if ('cyadmin' == $route->get_module()) {
		$class = 'voa_s_cyadmin_common_sqlrecord';
	} else if ('uc' == $route->get_module()) {
		$class = 'voa_s_uc_common_sqlrecord';
	}

	// 如果类名为空, 则不记录
	if (empty($class) || !class_exists($class)) {
		return true;
	}

	try {
		// 数据入库
		$serv = &service::factory($class);
		$data = array(
			'uid' => (int)startup_env::get('wbs_uid'),
			'uniqueid' => url_uniqueid(),
			'datetime' => rgmdate(startup_env::get('timestamp'), 'Y-m-d H:i:s'),
			'url' => startup_env::get('boardurl'),
			'get' => var_export($_GET, true),
			'post' => var_export($_POST, true),
			'sql' => $sql.';'.var_export($params, true)
		);
		$serv->insert($data);
	} catch (Exception $e) {
		logger::error($e);
	}
}

/**
 * 把字串中字母转成小写(重写 rstrtolower);
 * @param string $str 字串;
 * @return 转换后的字串;
 */
function rstrtolower($str) {
	return strtr($str, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz");
}


function rbase64_encode($string) {
	$data = base64_encode($string);
	$data = str_replace(array('+', '/', '='),array('-', '_', ''), $data);
	return $data;
}


function rbase64_decode($string) {
	$data = str_replace(array('-', '_'), array('+', '/'),$string);
	$mod4 = strlen($data) % 4;
	if ($mod4) {
		$data .= substr('====', $mod4);
	}

	return base64_decode($data);
}


/**
 * 替换preg_replace 函数，兼容php 5.5
 * @param mixed $pattern
 * @param mixed $replacement
 * 			//3 array(array('callback' => 'function', 'params' => array(1,2)), array('callback' => 'function', 'params' => array(1,2)) );
 *			//2 array('callback' => 'function', 'params' => array(1,2))             array('callback' => array($obj, 'function'), 'params' => array(1,2));
 *			//1 array('num \\1', 'num \\2')
 * @param string $subject
 * @return mixed|Ambigous <string, unknown, mixed>
 */

function rpreg_replace($pattern, $replacement, $subject) {
	if (is_array($pattern)) {
		foreach ($pattern as $loop=>$pat) {
			$rep = $replacement;
			if (is_string($replacement)) {
				$rep = $replacement;
			} elseif (!empty($replacement['callback'])) {
				$rep = $replacement;
			} elseif (!empty($replacement[$loop]['callback']) || is_string($replacement[$loop])) {
				$rep = $replacement[$loop];
			}
			$subject = rpreg_replace($pat, $rep, $subject);
		}
		return $subject;
	} else {
		return preg_replace_callback($pattern, function ($matches) use ($replacement){
			$ret = '';
			if (is_array($replacement)) {
				foreach ($replacement['params'] as $pk => $pv) {
					if (is_numeric($pv)) {
						$replacement['params'][$pk] = $matches[$pv-1];
					}
				}
				$ret = call_user_func_array($replacement['callback'], $replacement['params']);
			} elseif (is_string($replacement)) {
				$ret = $replacement;
				foreach ($matches as $k=>$v) {
					$num = $k+1;
					$ret = str_replace(array("\\{$num}", "\${$num}", "\$\{{$num}\}"), $v, $ret);
				}
			}
			return $ret;
		}, $subject);
	}
}

/**
 * 把字串中字母转成大写(重写 rstrtoupper);
 *
 * @param string $str 字串;
 * @return 转换后的字串;
 */
function rstrtoupper($str) {
	return strtr($str, "abcdefghijklmnopqrstuvwxyz", "ABCDEFGHIJKLMNOPQRSTUVWXYZ");
}

/**
 * 把数据转成数字
 * @param * $int 传入的数据
 * @param bool $allowarray 是否允许数组
 */
function rintval($int, $allowarray = false) {
	$ret = is_scalar($int) ? intval($int) : 0;
	if ($int == $ret || (!$allowarray && is_array($int))) {
		return $ret;
	}

	if ($allowarray && is_array($int)) {
		foreach ($int AS &$v) {
			$v = rintval($v, true);
		}

		return $int;
	} elseif ($int <= 0xffffffff) {
		$l = strlen($int);
		$m =substr($int, 0, 1) == '-' ? 1 : 0;
		if (($l - $m) === strspn($int,'0987654321', $m)) {
			return $int;
		}
	}

	return $ret;
}

/**
 * 创建目录
 * @param string $dir 目录
 * @param string $mode 权限字串
 * @param boolean $makeindex 是否创建默认索引文件
 */
function rmkdir($dir, $mode = 0777, $makeindex = TRUE) {
	if (!is_dir($dir)) {
		rmkdir(dirname($dir), $mode, $makeindex);
		@mkdir($dir, $mode);
		if (!empty($makeindex)) {
			@touch($dir.'/index.html');
			@chmod($dir.'/index.html', 0777);
		}
	}

	return true;
}

function rstripslashes($string) {
	if (empty($string) || is_numeric($string)) {
		return $string;
	}

	if (is_array($string)) {
		foreach($string AS $key => $val) {
			$string[$key] = rstripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}

	return $string;
}

function raddslashes($string, $force = 1) {
	if(is_array($string)) {
		$keys = array_keys($string);
		foreach($keys as $key) {
			$val = $string[$key];
			unset($string[$key]);
			$string[addslashes($key)] = raddslashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}

	return $string;
}

function rhtmlspecialchars($string, $flags = null) {
	if (is_array($string)) {
		foreach ($string AS $key => $val) {
			$string[$key] = rhtmlspecialchars($val, $flags);
		}
	} else {
		if (is_numeric($string)) {
			return $string;
		}

		if ($flags === null) {
			$string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
			if (strpos($string, '&amp;#') !== false) {
				$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
			}
		} else {
			if (PHP_VERSION < '5.4.0') {
				$string = htmlspecialchars($string, $flags);
			} else {
				if (strtolower(CHARSET) == 'utf-8') {
					$charset = 'UTF-8';
				} else {
					$charset = 'ISO-8859-1';
				}

				$string = htmlspecialchars($string, $flags, $charset);
			}
		}
	}

	return $string;
}

/**
 * 生成随机字串
 * @param int $length 随机字串长度
 * @param int $numeric 是否为数字字串
 */
function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.rstrtoupper($seed));
	if ($numeric) {
		$hash = '';
	} else {
		$hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
		$length --;
	}

	$max = strlen($seed) - 1;
	for ($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}

	return $hash;
}

/**
 * 解析对应的语言;
 * @param string $arg 语言相关;
 *  + key 键值
 *  + file 语言文件
 * @param class $smarty smarty 实例;
 * @return 返回对应的文字;
 */
function parse_lang($arg, $smarty = null) {
	$file = 'message';
	if (is_array($arg)) {
		if (isset($arg['file']) && !empty($arg['file'])) {
			$file = $arg['file'];
		}

		$key = (string)$arg['key'];
	} else {
		$key = (string)$arg;
	}

	language::load_lang($file);
	$result = language::get($key);

	return empty($result) ? $key : $result;
}

/**
 * 把时间戳转换成指定格式
 * @param int $timestamp 时间戳
 * @param string $format 格式
 * @param float $timeoffset 时区
 * @param string $uformat
 */
function rgmdate($timestamp, $format = 'zx', $timeoffset = '9999', $uformat = '') {
	static $dformat, $tformat, $dtformat, $offset, $lang;
	if ($dformat === null) {
		$dformat = config::get(startup_env::get('app_name').'.dateformat');
		$tformat = config::get(startup_env::get('app_name').'.timeformat');
		$dtformat = $dformat.' '.$tformat;
		$lang = language::get('date');
		$offset = config::get(startup_env::get('app_name').'.timeoffset');
		if (!$offset) {
			$offset = 8;
		}
	}

	$format = trim($format);
	$timeoffset = $timeoffset == 9999 ? $offset : $timeoffset;
	$timestamp += $timeoffset * 3600;
	$format = (empty($format) || $format == 'zx') ? $dtformat : ($format == 'z' ? $dformat : ($format == 'x' ? $tformat : $format));
	if ($format == 'u') {
		$todaytimestamp = startup_env::get('timestamp') - (startup_env::get('timestamp') + $timeoffset * 3600) % 86400 + $timeoffset * 3600;
		$s = gmdate(!$uformat ? $dtformat : $uformat, $timestamp);
		$time = startup_env::get('timestamp') + $timeoffset * 3600 - $timestamp;
		if ($timestamp >= $todaytimestamp) {
			if ($time > 3600) {
				return intval($time / 3600).'&nbsp;'.$lang['hour'].$lang['before'];
			} elseif ($time > 1800) {
				return $lang['half'].$lang['hour'].$lang['before'];
			} elseif ($time > 60) {
				return intval($time / 60).'&nbsp;'.$lang['min'].$lang['before'];
			} elseif ($time > 0) {
				return $time.'&nbsp;'.$lang['sec'].$lang['before'];
			} elseif ($time == 0) {
				return $lang['now'];
			} else {
				return $s;
			}
		} elseif (($days = intval(($todaytimestamp - $timestamp) / 86400)) >= 0 && $days < 7) {
			if ($days == 0) {
				return $lang['yday'].'&nbsp;'.gmdate($tformat, $timestamp);
			} elseif ($days == 1) {
				return $lang['byday'].'&nbsp;'.gmdate($tformat, $timestamp);
			} else {
				return ($days + 1).'&nbsp;'.$lang['day'].$lang['before'];
			}
		} else {
			return $s;
		}
	} else {
		$format = trim($format);
		if (!$format) {
			$format = 'Y-m-d H:i';
		}

		return gmdate($format, $timestamp);
	}
}

function rimplode($array) {
	if (!empty($array)) {
		$array = array_map('addslashes', $array);
		return "'".implode("','", is_array($array) ? $array : array($array))."'";
	} else {
		return 0;
	}
}

/**
 * 判断文件或是路径是否可写;
 * @param string $filepath	文件路径;
 * @return 可写则返回 true;
 */
function ris_writable($filepath) {
	$unlink = false;
	'/' == substr($filepath, -1) && $filepath = substr($filepath, 0, -1);
	if(is_dir($filepath)) {
		$unlink = true;
		mt_srand((double)microtime()*1000000);
		$filepath = $filepath.'/cms_'.uniqid(mt_rand()).'.tmp';
	}

	$fp = @fopen($filepath, 'ab');
	if(false === $fp) {
		return false;
	}

	fclose($fp);
	if($unlink) {
		@unlink($filepath);
	}

	return true;
}

/**
 * 把变量中的数据格式化成一个字符串
 * @param array $array	变量;
 * @param int $level	当前解析的是第几层(缩进的层数);
 * @return 返回一个变量的字符串;
 */
function rvar_export($array, $level = 0) {
	if(!is_array($array)) {
		return "'".$array."'";
	}
	if(is_array($array) && function_exists('var_export')) {
		return var_export($array, true);
	}

	$space = '';
	for($i = 0; $i <= $level; $i++) {
		$space .= "\t";
	}
	$evaluate = "Array\n$space(\n";
	$comma = $space;
	foreach($array as $key => $val) {
		$key = is_string($key) ? '\''.addcslashes($key, '\'\\').'\'' : $key;
		$val = !is_array($val) && (!preg_match("/^\-?[1-9]\d*$/", $val) || 12 < strlen($val)) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
		if(is_array($val)) {
			$evaluate .= "$comma$key => ".rvar_export($val, $level + 1);
		} else {
			$evaluate .= "$comma$key => $val";
		}
		$comma = ",\n$space";
	}
	$evaluate .= "\n$space)";
	return $evaluate;
}

/**
 * 获取来源页面地址;
 * @param string $default	如果没有来源页面信息, 则返回该值;
 * @return 返回来源页面地址;
 */
function get_referer($default = '/') {
	$front = controller_front::get_instance();
	$request = $front->get_request();
	$referer = $request->get('referer');
	if (!$referer) {
		$referer = $request->post('referer');
	}
	if(empty($referer) && isset($_SERVER['HTTP_REFERER'])) {
		$referer = preg_replace('/([\?&])((sid\=[a-z0-9]{6})(&|$))/i', '\\1', $_SERVER['HTTP_REFERER']);
		$referer = substr($referer, -1) == '?' ? substr($referer, 0, -1) : $referer;
	} else {
		$referer = rhtmlspecialchars($referer);
	}

	if(strpos($referer, 'login.php')) {
		$referer = $default;
	}

	return $referer;
}

/**
 * 读取指定文件;
 * @param string $filename	文件路径;
 * @param string $method	文件的读取方式;
 * @return 返回读取的数据;
 */
function rfread($filename, $method = 'rb') {
	check_filepath($filename);
	$filedata = '';
	if($handle = @fopen($filename, $method)) {
		flock($handle, LOCK_SH);
		$filedata = @fread($handle, filesize($filename));
		fclose($handle);
	}
	return $filedata;
}

/**
 * 把数据写入文件;
 * @param string $filename	文件名;
 * @param string $data	需要写入的数据;
 * @param string $method	写入的方式;
 * @param boolean $iflock	写时是否加文件锁;
 * @param boolean $check	是否检测文件路径的合法性;
 * @param boolean $chmod	是否改变文件的属性;
 */
function rfwrite($filename, $data, $method = 'rb+', $iflock = 1, $check = 1, $chmod = 1) {
	$check && check_filepath($filename);
	if(false == ris_writable($filename)) {
		logger::error('Can not write to cache files, please check directory '.$filename.' .');
	}

	touch($filename);
	$handle = fopen($filename, $method);
	$iflock && flock($handle, LOCK_EX);
	fwrite($handle, $data);
	$method == 'rb+' && ftruncate($handle, strlen($data));
	fclose($handle);
	$chmod && @chmod($filename, 0777);
}

/**
 * 格式化文件的大小;
 * @param int $filesize	文件的大小(单位：bytes);
 * @return 返回格式化后的字串；
 */
function size_count($filesize) {
	if(1073741824 <= $filesize) {
		$filesize = (round($filesize / 1073741824 * 100) / 100).' G';
	} elseif(1048576 <= $filesize) {
		$filesize = (round($filesize / 1048576 * 100) / 100).' M';
	} elseif(1024 <= $filesize) {
		$filesize = (round($filesize / 1024 * 100) / 100).' K';
	} else {
		$filesize = $filesize.' bytes';
	}
	return $filesize;
}

/**
 * 文件尺寸描述转为bytes整数值
 * @param string $val
 * @return number
 */
function count_size($val) {
	$unit = '';
	$value = 0;
	if (preg_match('/^(\d+)(.*)/s', trim($val), $match)) {
		$unit = strtolower(trim($match[2]));
		$value = intval(trim($match[1]));
	}
	if ($unit == 'bytes' || empty($unit)) {
		return $value;
	}
	switch ($unit) {
		case 't':
			$value *= 1024;
		case 'g':
			$value *= 1024;
		case 'm':
			$value *= 1024;
		case 'k':
			$value *= 1024;
	}
	return $value;
}

/**
 * 计算目录的大小;
 * @param string $dir	目录;
 * @return 返回目录所占的空间;
 */
function dir_size($dir) {
	$dh = opendir($dir);
	$size = 0;
	while($file = readdir($dh)) {
		if($file != '.' && $file != '..') {
			$path = $dir."/".$file;
			if(@is_dir($path)) {
				$size += dir_size($path);
			} else {
				$size += filesize($path);
			}
		}
	}
	@closedir($dh);
	return $size;
}

/**
 * 检测路径是否已 / 结尾;
 * @param string $filepath	文件目录;
 * @param boolean $pre_slash	控制是否需要以 ./ 开头;
 * @param boolean $suffix_slash	控制是否需要以 / 结尾;
 * @return 返回字串;
 */
function slash($filepath, $pre_slash = 1, $suffix_slash = 1) {
	if (empty($filepath)) {
		return;
	}

	$filepath = str_replace('\\', '/', $filepath);
	$filepath = preg_replace("/(\.[\.]*)\//i", '/', $filepath);
	$filepath = preg_replace("/(\/[\/]+)/i", '/', $filepath);
	$first = '/' == substr($filepath, 0, 1) ? 1 : 0;
	$last = '/' == substr($filepath , -1) ? 1 : 0;
	/** suffix_slash 为 1 时，如果路径字串没有以 / 结尾，则补上 */
	if (1 == $suffix_slash) {
		$filepath = 1 == $last ? $filepath : $filepath.'/';
	} else {
		$filepath = 1 == $last ? substr($filepath, 0, -1) : $filepath;
	}

	/** pre_slash 为 1 时，如果路径字串没有以 ./ 或 / 开头，则补上 */
	if (1 == $pre_slash) {
		$filepath = 1 == $first ? '.'.$filepath : './'.$filepath;
	} else {
		$filepath = 1 == $first ? substr($filepath, 1) : $filepath;
	}

	return preg_replace("/(\/[\/]+)/i", '/', $filepath);
}

/**
 * 检查文件的路径是否合法;
 * @param string $filename	文件路径;
 * @param boolean $ifcheck	是否检查路径中是否有不允许的符合(.. -> 可能会造成跨目录的BUG);
 * @return 返回文件路径;
 */
function check_filepath($filename, $ifcheck = 1) {
	$tmpname = strtolower($filename);
	$arr = array('http://');
	$ifcheck && array_push($arr, '..');
	if(str_replace($arr, '', $tmpname) != $tmpname) {
		rexit('Forbidden');
	}
	return $filename;
}

/**
 * 写一个 PHP 缓存文件;
 * @param string $dir	目录路径;
 * @param string $cachename	文件名;
 * @param array $data	数据;
 * @param string $prefix	文件名前缀;
 */
function write_cache_file($dir, $cachename, $data = array(), $prefix = 'cache_') {
	if(!empty($data) && is_array($data)) {
		$cachedata = "\$cache = ".rvar_export($data).";\n\n";
	} else {
		$cachedata = $data;
	}

	rfwrite(slash($dir.$prefix.$cachename).'.php', "<?php\n//Myws! cache file, DO NOT modify me!\n//Created on ".date("M j, Y, G:i")."\n\n".$cachedata, 'w');
}

/**
 * 转换编码
 * @param mixed $m
 * @param string $from
 * @param string $to
 * @return mixed
 */
function riconv($m, $from = 'UTF-8', $to = 'GBK'){
	if ( strpos($to, '//') === false ) {
		$to	=	$to.'//IGNORE';
	}
	switch ( gettype($m) ) {
		case 'integer':
		case 'boolean':
		case 'float':
		case 'double':
		case 'NULL':
			return $m;
		case 'string':
			return @iconv($from, $to, $m);
		case 'object':
			$vars = array_keys(get_object_vars($m));
			foreach($vars AS $key) {
				$m->$key = riconv($m->$key, $from ,$to);
			}
			return $m;
		case 'array':
			foreach($m AS $k => $v) {
				$k2	=	riconv($k, $from, $to);
				if ( $k != $k2 ) {
					unset($m[$k]);
				}
				$m[$k2] = riconv($v, $from, $to);
			}
			return $m;
		default:
			return '';
	}
}

/**
 * json_encode2
 * 将变量转为 json 编码字符串
 *
 * @param mixed $value
 * @param int $options 同内置函数的第二个参数参量，默认为：0，或JSON_*_*
 * @return string
 */
function rjson_encode($value, $options = 0) {

	if (is_object($value)) {
		$value = get_object_vars($value);
	}

	$value = _urlencode($value);
	$json = json_encode($value, $options);
	return urldecode($json);
}

/**
 * _urlencode
 * urlencode 字符串或数组
 *
 * 注意：
 *   本函数其实只是用于json_encode2，如果php版本>=5.3的话，
 *   建议用闭包实现，这样就不用将此函数暴露在全局中
 *
 * @param string|array $value
 * @return string|array
 */
function _urlencode($value) {

	if (is_array($value)) {
		foreach ($value as $k => $v) {
			$value[$k] = _urlencode($v);
		}
	} else if (is_string($value)) {
		$value = urlencode(str_replace(
			array("\\", "\r\n", "\r", "\n", "\"", "\/", "\t", "\x08", "\x0C"),
			array('\\\\', '\\n', '\\n', '\\n', '\\"', '\\/', '\\t', "\\f", "\\b"),
			$value
		));
	}

	return $value;
}

function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {

	if (function_exists("mb_substr")) $slice = mb_substr($str, $start, $length, $charset);
	elseif (function_exists('iconv_substr')) {
		$slice = iconv_substr($str, $start, $length, $charset);
	} else {
		$re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset], $str, $match);
		$slice = join("", array_slice($match[0], $start, $length));
	}

	return $suffix ? $slice . '...' : $slice;
}

/**
 * 字符截取
 * @param string $string
 * @param number $length
 * @param string $dot
 * @return string
 */
function rsubstr($string, $length, $dot = ' ...', $charset = null) {
	if(strlen($string) <= $length) {
		return $string;
	}
	if ($charset === null) {
		$charset = 'utf-8';
	}
	$pre = chr(1);
	$end = chr(1);
	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), $string);
	$strcut = '';
	if(strtolower($charset) == 'utf-8') {
		$n = $tn = $noc = 0;
		while($n < strlen($string)) {
			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}

			if($noc >= $length) {
				break;
			}
		}

		if($noc > $length) {
			$n -= $tn;
		}

		$strcut = substr($string, 0, $n);
	} else {
		for($i = 0; $i < $length; $i++) {
			$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
		}
	}
	$strcut = str_replace(array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
	$pos = strrpos($strcut, chr(1));
	if($pos !== false) {
		$strcut = substr($strcut,0,$pos);
	}
	return $strcut.$dot;
}

/**
 * 转换文本格式的时间为本地时间戳
 * @param string $datetime
 * @return number
 */
function rstrtotime($datetime) {

	$timestamp = strtotime($datetime);
	if (-1 === $timestamp || FALSE === $timestamp) {
		return 0;
	}

	// 判断是否含有时区标识
	if (preg_match("/(GMT|UTC)/i", $datetime) || preg_match("/T(.*?)Z$/i", $datetime)) {
		return $timestamp;
	}

	/** 获取时区配置 */
	$offset = (int)config::get(startup_env::get('app_name').'.timeoffset');
	$ymdhis = date('Y m d H i s', $timestamp);
	list($y, $m, $d, $h, $i, $s) = explode(' ', $ymdhis);
	$timestamp = gmmktime($h, $i, $s, $m, $d, $y);
	return $timestamp - 3600 * $offset;
}

/**
 * 获取文件 或 扩展名 的 mime 类型字符串
 * @author Deepseath
 * @param string $filename 可以是文件名也可以是扩展名也可以使用文件绝对路径
 * @return string
 */
function rmime_content_type($filename) {

	$mime_types = array(
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',

			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
	);

	if (strpos($filename, '.') !== false) {
		// 如果是文件名则取出其扩展名
		$ext = rstrtolower(substr(strrchr($filename, '.'), 1, 10));
	} else {
		// 如果是扩展名则直接使用
		$ext = $filename;
	}

	// 待返回的 mime 字符串
	$mime = '';

	if (isset($mime_types[$ext])) {
		// 已定义的扩展名mime

		$mime = $mime_types[$ext];

	} elseif (is_file($filename)) {
		// 给出的参数是一个文件路径

		if (function_exists('finfo_open')) {
			// 如果是一个文件路径 且 内置函数 finfo_open 可用（fileinfo扩展可用）

			$finfo = finfo_open(FILEINFO_MIME);
			$mime = finfo_file($finfo, $filename);
			finfo_close($finfo);
		}

		// 如果上面未取到 $mime
		if (!$mime && function_exists('mime_content_type') && ($tmp = mime_content_type($filename))) {
			// 尝试使用内置函数 mime_content_type 来获取（fileinfo扩展可用）

			$mime = $tmp;
			unset($tmp);
		}

		if ($mime && stripos($mime, ';') !== false) {
			// 剔除字符集信息
			$mime = trim(preg_replace('/;.+?$/', '', $mime));
		}
	}

	if (!$mime) {
		// 如果以上操作均未获取到，则认为是普通未知文件
		$mime = 'application/octet-stream';
	}

	return $mime;
}

/**
 * 更高级的对数组按照键名进行“自然排序”类似natcasesort和ksort
 * 可将多维数组全部按照各维度的键名排序
 * @param array $array <strong style="color:red">引用结果</strong>
 * @author Deepseath
 * @return boolean
 */
function natksort(&$array) {
	$keys = array_keys($array);
	natcasesort($keys);
	foreach ($keys as $k) {
		$_sub = $array[$k];
		if (is_array($_sub)) {
			natksort($_sub);
		}
		$new_array[$k] = $_sub;
		unset($_sub);
	}
	$array = $new_array;
	return true;
}

/**
 * 认证字符串的加密和解密函数
 * @param string $string 待处理的字符串
 * @param string $key 加密密钥
 * @param string $operation 操作方式：DECODE=解密，ENCODE=加密
 * @param number $expiry 过期时间。单位：秒。0=不过期
 * @return boolean|string
 */
function authcode($string, $key, $operation = 'DECODE', $expiry = 0) {
	if ($key == '') {
		return false;
	}
	$ckey_length = 4;
	$key = md5($key);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? rbase64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', rbase64_encode($result));
	}

}

/**
 * 将一组整齐的数组转换为csv字符串
 * @param array $list 待转换的数组列表数据
 * @param string $newline_symbol 每行之间的分隔符号，默认为：“\r\n”
 * @param string $field_comma 字段之间的分隔符号，默认为：“,”
 * @param string $field_quote_symbol 字段的引用符号，默认为：“"”
 * @param string $out_charset 输出的数据字符集编码，默认为：gbk
 * @return string
 */
function array2csv(array $list, $newline_symbol = "\r\n", $field_comma = ",", $field_quote_symbol = '"', $out_charset = 'gbk') {

	// 初始化输出
	$data = '';
	// 初始化换行符号
	$_row_comma = '';

	// 遍历所有行数据
	foreach ($list as $_arr_row) {

		// 初始化行数据
		$_row = '';
		// 初始化每个字段的分隔符号
		$_comma = '';

		// 遍历所有字段
		foreach ($_arr_row as $_str) {
			// 字段数据分隔符
			$_row .= $_comma;
			if (strpos($_str, $field_comma) === false) {
				// 字段数据不包含字段分隔符，直接使用
				$_row .= $_str;
			} else {
				// 字段数据包含字段分隔符，则使用字段引用符号引用并转义数据内的引用符号
				$_row .= $field_quote_symbol.addcslashes($_str, $field_quote_symbol).$field_quote_symbol;
			}
			// 定义字段分隔符号
			$_comma = $field_comma;
		}

		// 行数据，以行分隔符号连接
		$data .= $_row_comma.$_row;

		// 定义换行符号
		$_row_comma = $newline_symbol;
	}

	// 输出数据
	return riconv($data, 'UTF-8', $out_charset);
}

/**
 * PHP < 5.5 兼容函数
 */
if (!function_exists('array_column')) {
	function array_column($input, $column_key, $index_key = null)
	{
		if ($index_key !== null) {
			// Collect the keys
			$keys = array();
			$i = 0; // Counter for numerical keys when key does not exist

			foreach ($input as $row) {
				if (array_key_exists($index_key, $row)) {
					// Update counter for numerical keys
					if (is_numeric($row[$index_key]) || is_bool($row[$index_key])) {
						$i = max($i, (int) $row[$index_key] + 1);
					}

					// Get the key from a single column of the array
					$keys[] = $row[$index_key];
				} else {
					// The key does not exist, use numerical indexing
					$keys[] = $i++;
				}
			}
		}

		if ($column_key !== null) {
			// Collect the values
			$values = array();
			$i = 0; // Counter for removing keys

			foreach ($input as $row) {
				if (array_key_exists($column_key, $row)) {
					// Get the values from a single column of the input array
					$values[] = $row[$column_key];
					$i++;
				} elseif (isset($keys)) {
					// Values does not exist, also drop the key for it
					array_splice($keys, $i, 1);
				}
			}
		} else {
			// Get the full arrays
			$values = array_values($input);
		}

		if ($index_key !== null) {
			return array_combine($keys, $values);
		}

		return $values;
	}
}

