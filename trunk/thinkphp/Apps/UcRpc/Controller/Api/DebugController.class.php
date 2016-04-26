<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/1/26
 * Time: 下午4:28
 * CRM 统计项目 RPC调试用, 开发完删除
 */

namespace UcRpc\Controller\Api;

class DebugController extends \Common\Controller\Api\AbstractController {

	/**
	 * 每个企业的应用使用数据 (OA RPC 接收 UC请求)
	 * @return bool
	 */
	public function plugin() {

		// 统计应用纬度数据
		$serv_plugin = D('UcRpc/PluginCrmstat', 'Service');
		$plugin_stat = $serv_plugin->main();

		// 提交到总后台
		$url = 'http://cy.local.vchangyi.net/OaRpc/Rpc/Crmstat';
		\Com\Rpc::query($data, $url, 'stat', $this->_setting['ep_id'], $plugin_stat);

		return true;
	}

	/**
	 * CY RPC 接收 UC 请求
	 * 注意: 发送一次请求 先执行 plugin方法 再等全部企业数据返回到CY后 执行本方法
	 * 统计今日所有应用的汇总数据
	 * @return bool
	 */
	public function sum_plugin_data() {

		$url = 'http://cy.tb6.vchangyi.com/OaRpc/Rpc/Crmstat';
		\Com\Rpc::query($data, $url, 'sum_plugin_data');

		return true;
	}

	/**
	 * CY RPC 接收 UC 请求
	 * 注意: 先等sum_plugin_data 计算完后执行此方法
	 */
	public function total_plugin_data() {

		$url = 'http://cy.tb6.vchangyi.com/OaRpc/Rpc/Crmstat';
		\Com\Rpc::query($data, $url, 'total_plugin_data');

		return true;
	}
}
