<?php
/**
 * rpc for weixin, 接收来自微信服务器的接口配置
 *
 * $Author$
 * $Id$
 */

require_once dirname(__FILE__).'/open_common.php';

$allow_methods = array(
	'wxmsg.image', /** 图片消息 */
	'wxmsg.link', /** 链接消息 */
	'wxmsg.location', /** 地理位置消息 */
	'wxmsg.text', /** 文本消息 */
	'wxmsg.voice', /** 语音消息 */
	'wxevent.click', /** 自定义菜单事件消息 */
	'wxevent.location', /** 自动上报地理位置事件消息 */
	'wxevent.scan', /** 二维码扫描事件消息 */
	'wxevent.subscribe' /** 关注事件消息 */
);
$conf = fetch_open_config($allow_methods);

/** 从新定义某些专用配置，如 验证方式， 缓存时间 等 */
$conf['auth'] = true;
