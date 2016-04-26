<?php
/**
 * 邮件发送配置相关
 * $Author$
 * $Id$
 */

$conf = array(
	'account' => 'postmaster@single-vchangyi.sendcloud.org',
	'password' => 'UkuZZbhoc58Tbq4U',
	'from' => 'service@vchangyi.com', /** 发信人邮箱 */
	'fromname' => '畅移', /** 发信人名称 */
	'tpls' => array(
		'register' => 'qrcode_email_verify',
		'pwdreset' => 'password_reset',
		'register_vchangyi' => 'voa_register_success',
		'register_cyadmin' => 'cyadmin_register_success',
		'invite_follow' => 'invite_follow'
	), /** 模板名称 */
	'register_succeed_msg' => "感谢您注册畅移云工作！您的注册信息如下：公司名称：%s，  公司账号：%s，  手机号码：%s，  邮箱地址：%s，  企业后台地址：%s，  登录企业后台的账号即注册时的手机号，密码为注册时设置的密码。另外，为了更好的办公体验，畅移信息现提供个人网页版，员工可以在电脑上更方便的使用畅移应用，与微信企业号功能实现无缝连接。请复制右边链接到浏览器中打开：%s 如有问题，请致电 4008606961",
	'cyadmin_register_succeed_msg' => "感谢您注册畅移云工作！您的注册信息如下：公司名称：%s，  公司账号：%s，  手机号码：%s，  邮箱地址：%s，  企业后台地址：%s，  账户初始信息请找专属客服索取。另外，为了更好的办公体验，畅移信息现提供个人网页版，员工可以在电脑上更方便的使用畅移应用，与微信企业号功能实现无缝连接。请复制右边链接到浏览器中打开：%s 如有问题，请致电 4008606961",
	'subject_for_register' => '感谢您注册畅移云工作，欢迎使用。', /** 注册开通企业号时的邮件标题 */
	'subject_for_invite_follow' => '邀请您关注微信企业号',
	'wxqy_follow_push_msg' => '温馨提示：为了更好的办公体验，畅移信息现提供个人网页版，员工可以在电脑上更方便的使用畅移应用，与微信企业号功能实现无缝连接。那么，如何登录畅移个人网页版呢？请复制下面链接到浏览器中打开：'
);
