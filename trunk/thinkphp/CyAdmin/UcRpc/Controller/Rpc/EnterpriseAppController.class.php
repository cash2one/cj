<?php
/**
 * Created by PhpStorm.
 * User: ppker
 * Date: 2015/10/28
 * Time: 15:58
 */

namespace UcRpc\Controller\Rpc;

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
	public function newApp($params) {

		$serv = D('Common/EnterpriseApp', 'Service');
		if (!$serv->newApp($params)) {
			E('_ERR_DEFAULT');
			return false;
		}

		return true;
	}

	public function updateApp($params) {

		$serv = D('Common/EnterpriseApp', 'Service');
		if (!$serv->updateApp($params)) {
			E('_ERR_DEFAULT');
			return false;
		}

		return true;
	}

}