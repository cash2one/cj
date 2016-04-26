<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/27
 * Time: 上午10:35
 */
namespace OaRpc\Controller\Rpc;

class CrmstatController extends AbstractController {

	/**
	 * CRM 统计数据 (处理单个OA站点数据)
	 * @param $ep_id
	 * @param $plugin_data
	 * @return bool
	 */
	public function stat($ep_id, $plugin_data) {

		if (empty($ep_id) || empty($plugin_data)) {
			return false;
		}

		$service = D('OaRpc/Crmstat', 'Service');
		$service->deal_stat($ep_id, $plugin_data);

		return true;
	}

}
