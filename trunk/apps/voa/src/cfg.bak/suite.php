<?php
/**
 * 企业号套件配置相关
 * $Author$
 * $Id$
 */

// 没有 agent 的套件
$conf['noagent'] = array('tj706e8d913b31c376');
// 主页型应用
$conf['appagent'] = array('tjc7a647fe1b228920-6');

// 应用和appid对应关系
$conf['plgin2appid'] = array(
	// 微信OA
	'tj0129f84436fb3a58' => array(
		'dailyreport' => 1,
		'askoff' => 2,
		'sign' => 3,
		'minutes' => 6,
		'reimburse' => 7,
		'meeting' => 8,
		'addressbook' => 9,
		'askfor' => 10,
		'vnote' => 11,
		'express' => 12,
		'invite' => 13
	),
	// 团队协作
	'tjaf008b85e2a55916' => array(
		'project' => 2,
		'blessingredpack' => 3
	),
	// 门店管理
	'tj59546543529912af' => array(
		'inspect' => 1,
		'showroom' => 2,
		'train' => 3,
		'workorder' => 4
	),
	// 销售管理（旧）
	'tjddb742f3f8c2e73d' => array(
		'travel' => 1
	),
	// 企业文化
	'tj407a156836450616' => array(
		'news' => 1,
		'thread' => 2,
		'nvote' => 3,
		'activity' => 4,
		'exam' => 5,
		'jobtrain' => 6,
		'questionnaire' => 7
	),
	// 销售管理（新）
	'tj3562f4e669a24045' => array(
		'campaign' => 1,// 活动推广
		'sale' => 2,// 销售管理
	),
	// 企业群聊
	'tj706e8d913b31c376' => array(
		'chatgroup' => 1
	),
	// 微社群
	'tjc7a647fe1b228920' => array(
		'cinvite' => 3,
		'community' => 4,
		'event' => 5,
		'banner' => 6,
	)
);
