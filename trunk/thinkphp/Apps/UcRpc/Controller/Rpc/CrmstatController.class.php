<?php

/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/1/27
 * Time: 下午3:54
 * CRM 数据统计
 */

namespace UcRpc\Controller\Rpc;

class CrmstatController extends AbstractController {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Crm 数据统计
	 * @author anything
	 */
	public function stat() {

		// 统计应用纬度数据
		$serv_plugin = D('UcRpc/PluginCrmstat', 'Service');
		$plugin_stat = $serv_plugin->main();

		// 提交到总后台
		$url = cfg('CYADMIN_RPC_HOST') . '/OaRpc/Rpc/Crmstat';
		$data = array();
		\Com\Rpc::query($data, $url, 'stat', $this->_setting['ep_id'], $plugin_stat);

		return true;
	}
}
