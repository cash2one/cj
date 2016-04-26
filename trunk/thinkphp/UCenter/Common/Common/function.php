<?php
/**
 * function.php
 * 项目全局方法
 * $Author$
 * $Id$
 */

/**
 * 生成编码, 尽量减少重复的可能
 * @param string $mobile 手机号码
 * @param string $salt 干扰码
 * @return string
 */
function generate_code($mobile, $salt) {

	$md5 = md5(md5($mobile . NOW_TIME) . $salt);
	$md5 = substr($md5, 0, -4) . substr($mobile, -4);
	return $md5;
}
