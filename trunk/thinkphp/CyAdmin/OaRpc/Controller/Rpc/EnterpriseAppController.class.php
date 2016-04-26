<?php
/**
 * Created by PhpStorm.
 * User: ppker
 * Date: 2015/10/28
 * Time: 15:58
 */

namespace OaRpc\Controller\Rpc;

class EnterpriseAppController extends AbstractController {

	/**
	 * 为企业新增应用信息
	 *
	 * @param array $params 请求参数
	 * + ep_id int
	 * + name string
	 * + agentid int
	 * + appstatus int
	 * + icon string
	 * + desc string
	 * + pluginid int
	 */
	public function newApp($params, $update = false) {

		$app = array();
		$serv = D('Common/EnterpriseApp', 'Service');
		if (!$serv->newApp($app, $params, $update)) {
			E('_ERR_DEFAULT');
			return false;
		}

		return $app;
	}

	// 更新应用信息
	public function updateApp($params, $insert = false) {

		$app = array();
		$serv = D('Common/EnterpriseApp', 'Service');
		if (!$serv->updateApp($app, $params, $insert)) {
			E('_ERR_DEFAULT');
			return false;
		}

		return $app;
	}

}