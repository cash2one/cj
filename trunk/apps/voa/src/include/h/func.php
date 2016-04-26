<?php
/**
 * voa_h_func
 * $Author$
 * $Id$
 */

class voa_h_func {
	/** 错误码 */
	public static $errcode = 0;
	/** 错误信息 */
	public static $errmsg = '';
	/** 非系统级别错误 */
	public static $error = 0;

	/**
	 * 从主机地址中获取域名信息
	 * @param string $host 主机地址
	 */
	public static function get_domain($host = '') {
		if (empty($host)) {
			$request = controller_request::get_instance();
			$host = $request->server('HTTP_HOST');
		}

		$hostarr = explode('.', $host);
		$domain = rawurlencode($hostarr[0]);
		return $domain;
	}

	/**
	 * 根据域名生成站点缓存目录
	 * @param string $domain 二级域名
	 */
	public static function get_sitedir($domain = '') {
		$sitedir = startup_env::get('sitedir');
		if (!empty($sitedir)) {
			return $sitedir;
		}

		if (empty($domain)) {
			$domain = voa_h_func::get_domain();
		}

		$md5 = md5($domain);
		$sitedir = config::get(startup_env::get('app_name').'.cache.sitedir');
		$sitedir = APP_PATH.$sitedir.'/'.substr($md5, 0, 1).'/'.substr($md5, -1).'/'.$domain.'/';
		rmkdir($sitedir);
		startup_env::set('sitedir', $sitedir);
		return $sitedir;
	}

	/**
	 * 根据域名生成附件缓存目录
	 * @param string $domain 二级域名
	 */
	public static function get_attachdir($domain = '') {
		$attachdir = startup_env::get('attachdir');
		if (!empty($attachdir)) {
			return $attachdir;
		}

		if (empty($domain)) {
			$domain = voa_h_func::get_domain();
		}

		$md5 = md5($domain);


		$attachdir = config::get(startup_env::get('app_name').'.attachment.dir');

		$attachdir = APP_PATH.$attachdir.'/'.substr($md5, 0, 1).'/'.substr($md5, -1).'/'.$domain.'/';

		rmkdir($attachdir);
		startup_env::set('attachdir', $attachdir);
		return $attachdir;
	}

	/**
	 * 根据域名生成附件缓存目录
	 * @param string $domain 二级域名
	 */
	public static function get_pemdir($domain = '') {

		$attachdir = startup_env::get('attachpemdir');
		if (!empty($attachdir)) {
			return $attachdir;
		}

		if (empty($domain)) {
			$domain = voa_h_func::get_domain();
		}

		$md5 = md5($domain);
		$attachdir = config::get(startup_env::get('app_name').'.attachment.pemdir');
		$attachdir = APP_PATH.$attachdir.'/'.substr($md5, 0, 1).'/'.substr($md5, -1).'/'.$domain.'/';

		rmkdir($attachdir);
		startup_env::set('attachpemdir', $attachdir);
		return $attachdir;
	}

	/**
	 * 根据当前页数、每页的显示数来确定 sql 查询的开始及读取条数;
	 * @param int $page	当前页数;
	 * @param int $perpage	每页显示数;
	 * @param int $maxpage	显示的最大页数;
	 * @return 返回开始数、每页显示数、当前页数;
	 */
	public static function get_limit($page = 0, $perpage = 0, $maxpage = 0) {
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$perpage = intval($perpage) ? intval($perpage) : (empty($sets['perpage']) ? 30 : $sets['perpage']);
		$perpage = empty($perpage) ? 30 : $perpage;
		$page = max(intval($page), 1);
		$maxpage = intval($maxpage);
		$page = $maxpage && $page > $maxpage ? $maxpage : $page;
		$start = ($page - 1) * $perpage;
		return array($start, $perpage, $page);
	}

	/**
	 * 从指定 url 获取 json 数据
	 * @param array $data 结果
	 * @param string $url url地址
	 * @param mixed $post post数据
	 */
	public static function get_json_by_post(&$data, $url, $post = '') {

		$snoopy = new snoopy();
		$result = $snoopy->submit($url, $post);
		logger::error("url: " . $url . "\npost: " . var_export($post, true));
		/** 如果读取错误 */
		if (!$result || 200 != $snoopy->status) {
			logger::error('$snoopy->submit error: '.$url.'|'.$result.'|'.$snoopy->status);
			return false;
		}

		/** 解析 json */
		$data = (array)json_decode($snoopy->results, true);
		logger::error("result: " . var_export($snoopy->results, true) . "\ndata: " . var_export($data, true));

		if (empty($data)) {
			logger::error('$snoopy->submit error: '.$url.'|'.$snoopy->results.'|'.$snoopy->status);
			return false;
		}

		return true;
	}

	/**
	 * 发送一组自定义的 http 头字段 到指定 url 获取 json 数据
	 * <p style="color:blue"><strong>注意：</strong>与 $this->get_json_by_post 类似，只是会发送自定义的http头字段信息。<br />本方法属于特殊需求，不推荐常规使用（企业微信的通讯录接口需要使用）。</p>
	 * @param array &$data <strong style="color:red">(引用结果)</strong> 获取的结果
	 * @param string $url 请求的url地址
	 * @param mixed $post post数据
	 * @param array $http_header 请求的http头字段数组
	 * @param array $http_method 使用的HTTP协议，默认为：GET，可使用 POST、DELETE、PUT
	 * @return boolean
	 */
	public static function get_json_by_post_and_header(&$data, $url, $post = array(), $http_header = array(), $http_method = 'GET', &$snoopy_reporting = array()) {

		if ((empty($http_header) || !is_array($http_header)) && rstrtoupper($http_method) == 'POST') {
			// 如果未指定http头字段，则使用公用的请求方式
			return voa_h_func::get_json_by_post($data, $url, $post);
		}

		logger::error("url: " . $url . "|method: {$http_method}" . "\npost: " . var_export($post, true));
		// 载入 Snoopy 类
		$snoopy = new snoopy();
		// 使用自定义的头字段，格式为 array(字段名 => 值, ... ...)
		$snoopy->rawheaders = $http_header;
		switch (rstrtoupper($http_method)) {
			case 'POST':
			case 'PUT':
				$result = $snoopy->submit($url, $post);
				break;
			case 'DELETE':
				$result = $snoopy->submit_by_delete($url, $post);
				break;
			default:
				if ($post) {
					if (is_array($post)) {
						$get_data = http_build_query($post);
					} else {
						$get_data = $post;
					}
					if (strpos($url, '?') === false) {
						$url .= '?';
					} else {
						$url .= '&';
					}
					$url .= $get_data;
				}
				$result = $snoopy->fetch($url);
		}

		$snoopy_reporting = $snoopy;

		/** 如果读取错误 */
		if (!$result || 200 != $snoopy->status) {
			logger::error('$snoopy->submit error: '.$url.'|'.$result.'|'.$snoopy->status);
			return false;
		}

		/** 解析 json */
		$data = json_decode($snoopy->results, true);
		logger::error("result: " . var_export($snoopy->results, true) . "\ndata: " . var_export($data, true));
		if (empty($data)) {
			logger::error('$snoopy->submit error: '.$url.'|'.$snoopy->results.'|'.$snoopy->status);
			return false;
		}

		return true;
	}

	/**
	 * 过滤垃圾词语;
	 * @param string $message	待过滤的字串;
	 * @return unknown
	 */
	public static function censor($message) {
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$censor = $sets['censor'];
		if($censor['banned'] && preg_match($censor['banned'], $message)) {
			return false;
		} else {
			return empty($censor['filter']) ? $message : @preg_replace($censor['filter']['find'], $censor['filter']['replace'], $message);
		}
	}

	/**
	 *
	 * @param string $message	字串;
	 * @return unknown
	 */
	public static function censormod($message) {
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$censor = $sets['censor'];
		return $censor['mod'] && preg_match($censor['mod'], $message);
	}

	/**
	 * 处理附件的显示;
	 * @param int $aid	附件ID值;
	 * @param array $attachments	附件数组;
	 * @return unknown
	 */
	public static function attach_tag($aid, $attachments) {
		$replacement = '';
		if(isset($attachments[$aid])) {
			$attach = $attachments[$aid];
			unset($attachments[$aid]);
			$attach_url = $attach['_url'].'/'.$attach['attachment'];
			if($attach['isimage']) {
				$replacement = '<br /><img src="'.$attach_url.'" aid="attachimg_'.$aid.'" border="0" onload="javascript:checkImageWidth(this);" onmouseover="javascript:changeImagePointer(this);" onclick="javascript:openImageInNewWindow(this);" onmousewheel="javascript:return imgzoom(this);" /><br />';
			} else {
				$replacement = $attach['attach_icon'].'<a href="'.$attach_url.'" target="_blank">'.$attach['filename'].'</a>';
			}
		} else {
			$replacement = '<strike>[attach]'.$aid.'[/attach]</strike>';
		}

		return array($replacement, $attachments);
	}

	/**
	 * 返回给定秒数的时间描述
	 * @param number $second
	 * @return array(day, hour, minute)
	 */
	public static function get_dhi($second) {
		return array(
			floor($second/86400), floor(($second % 86400) / 3600), floor(($second % 3600) / 60)
		);
	}

	/**
	 * 返回格式化的后的字串
	 * @param string $fmt 格式字串
	 * @param number $ts 时间戳
	 * @return multitype:Ambigous <>
	 */
	public static function date_fmt($fmt, $ts = 0) {
		$ts = 0 >= $ts ? startup_env::get('timestamp') : $ts;
		$fmts = explode(' ', $fmt);
		$r = rgmdate($ts, $fmt);
		$rs = explode(' ', $r);
		$arr = array();
		foreach ($fmts as $k => $v) {
			$arr[$v] = $rs[$k];
		}

		return $arr;
	}

	/**
	 * 返回代理 url 地址
	 * @param string $path url 路径(地址)
	 * @param number $pluginid
	 * @return string
	 */
	public static function get_agent_url($path, $pluginid = 0) {
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$scheme = config::get('voa.oa_http_scheme');
		return $scheme.$sets['domain'].$path.(false === stripos($path, '?') ? '?' : '&').'pluginid='.$pluginid;
	}

	/**
	 * 公共的密码加密储存方法
	 * @param string $password 密码值，可以为原文也可以md5后
	 * @param string $salt 散列值，如为空则自动生成
	 * @param boolean $is_original 给定的$password是否为密码原文，true是，false为md5后的值
	 * @param number $salt_length 散列值的长度
	 * @return array(password, salt)
	 * <p>array(0=>password 加密后的密码字符串, 1 =>salt 密码散列值)</p>
	 */
	public static function generate_password($password, $salt = null, $is_original = false, $salt_length = 6) {
		$salt = (string)$salt;
		if ($salt === null || $salt === '') {
			$salt = random($salt_length);
		}
		if ($is_original) {
			$password = md5($password);
		}
		return array(md5($password.$salt), $salt);
	}

	/** 获取静态目录 */
	public static function cp_static_url() {
		return '/admincp/static/';
	}

	/**
	 * 生成 sig 值
	 * @param mixed $source 源字串
	 * @param int $timestamp 时间戳
	 * @return string
	 */
	public static function sig_create($source, &$timestamp = 0) {

		// 强制转换成数组
		$source = (array)$source;
		// 如果没传时间戳
		$timestamp = 0 < $timestamp ? $timestamp : startup_env::get('timestamp');
		// 取秘钥
		$secret = config::get(startup_env::get('app_name').'.auth_key');
		// 参数数组
		$source[] = $timestamp;
		$source[] = $secret;
		// 排序
		sort($source, SORT_STRING);

		return sha1(implode($source));
	}

	/**
	 * 检查默认 sig
	 * @param array $params 外部参数
	 * @return boolean
	 */
	public static function sig_check($params) {

		// 取出时间戳和 sig
		$ts = (int)$params['ts'];
		$sig = (string)$params['sig'];

		// 删除键值
		unset($params['ts'], $params['sig']);

		// 如果 sig 不相等
		if ($sig != self::sig_create($params, $ts)) {
			return false;
		}

		return true;
	}

	/**
	 * 全局输出定义错误码信息
	 * @param string $const_string 错误码常量文字，格式为：[number]:[string]
	 * @return boolean
	 */
	public static function set_errmsg($const_string) {

		self::$errcode = -449;
		self::$errmsg = 'voa_h_func default';
		if (preg_match('/^\s*(\d+)\s*\:\s*(.+)$/', $const_string, $match)) {
			// 分离 错误代码 和 错误消息
			self::$errcode = (int)$match[1];
			self::$errmsg = (string)$match[2];
		} else {
			// 错误代码定义出错
			self::$errcode = -440;
			self::$errmsg = $const_string;
		}

		if (!preg_match('/\%\w/i', self::$errmsg, $matches)) {
			// 错误消息描述内未发现变量，则直接输出
			return false;
		}

		// 获取给定的参数
		$values = func_get_args();
		// 列出变量值
		unset($values[0]);
		if (empty($values)) {
			// 如果变量值不存在
			return false;
		}

		// 变量个数 与 值的个数 相差数
		$count = count(preg_split('/\%\w/i', self::$errmsg)) - count($values);
		if ($count > 0) {
			// 变量个数 多于 给定值个数，则补充值的个数，避免出错
			for ($i = 0; $i < $count; $i++) {
				 $values[] = '';
			}
		}
		// 转义变量名
		self::$errmsg = preg_replace('/\%\s+$/is', '', vsprintf(self::$errmsg, $values));

		return false;
	}

	/**
	 * 抛出错误信息
	 * @param string $const_string
	 * @throws Exception
	 * @return false
	 */
	public static function throw_errmsg($const_string) {

		// 标记为非系统级错误
		self::$error = 1;
		self::$errcode = -449;
		self::$errmsg = 'voa_h_func default';
		if (preg_match('/^\s*(\d+)\s*\:\s*(.+)$/', $const_string, $match)) {
			// 分离 错误代码 和 错误消息
			self::$errcode = (int)$match[1];
			self::$errmsg = (string)$match[2];
		} else {
			// 错误代码定义出错
			self::$errcode = -440;
			self::$errmsg = '代码定义错误"'.$const_string.'"';
		}

		if (!preg_match('/\%\w/i', self::$errmsg, $matches)) {
			// 错误消息描述内未发现变量，则直接输出
			throw new help_exception(self::$errmsg, self::$errcode);
			return false;
		}

		// 获取给定的参数
		$values = func_get_args();
		// 列出变量值
		unset($values[0]);
		if (empty($values)) {
			// 如果变量值不存在
			throw new help_exception(self::$errmsg, self::$errcode);
			return false;
		}

		// 变量个数 与 值的个数 相差数
		$count = count(preg_split('/\%\w/i', self::$errmsg)) - count($values);
		if ($count > 0) {
			// 变量个数 多于 给定值个数，则补充值的个数，避免出错
			for ($i = 0; $i < $count; $i++) {
				$values[] = '';
			}
		}
		// 转义变量名
		self::$errmsg = preg_replace('/\%\s+$/is', '', vsprintf(self::$errmsg, $values));

		throw new help_exception(self::$errmsg, self::$errcode);
		return false;
	}

	// 获取授权回调url
	public static function get_auth_back_url($type) {

		$parsed_url = parse_url(startup_env::get('boardurl'));
		parse_str($parsed_url['query'], $queries);
		unset($queries['code'], $queries['logintype'], $queries['act']);
		$queries['logintype'] = $type;
		$boardurl = $parsed_url['scheme'].'://'.$parsed_url['host'].$parsed_url['path'].'?'.http_build_query($queries);
		if (!empty($parsed_url['fragment'])) {
			$boardurl .= '#'.$parsed_url['fragment'];
		}

		return $boardurl;
	}

	/**
	 * 生成登录标识 auth
	 * @param string $password 密码
	 * @param int $uid 用户UID
	 * @param int $lastlogin 登录的时间戳
	 * @return string
	 */
	public static function generate_auth($password, $uid, $lastlogin) {

		return md5(md5($password."\t".$uid)."\t".$lastlogin);
	}

	/**
	 * 生成指定 URL 的二维码
	 *
	 * @param string $url URL
	 * @param string $file 若有文件名则生成文件,否则直接输出
	 * @param boolean $is_download 是否下载文件
	 */
	public static function qrcode($url, $file = '', $is_download = false) {

		// 生成二维码
		include_once (ROOT_PATH . '/framework/lib/phpqrcode.php');
		// 纠错级别：L、M、Q、H
		$errorCorrectionLevel = 'L';
		// 点的大小：1到10
		$matrixPointSize = 10;
		$qrcode = QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);
		// 创建背景,并将二维码贴到左边
		//$bk = imagecreate(330, 330);
		//imagecolorallocate($bk, 255, 255, 255);
		//imagecopy($bk, $qrcode, 0, 0, 0, 0, 640, 640);
		if ($file) {
			// 生成文件
			imagepng($qrcode, $file);
		} else if ($is_download) {
			$filename = rgmdate(startup_env::get('timestamp'), 'YmdHi');
			// 直接下载
			Header("Content-type: application/octet-stream");
			Header("Accept-Ranges: bytes");
			Header("Accept-Length:6000");
			Header("Content-Disposition: attachment; filename={$filename}.png");
			imagepng($qrcode);
		} else {
			// 直接输出图片
			header('Content-Type: image/png');
			imagepng($qrcode);
		}
	}

}
