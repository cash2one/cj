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
$conf['selfip'] = '127.0.0.1';

/** 所有模块(数据库) */
$conf['dbs'] = array('oa', 'uc', 'cyadmin', 'orm_oa', 'orm_cyadmin', 'orm_uc');

/** 数据库管理用户 */
$conf['dbadmin'] = array(
	'host' => '127.0.0.1',
	'user' => 'root',
	'pw' => '101937',
	'charset' => 'utf8',
	'pconnect' => 0,
	'dbname' => '',
	'tablepre' => 'oa_'
);

/** oa 数据库配置 */
$conf['oa'] = array(
	array(
		'host' => '127.0.0.1',
		'user' => 'root',
		'pw' => '101937',
		'charset' => 'utf8',
		'pconnect' => 0,
		'dbname' => 'vwxoa',
		'tablepre' => 'oa_'
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
/**$conf['oa.askoff.shard'] = array(
	'rule' => 'voa_shard_plugin',
	'keys' => array(),
	'config' => array(
		'host' => '127.0.0.1',
		'user' => 'root',
		'pw' => '101937',
		'charset' => 'utf8',
		'pconnect' => 0,
		'dbname' => 'vwxoa_*',
		'tablepre' => 'oa_'
	)
);
$conf['oa.askoff_draft.shard'] = $conf['oa.askoff.shard'];
$conf['oa.askoff_post.shard'] = $conf['oa.askoff.shard'];
$conf['oa.askoff_proc.shard'] = $conf['oa.askoff.shard'];
$conf['oa.askoff_setting.shard'] = $conf['oa.askoff.shard'];*/

/** uc 数据库配置 */
$conf['uc'] = array(
	array(
		'host' => '127.0.0.1',
		'user' => 'root',
		'pw' => '',
		'charset' => 'utf8',
		'pconnect' => 0,
		'dbname' => 'vucenter',
		'tablepre' => 'uc_'
	)
);
$conf['orm_uc'] = array(
	array(
		'dsn' => 'mysql:dbname='.$conf['uc'][0]['dbname'].';host='.$conf['uc'][0]['host'].';port=3306',
		'failover' => 'mysql:dbname='.$conf['uc'][0]['dbname'].';host='.$conf['uc'][0]['host'].';port=3306',
		'timeout' => 5,
		'user' => $conf['uc'][0]['user'],
		'password' => $conf['uc'][0]['pw'],
		'charset' => 'utf8',
		'tablepre' => 'uc_',
		'persistent' => false
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
		'host' => '127.0.0.1',
		'user' => 'root',
		'pw' => '101937',
		'charset' => 'utf8',
		'pconnect' => 0,
		'dbname' => 'vchangyi',
		'tablepre' => 'cy_'
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
				'host' => '127.0.0.1',
				'user' => 'root',
				'pw' => '',
				'charset' => 'utf8',
				'pconnect' => 0,
				'dbname' => 'voa',
				'tablepre' => 'cy_'
		)
);
$conf['cyadmin.tables'] = array();


/** 畅移主站 后台 数据库配置 */
$conf['orm_cyadmin'] = array(
	array(
		'dsn' => 'mysql:dbname=vchangyi_admincp;host=127.0.0.1;port=3306',
		'failover' => 'mysql:dbname=vchangyi_admincp;host=127.0.0.1;port=3306',
		'timeout' => 5,
		'user' => 'root',
		'password' => '101937',
		'charset' => 'utf8',
		'pconnect' => 0,
		'dbname' => 'vchangyi_admincp',
		'tablepre' => 'cy_',
		'persistent' => false
	)
);
$conf['orm_cyadmin.tables'] = array();

