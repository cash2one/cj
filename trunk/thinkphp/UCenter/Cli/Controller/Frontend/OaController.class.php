<?php
/**
 * 调用方法:
 * php -q /data/wwwroot/vchangyi.com/thinkphp/UCenter/Common/Cli/Index.php /Frontend/Oa/MsgCrontab
 *
 * OaController.class.php
 * $author$
 */

namespace Cli\Controller\Frontend;

class OaController extends AbstractController {

	// Voa 应用计划任务
	public function PluginCrontab() {

		$serv = D('Common/Crontab', 'Service');
		// 每次执行 100 个计划任务
		$limit = cfg('PLUGIN_CRONTAB_LIMIT');
		$interval = cfg('PLUGIN_CRONTAB_INTERVAL');
		do {
			// 执行计划任务, 时间范围往后推 30s
			$count = $serv->run(NOW_TIME + 30, $limit);
			// 间隔 0.1s
			usleep($interval);
		} while($count >= $limit);

		return true;
	}

	// 站点数据统计
	public function VoaStat() {

		$serv = D('Cli/Crmstat', 'Service');
		// 每次执行 100 个计划任务
		$limit = cfg('PLUGIN_CRONTAB_LIMIT');
		$interval = cfg('PLUGIN_CRONTAB_INTERVAL');
		do {
			// 执行计划任务, 时间范围往后推 30s
			$count = $serv->run(NOW_TIME + 30, $limit);
			// 间隔 0.1s
			usleep($interval);
		} while($count >= $limit);

		return true;
	}

	// 更新关注状态
	public function UpdateSubscribe() {

		$serv = D('Cli/Subscribe', 'Service');
		// 每次执行100个站点
		$limit = cfg('SUBSCRIBE_CRONTAB_LIMIT');
		$interval = cfg('SUBSCRIBE_CRONTAB_INTERVAL');
		$page = 1;
		do {
			$count = $serv->update_subscribe($page, $limit);
			$page ++;
			// 间隔 1s
			usleep($interval);
		} while($count >= $limit);

		return true;
	}

}
