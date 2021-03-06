<?php
/**
 * 数据库配置文件 (生产环境)
 *
 * <code>
 *	 config::get('voa.db');
 * </code>
 * $Author$
 * $Id$
 */

/** 本机 ip */
$conf['selfip'] = '';

/** 所有模块(数据库) */
$conf['dbs'] = array('oa', 'uc', 'cyadmin', 'orm_oa', 'orm_cyadmin', 'orm_uc');

/** 数据库管理用户 */
$conf['dbadmin'] = array(
	'host' => '',
	'user' => '',
	'pw' => '',
	'charset' => 'utf8',
	'pconnect' => 0,
	'dbname' => '',
	'tablepre' => ''
);

/** oa 数据库配置 */
$conf['oa'] = array(
	array(
		'host' => '',
		'user' => '',
		'pw' => '',
		'charset' => 'utf8',
		'pconnect' => 0,
		'dbname' => '',
		'tablepre' => ''
	)
);

/**
 * 数据表
 * 表对应的数据源位置，默认为0
 * <code>
 *	 $conf['tables'] = array(
 *		 'member' => array(),
 *		 'member_field' => array('host' => 0),
 *	 );
 * </code>
 */
$conf['oa.tables'] = array(
	'member' => array(),
	'member_field' => array('host' => 0, 'charset' => 'UTF8'),
	'askoff' => array(),
	'askoff_post' => array(),
	'askoff_proc' => array(),
	'askoff_setting' => array()
);

/**
 * 数据表分库/分表配置
 * rule: 分库/分表规则, 指定内置分库规则, 或者指定分库分表类
 * keys: 分库/分表所必须的参数
 * config: 分库/分表的数据库相关配置
 */


/** uc 数据库配置 */
$conf['uc'] = array(
	array(
		'host' => '',
		'user' => '',
		'pw' => '',
		'charset' => 'utf8',
		'pconnect' => 0,
		'dbname' => '',
		'tablepre' => ''
	)
);

/**
 * 数据表
 * 表对应的数据源位置，默认为0
 * <code>
 *	 $conf['tables'] = array(
 *		 'member' => array(),
 *		 'member_field' => array('host' => 1),
 *	 );
 * </code>
 */
$conf['uc.tables'] = array(

);


/** 畅移主站数据库配置 */
$conf['main'] = array(
	array(
		'host' => '',
		'user' => '',
		'pw' => '',
		'charset' => 'utf8',
		'pconnect' => 0,
		'dbname' => 'vchangyi',
		'tablepre' => ''
	)
);

/**
 * 数据表
 * 表对应的数据源位置，默认为0
 * <code>
 *	 $conf['tables'] = array(
 *		 'member' => array(),
 *		 'member_field' => array('host' => 1),
 *	 );
 * </code>
 */
$conf['main.tables'] = array(

);

/** 畅移主站 后台 数据库配置 */
$conf['cyadmin'] = array(
		array(
			'host' => '',
			'user' => '',
			'pw' => '',
			'charset' => 'utf8',
			'pconnect' => 0,
			'dbname' => '',
			'tablepre' => ''
		)
);
$conf['cyadmin.tables'] = array();


/** 畅移主站 后台 数据库配置 */
$conf['orm_cyadmin'] = array(
	array(
		'dsn' => 'mysql:dbname=;host=;port=3306',
		'failover' => 'mysql:dbname=;host=;port=3306',
		'timeout' => 5,
		'user' => '',
		'password' => '',
		'charset' => 'utf8',
		'tablepre' => '',
		'persistent' => false
	)
);
$conf['orm_cyadmin.tables'] = array();

