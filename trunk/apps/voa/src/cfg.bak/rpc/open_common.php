<?php
/**
 * rpc for open common
 *
 * $Author$
 * $Id$
 */

$conf = array();
/** check function begin */
if (!function_exists('get_open_common_config')) {

/**
 * 获取通用全量配置
 *
 * @access public
 * @return void
 */
function get_open_common_config() {

	/***************************  通用配置 开始  *************************************/
	/** 所有类 */
	$conf['classes'] = array(
		'member', 'oa', 'wxmsg', 'wxevent', 'wxqymsg', 'wxqyevent', 'recognition', 'application', 'enterprise', 'test', 'experience',
		'cyadmin_enterprise', 'cyadmin_news', 'wepay_notify', 'woevent'
	);

	/** 需要签名检查 */
	$conf['auth'] = true;

	/** 测试示例 */
	$conf['test.classname'] = 'voa_server_test';
	$conf['test.methods'] = array('get');
	$conf['test.cache'] = array('ttl' => 0);

	/** 开通体验号 */
	$conf['experience.classname'] = 'voa_server_oa_experience';
	$conf['experience.methods'] = array('open');
	$conf['experience.cache'] = array('ttl' => 0);

	/** 用户操作 */
	$conf['member.classname'] = 'voa_server_oa_member';
	$conf['member.methods'] = array('get', 'edit', 'pwdmodify');
	$conf['member.cache'] = array('ttl' => 0);

	$conf['member.get.args'] = array(
		'uid' => array('type' => 'int', 'required' => true)
	);

	$conf['member.edit.args'] = array(
		'sid' => array('type' => 'string', 'required' => true),
		'data' => array('type' => 'array', 'required' => true)
	);

	// 应用接口
	$conf['application.classname'] = 'voa_server_oa_application';
	$conf['application.methods'] = array('app_open_confirm', 'app_close_confirm', 'app_delete_confirm');
	$conf['application.cache'] = array('ttl' => 0);
	$conf['application.open.args'] = array();

	// 企业corp更新接口
	$conf['enterprise.classname'] = 'voa_server_oa_enterprise';
	$conf['enterprise.methods'] = array('update_corp');
	$conf['enterprise.cache'] = array('ttl' => 0);
	$conf['enterprise.open.args'] = array();


	/** 识别接口 */
	$conf['recognition.classname'] = 'voa_server_oa_recognition';
	$conf['recognition.methods'] = array('namecard');
	$conf['recognition.cache'] = array('ttl' => 0);

	$conf['recognition.open.args'] = array();

	/** 企业站开通接口 */
	$conf['site.classname'] = 'voa_server_oa_site';
	$conf['site.methods'] = array('open', 'close', 'delete');
	$conf['site.cache'] = array('ttl' => 0);

	$conf['site.open.args'] = array();

	/** 企业 oa 接口 */
	$conf['oa.classname'] = 'voa_server_oa_oa';
	$conf['oa.methods'] = array('open', 'close', 'delete');
	$conf['oa.cache'] = array('ttl' => 0);

	$conf['oa.open.args'] = array();

	// 企业号套件接口
	$conf['wxqysuite.classname'] = 'voa_server_suite_msg';
	$conf['wxqysuite.methods'] = array('suite_ticket', 'change_auth', 'cancel_auth');
	$conf['wxqysuite.cache'] = array('ttl' => 0);

	$conf['wxqysuite.suite_ticket.args'] = array();
	$conf['wxqysuite.change_auth.args'] = array();
	$conf['wxqysuite.cancel_auth.args'] = array();

	/** weixin 普通消息接口 */
	$conf['wxmsg.classname'] = 'voa_server_weixin_msg';
	$conf['wxmsg.methods'] = array('image', 'link', 'location', 'text', 'voice');
	$conf['wxmsg.cache'] = array('ttl' => 0);

	$conf['wxmsg.image.args'] = array();
	$conf['wxmsg.link.args'] = array();
	$conf['wxmsg.location.args'] = array();
	$conf['wxmsg.text.args'] = array();
	$conf['wxmsg.voice.args'] = array();

	/** weixin 事件消息接口 */
	$conf['wxevent.classname'] = 'voa_server_weixin_event';
	$conf['wxevent.methods'] = array('subscribe', 'click', 'location', 'scan', 'unsubscribe');
	$conf['wxevent.cache'] = array('ttl' => 0);

	$conf['wxevent.subscribe.args'] = array();
	$conf['wxevent.click.args'] = array();
	$conf['wxevent.location.args'] = array();
	$conf['wxevent.scan.args'] = array();
	$conf['wxevent.unsubscribe.args'] = array();

	/** 微信企业普通消息接口 */
	$conf['wxqymsg.classname'] = 'voa_server_wxqy_msg';
	$conf['wxqymsg.methods'] = array('image', 'link', 'location', 'text', 'voice');
	$conf['wxqymsg.cache'] = array('ttl' => 0);

	$conf['wxqymsg.image.args'] = array();
	$conf['wxqymsg.link.args'] = array();
	$conf['wxqymsg.location.args'] = array();
	$conf['wxqymsg.text.args'] = array();
	$conf['wxqymsg.voice.args'] = array();

	/** 微信企业时间消息接口 */
	$conf['wxqyevent.classname'] = 'voa_server_wxqy_event';
	$conf['wxqyevent.methods'] = array('subscribe', 'click', 'location', 'scan');
	$conf['wxqyevent.cache'] = array('ttl' => 0);

	$conf['wxqyevent.subscribe.args'] = array();
	$conf['wxqyevent.click.args'] = array();
	$conf['wxqyevent.location.args'] = array();
	$conf['wxqyevent.scan.args'] = array();

	/** 主站后台企业资料接口 */
	$conf['cyadmin_enterprise.classname'] = 'voa_server_cyadmin_enterprise';
	$conf['cyadmin_enterprise.methods'] = array('cyadmin_enterprise.update_profile');
	$conf['cyadmin_enterprise.cache'] = array('ttl' => 0);

	/** 主站后台新闻公告模板接口 */
	$conf['cyadmin_news.classname'] = 'voa_server_cyadmin_news';
	$conf['cyadmin_news.methods'] = array('cyadmin_news.template_list');
	$conf['cyadmin_news.cache'] = array('ttl' => 0);

	// 微信支付
	$conf['wepay_notify.classname'] = 'voa_server_wepay_notify';
	$conf['wepay_notify.methods'] = array('order');
	$conf['wepay_notify.cache'] = array('ttl' => 0);
	$conf['wepay_notify.order.args'] = array();

	// 微信开放平台
	$conf['woevent.classname'] = 'voa_server_weopen_event';
	$conf['woevent.methods'] = array('ticket', 'unauthorized');
	$conf['woevent.cache'] = array('ttl' => 0);
	$conf['woevent.ticket.args'] = array();
	$conf['woevent.unauthorized'] = array();

	return $conf;
}

}

/** 提取配置信息 */
if (!function_exists('fetch_open_config')) {
/**
 * fetch_open_config
 * 从 open_common 里为允许的方法提取配置
 *
 * @param  array $allow_methods
 * @access public
 * @return array
 */
function fetch_open_config($allow_methods = array()) {

	/** 获取所有接口相关配置 */
	$conf_all = get_open_common_config();

	$conf = array(
		'auth' => false,
		'classes' => array(),
		'cache_controlls' => array(),
	);

	if (!$allow_methods || !is_array($allow_methods)) {
		return $conf;
	}

	/** 遍历所有可用的接口方法 */
	foreach ($allow_methods as $method) {
		/**
		 * @param $class_name 类名
		 * @param $method_name 类方法
		 */
		list($class_name, $method_name) = explode('.', $method);
		/** 类名键值 */
		$class_key = strtolower($class_name.'.classname');
		/** 类方法键值 */
		$methods_key = strtolower($class_name.'.methods');
		/** 参数键值 */
		$args_key = strtolower($method.'.args');
		/** 缓存控制键值 */
		$cache_controlls_key = strtolower($method);

		/** classes, 如果当前类不存在, 则把当前类推入 */
		if (!in_array($class_name, $conf['classes'])) {
			$conf['classes'][] = $class_name;
			$conf[$class_key] = $conf_all[$class_key];
			$conf[$methods_key] = array();
		}

		/** methods */
		if (!in_array($method_name, $conf[$methods_key])) {
			$conf[$methods_key][] = $method_name;
		}

		/** args */
		$conf[$args_key] = !empty($conf_all[$args_key]) ? $conf_all[$args_key] : array();

		/** cache controll */
		if (!empty($conf_all['cache_controlls'][$cache_controlls_key])) {
			$conf['cache_controlls'][$cache_controlls_key] = $conf_all['cache_controlls'][$cache_controlls_key];
		}
	}

	return $conf;
}

}
