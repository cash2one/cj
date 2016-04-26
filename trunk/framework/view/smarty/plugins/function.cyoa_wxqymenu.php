<?php
/**
 * function.cyoa_wxqymenu.php
 * 微信企业号菜单控制相关
 * Create By Deepseath
 * $Author$
 * $Id$
 */
function smarty_function_cyoa_wxqymenu($params, $template) {

	// 载入相关函数库
	if (!function_exists('smarty_function_escape_special_chars')) {
		require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');
	}
	if (!function_exists('_cyoa_merge')) {
		require_once(SMARTY_PLUGINS_DIR . 'cyoa_functions.php');
	}

	// 定义默认值
	$defaults = array(
		// 要进行的操作 hide、show、close、hidemenu、showmenu、hidenonebase、shownonebase
		'type' => 'hide',
		// 需要操作的菜单项目
		'menuitems' => '',
		// 是否直接输出执行，为空直接输出执行，否则给出指定名称的JS函数
		'function' => ''
	);

	$params = _cyoa_merge($defaults, $params);

	$func = '_wxqymenu_'.$params['type'];

	if (!function_exists($func)) {
		return '';
	}

	$constant = '_WXQYMENU_'.strtoupper($params['type']).'_';
	if (!defined($constant)) {
		define($constant, 1);
		$params['_'.$params['type'].'_'] = 0;
	} else {
		$params['_'.$params['type'].'_'] = 1;
	}

	return $func($params, $template);
}

/**
 * 输出
 * @param array $params
 * @param object $template
 */
function _output($params, $template, $type, $code) {
	// 已经加载过，则不再输出
	if (!empty($params['_'.$type.'_'])) {
		return;
	}

	$jsapi = '';
	switch ($type) {
		case 'hide':
			$jsapi = 'hideOptionMenu';
			break;
		case 'show':
			$jsapi = 'showOptionMenu';
			break;
		case 'close':
			$jsapi = 'closeWindow';
			break;
		case 'hidemenu':
			$jsapi = 'hideMenuItems';
			break;
		case 'showmenu':
			$jsapi = 'showMenuItems';
			break;
		case 'hidenonebase':
			$jsapi = 'hideAllNonBaseMenuItem';
			break;
		case 'shownonebase':
			$jsapi = 'showAllNonBaseMenuItem';
			break;
	}

	if (empty($jsapi)) {
		return;
	}

	if ($params['function']) {
		$code = <<<EOF
function {$params['function']}() {
	{$code}
}
EOF;
	} else {
		$code = <<<EOF
wx.ready(function(){
	{$code}
});
EOF;
	}

	$template->append('_cyoa_jsapi_code_', $code);
	$template->append('_cyoa_jsapi_', $jsapi);
}

function _wxqymenu_hide($params, $template) {
	$code = <<<EOF
wx.hideOptionMenu();
EOF;

	return _output($params, $template, 'hide', $code);
}

function _wxqymenu_show($params, $template) {
	$code = <<<EOF
wx.showOptionMenu();
EOF;

	return _output($params, $template, 'show', $code);
}

function _wxqymenu_close($params, $template) {
	$code = <<<EOF
wx.closeWindow();
EOF;

	return _output($params, $template, 'close', $code);
}

function _wxqymenu_hidemenu($params, $template) {
	$params['menuitems'] = explode(',', $params['menuitems']);
	$params['menuitems'] = array_map('trim', $params['menuitems']);
	if (empty($params['menuitems'])) {
		return '';
	}
	$menuitems = rjson_encode($params['menuitems']);
	$code = <<<EOF
wx.hideMenuItems({
	"menuList": {$menuitems}
});
EOF;

	return _output($params, $template, 'hidemenu', $code);
}

function _wxqymenu_showmenu($params, $template) {
	$params['menuitems'] = explode(',', $params['menuitems']);
	$params['menuitems'] = array_map('trim', $params['menuitems']);
	if (empty($params['menuitems'])) {
		return '';
	}
	$menuitems = rjson_encode($params['menuitems']);

	$code = <<<EOF
wx.showMenuItems({
	"menuList": {$menuitems}
});
EOF;

	return _output($params, $template, 'showmenu', $code);
}

function _wxqymenu_hidenonebase($params, $template) {
	$code = <<<EOF
wx.hideAllNonBaseMenuItem();
EOF;

	return _output($params, $template, 'hidenonebase', $code);
}

function _wxqymenu_shownonebase($params, $template) {
	$code = <<<EOF
wx.showAllNonBaseMenuItem();
EOF;

	return _output($params, $template, 'shownonebase', $code);
}
