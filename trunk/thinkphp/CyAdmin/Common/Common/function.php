<?php
/**
 * function.php
 * 项目全局方法
 * $Author$
 * $Id$
 */

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


