<?php
/**
 * 命令行配置
 */

return array(
	//'配置项'=>'配置值'

	// 应用计划任务频率
	'PLUGIN_CRONTAB_INTERVAL' => 100000,
	// 每次执行计划任务站点数
	'PLUGIN_CRONTAB_LIMIT' => 100,

	// 更新关注/头像信息的计划任务
	'SUBSCRIBE_CRONTAB_INTERVAL' => 1000000,
	// 每次更新的站点数
	'SUBSCRIBE_CRONTAB_LIMIT' => 100
);