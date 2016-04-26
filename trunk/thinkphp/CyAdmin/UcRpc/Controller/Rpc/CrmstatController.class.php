<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/29
 * Time: 下午9:36
 */

namespace UcRpc\Controller\Rpc;

class CrmstatController extends AbstractController {

	/**
	 * 统计所有应用数据
	 * @return bool
	 */
	public function sum_plugin_data() {

		$serv_stat = D('Stat/StatPluginTotal', 'Service');
		$serv_stat->sum_plugin_data();

		return true;
	}

	/**
	 * 统计应用纬度数据
	 * @return bool
	 */
	public function total_plugin_data() {

		$serv_stat = D('Stat/StatPluginTotal', 'Service');
		$serv_stat->total_plugin_data();

		return true;
	}
}