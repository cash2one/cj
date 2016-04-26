<?php
/**
 * function.cyoa_jsapi.php
 * 载入微信jsapi接口相关配置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

function smarty_function_cyoa_jsapi($params, $template) {

	// 载入相关函数库
	if (!function_exists('smarty_function_escape_special_chars')) {
		require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');
	}
	if (!function_exists('_cyoa_merge')) {
		require_once(SMARTY_PLUGINS_DIR . 'cyoa_functions.php');
	}

	$_cyoa_jsapi_ = $template->getTemplateVars('_cyoa_jsapi_');

	// 定义默认值
	$defaults = array(
		// 调用页面url
		'url' => '',
		// api配置信息
		'jsapi' => array(),
		// 需要加载的api接口名列表数组
		'list' => !empty($_cyoa_jsapi_) ? $_cyoa_jsapi_ : array(),
		// 是否启用调试模式
		'debug' => 0
	);

	foreach ($defaults as $_k => $_v) {
		if (!isset($params[$_k])) {
			$params[$_k] = $_v;
		}
	}

	// 整理api list
	$list = array();
	foreach ($params['list'] as $_api) {
		if (!in_array($_api, __wx_jsapi_list()) || in_array($_api, $list)) {
			continue;
		}
		$list[] = $_api;
	}

	// 未指定加载的接口，则不加载
	if (empty($list)) {
		return '';
	}
	// api配置信息
	if (empty($params['jsapi'])) {
		$params['jsapi'] = array();
		$wxqy_service = new voa_wxqy_service();
		$params['jsapi'] = $wxqy_service->jsapi_signature($params['url']);
	}

	// 配置数组
	$config = array(
		'appId' => $params['jsapi']['corpid'],
		'timestamp' => $params['jsapi']['timestamp'],
		'nonceStr' => $params['jsapi']['nonce_str'],
		'signature' => $params['jsapi']['signature'],
		'debug' => $params['debug'] ? true : false,
		'jsApiList' => $list
	);

	// json
	$config = rjson_encode($config);
	echo <<<EOF
wx.config({$config});
EOF;
}

/**
 * 默认微信jsapi全部接口列表
 * @return array
 */
function __wx_jsapi_list() {
	return array(
		'checkJsApi',
		'onMenuShareTimeline',
		'onMenuShareAppMessage',
		'onMenuShareQQ',
		'onMenuShareWeibo',
		'hideMenuItems',
		'showMenuItems',
		'hideAllNonBaseMenuItem',
		'showAllNonBaseMenuItem',
		'translateVoice',
		'startRecord',
		'stopRecord',
		'onRecordEnd',
		'playVoice',
		'pauseVoice',
		'stopVoice',
		'uploadVoice',
		'downloadVoice',
		'chooseImage',
		'previewImage',
		'uploadImage',
		'downloadImage',
		'getNetworkType',
		'openLocation',
		'getLocation',
		'hideOptionMenu',
		'showOptionMenu',
		'closeWindow',
		'scanQRCode',
		'chooseWXPay',
		'openProductSpecificView',
		'addCard',
		'chooseCard',
		'openCard'
	);
}
