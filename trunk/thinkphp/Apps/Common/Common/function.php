<?php
/**
 * function.php
 * 项目全局方法
 * $Author$
 * $Id$
 */

/**
 * 获取插件信息
 * @param array $plugin 插件信息
 * @param number $pluginid 插件ID
 * @return boolean
 */
function get_plugin(&$plugin, $pluginid = 0) {

	// 获取插件列表
	$cache = &\Common\Common\Cache::instance();
	$plugins = $cache->get('Common.plugin');
	// 获取插件ID
	if (0 >= $pluginid) {
		$pluginid = cfg('PLUGIN_ID');
	}

	// 如果插件ID错误, 则随机取一个
	if (0 >= $pluginid || !isset($plugins[$pluginid])) {
		// 编辑插件
		foreach ($plugins as $_p) {
			// 如果当前插件信息为空
			if (empty($plugin)) {
				$plugin = $_p;
			}

			// 如果套件ID存在
			if (!empty($_p['cp_suiteid'])) {
				$plugin = $_p;
				break;
			}
		}
	} else {
		$plugin = $plugins[$pluginid];
	}

	return $plugin;
}

/**
 * 根据域名生成站点缓存目录
 *
 * @param string $domain 二级域名
 */
function get_sitedir($domain = '') {

	static $sitedir = '';
	// 如果已经路径生成
	if (! empty($sitedir)) {
		return $sitedir;
	}

	// 如果 $domain 为空, 则重新取域名信息
	if (empty($domain)) {
		$domain = get_sl_domain();
	}

	// md5, 取首尾字符 + 域名作为目录
	$md5 = md5($domain);
	$sitedir = cfg('DATA_CACHE_PATH') . substr($md5, 0, 1) . '/' . substr($md5, - 1) . '/' . $domain . '/';
	rmkdir($sitedir);
	return $sitedir;
}

// 清理缓存
function clear_cache() {

	// 获取站点缓存目录
	/**$sitedir = get_sitedir();
	$pattern = addcslashes(cfg('DATA_CACHE_PATH'), '"\/\'\.');
	$replacement = APP_PATH . '../../apps/voa/tmp/site/';
	$sitedir = preg_replace('/^' . $pattern . '/i', $replacement, $sitedir);

	// 打开目录
	$handle = opendir($sitedir);
	if (!$handle) {
		return false;
	}

	// 遍历所有文件
	while (false !== ($file = readdir($handle))) {
		if (false === stripos($file, 'dbconf.inc.php')) {
			@unlink($sitedir . '/' . $file);
		}
	}

	closedir($handle);*/
	$serv_sys = D('Common/CommonSyscache', 'Service');
	$list = $serv_sys->list_all();
	foreach ($list as $_cache) {
		// 兼容旧框架
		$cachekey = $_cache['csc_name'];
		$ucfirst = strtoupper($cachekey{0});
		if ($ucfirst != $cachekey{0}) {
			$keys = explode('.', $cachekey);
			if (1 == count($keys)) {
				array_unshift($keys, 'common');
			}

			if ('plugin' == $keys[0]) {
				array_shift($keys);
			}

			$cachekey = implode('.', $keys);
			$cachekey = ucfirst($cachekey);
		}

		\Common\Common\Cache::instance()->set($cachekey, null);
	}

	return true;
}

/**
 * 重写配置读取方法
 *
 * @param string $file 文件路径
 * @return Ambigous <void, multitype:>
 */
function load_dbconfig($file) {

	// 如果文件不存在, 则生成
	if (!file_exists($file)) {
		// 特殊处理, 把缓存移到旧框架下
		$pattern = addcslashes(cfg('DATA_CACHE_PATH'), '"\/\'\.');
		$replacement = APP_PATH . '../../apps/voa/tmp/site/';
		$oldfile = preg_replace('/^' . $pattern . '/i', $replacement, $file);
		if (@include $oldfile) {
			$config = array(
				/* 数据库设置 */
				'DB_TYPE' => 'mysql', // 数据库类型
				'DB_HOST' => $conf['host'], // 服务器地址
				'DB_NAME' => $conf['dbname'], // 数据库名
				'DB_USER' => $conf['user'], // 用户名
				'DB_PWD' => $conf['pw'], // 密码
				'DB_PORT' => '3306', // 端口
				'DB_PREFIX' => 'oa_', // 数据库表前缀
				'DB_CHARSET' => 'utf8' // 数据库编码默认采用utf8
			);

			rfwrite($file, "<?php\n//wbs! cache file, DO NOT modify me!\n//Created on ".rgmdate(NOW_TIME, "M j, Y, G:i")."\n\nreturn ".var_export($config, true).";\n\n");
		}
	}

	return load_config($file);
}


/**
 * [is_in_range 判断一个数据是否在两个数据之间]
 * @param  [type]  $x   [description]
 * @param  [type]  $min [description]
 * @param  [type]  $max [description]
 * @return boolean      [description]
 */
function is_in_range($x, $min, $max) {
	return $x > $min && $x < $max;
}



/**
 * 给出附件的at_id返回附件访问的绝对url地址
 * @param number $at_id 附件id
 * @param int $width 宽度
 * @return string
 */
function attachment_url($at_id, $width = 0) {

	$cache = &\Common\Common\Cache::instance();
	$sets = $cache->get('Common.setting');

	$scheme = cfg('PROTOCAL');
	$url = $scheme.$sets['domain'].'/attachment/read/'.$at_id;
	if (0 < $width) {
		$url .= '/'.$width;
	}

	/** 临时去除 */
	// FIXME 临时去除附件浏览验证扰码
	//$url .= '?ts='.startup_env::get('timestamp').'&sig='.self::attach_sig_create($at_id);

	return $url;
}


function parse_headers(&$header, &$cookies, $headers) {

	// 头部参数
	$header = array();
	// 响应的 cookie
	$cookies = array();
	// 最后一个头信息参数
	$last_header = array();

	$header_lines = $headers;
	array_shift($header_lines);
	foreach ($header_lines as $header_line) {
		parse_header_line($header_line, $header, $last_header);
	}

	if (array_key_exists('set-cookie', $header)) {
		if (is_array($header['set-cookie'])) {
			$cookies = $header['set-cookie'];
		} else {
			$cookies = array($header['set-cookie']);
		}

		foreach ($cookies as $cookie_str) {
			parse_cookie($cookie_str, $cookies);
		}

		unset($header['set-cookie']);
	}

	foreach (array_keys($header) as $k) {
		if (is_array($header[$k])) {
			$header[$k] = implode(', ', $header[$k]);
		}
	}

	return true;
}



/**
 * Parses the line from HTTP response filling $headers array
 *
 * The method should be called after reading the line from socket or receiving-
 * it into cURL callback. Passing an empty string here indicates the end of
 * response headers and triggers additional processing, so be sure to pass an
 * empty string in the end.
 *
 * @param string Line from HTTP response
 */
function parse_header_line($header_line, &$header, &$last_header) {

	$header_line = trim($header_line, "\r\n");
	// string of the form header-name: header value
	if (preg_match('!^([^\x00-\x1f\x7f-\xff()<>@,;:\\\\"/\[\]?={}\s]+):(.+)$!', $header_line, $m)) {
		$name = strtolower($m[1]);
		$value = trim($m[2]);
		$header[$name] = $value;

		if (! is_array($header[$name])) {
			$header[$name] = array($header[$name]);
		}

		$header[$name][] = $value;
		$last_header = $name;
	} elseif (preg_match('!^\s+(.+)$!', $header_line, $m) && $last_header) {
		if (! is_array($header[$this->_last_header])) {
			$header[$this->_last_header] .= ' ' . trim($m[1]);
		} else {
			$key = count($header[$this->_last_header]) - 1;
			$header[$this->_last_header][$key] .= ' ' . trim($m[1]);
		}
	}

	return true;
}


/**
 * Parses a Set-Cookie header to fill $cookies array
 *
 * @param string value of Set-Cookie header
 * @link http://cgi.netscape.com/newsref/std/cookie_spec.html
 */
function parse_cookie($cookie_str, &$cookies) {

	$cookie = array('expires' => null, 'domain' => null, 'path' => null, 'secure' => false);

	// Only a name=value pair
	if (! strpos($cookie_str, ';')) {
		$pos = strpos($cookie_str, '=');
		$cookie['name'] = trim(substr($cookie_str, 0, $pos));
		$cookie['value'] = trim(substr($cookie_str, $pos + 1));
	} else { // Some optional parameters are supplied
		$elements = explode(';', $cookie_str);
		$pos = strpos($elements[0], '=');
		$cookie['name'] = trim(substr($elements[0], 0, $pos));
		$cookie['value'] = trim(substr($elements[0], $pos + 1));

		for($i = 1; $i < count($elements); $i ++) {
			if (false === strpos($elements[$i], '=')) {
				$el_name = trim($elements[$i]);
				$el_value = null;
			} else {
				list($el_name, $el_value) = array_map('trim', explode('=', $elements[$i]));
			}

			$el_name = strtolower($el_name);
			if ('secure' == $el_name) {
				$cookie['secure'] = true;
			} elseif ('expires' == $el_name) {
				$cookie['expires'] = str_replace('"', '', $el_value);
			} elseif ('path' == $el_name || 'domain' == $el_name) {
				$cookie[$el_name] = urldecode($el_value);
			} else {
				$cookie[$el_name] = $el_value;
			}
		}
	}

	$cookies[] = $cookie;
	return true;
}

/**
 * 根据域名生成站点缓存目录
 *
 * @param string $domain 二级域名
 */
function get_pemdir($domain = '') {

    static $sitedir = '';
    // 如果已经路径生成
    if (! empty($sitedir)) {
        return $sitedir;
    }

    // 如果 $domain 为空, 则重新取域名信息
    if (empty($domain)) {
        $domain = get_sl_domain();
    }

    // md5, 取首尾字符 + 域名作为目录
    $md5 = md5($domain);
    $sitedir = cfg('DATA_CACHE_PATH') . substr($md5, 0, 1) . '/' . substr($md5, - 1) . '/' . $domain . '/';

    $pattern = addcslashes(cfg('DATA_CACHE_PATH'), '"\/\'\.');
    $replacement = APP_PATH . '../../apps/voa/data/pem/';
    $sitedir = preg_replace('/^' . $pattern . '/i', $replacement, $sitedir);
    rmkdir($sitedir);
    return $sitedir;
}

/**
 * 记录缓存
 * @param string $name 缓存名称
 * @return boolean
 */
function syscache_record($cachename) {

	static $s_caches = null;
	$serv_sys = D('Common/CommonSyscache', 'Service');
	if (null == $s_caches) {
		$s_caches = $serv_sys->list_all();
		if (!empty($s_caches)) {
			$s_caches = array_combine_by_key($s_caches, 'csc_name');
		}
	}

	// 如果不存在, 则
	if (empty($s_caches[$cachename])) {
		$serv_sys->insert(array('csc_name' => $cachename, 'csc_type' => 1, 'csc_data' => ''));
	}

	return true;
}

/**
 * 判断应用是否开启
 * @param string $identifier
 * @return bool
 */
function is_plugin_open($identifier = '') {

	// 获取插件列表
	$cache = &\Common\Common\Cache::instance();
	$plugins = $cache->get('Common.plugin');

	if (empty($identifier)) {
		return false;
	}

	foreach ($plugins as $_k => $_p) {
		if ($_p['cp_identifier'] != $identifier) {
			continue;
		}

		if ($_p['cp_available'] == \Common\Model\CommonPluginModel::AVAILABLE_OPEN) {
			return true;
		}
	}

	return false;
}

