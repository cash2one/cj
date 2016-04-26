<?php
/**
 * 邮件默认配置
 * $Author$
 * $Id$
 */

/** SMTP邮件服务器配置 */
$conf['mail']['driver'] = 'SMTP';
$conf['mail']['host'] = 'localhost';
$conf['mail']['port'] = 25;
/** optional Mail_smtp parameter */
$conf['mail']['localhost'] = 'localhost';
$conf['mail']['auth'] = false;
$conf['mail']['username'] = '';
$conf['mail']['password'] = '';

/** 邮件队列配置 */
$conf['sender_address'] = 'webmaster@vchangyi.com';
$conf['sender_name'] = 'vchangyi';
$conf['sec_to_send'] = 0;
/** 发送成功后是否从数据库里删除记录 */
$conf['delete_after_send'] = false;
$conf['max_amount_mails'] = 50;
