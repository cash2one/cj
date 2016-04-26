<?php
/**
 * EnterpriseAppController.class.php
 * $author$
 */

namespace PubApi\Controller\Api;

class EnterpriseAppController extends AbstractController {


	// New_get
	public function New_post() {

		$params = I('post.');
		$data = array();
		$url = cfg('CYADMIN_RPC_HOST') . '/UcRpc/Rpc/EnterpriseApp';
		if (!\Com\Rpc::query($data, $url, 'newApp', $params)) {
			E($data->getNumber() . ':' . $data->getMessage());
			return false;
		}

		return true;
	}

	// Update_get
	public function Update_post() {

		$params = I('post.');
		$data = array();
		$url = cfg('CYADMIN_RPC_HOST') . '/UcRpc/Rpc/EnterpriseApp';
		if (!\Com\Rpc::query($data, $url, 'updateApp', $params)) {
			E($data->getNumber() . ':' . $data->getMessage());
			return false;
		}

		return true;
	}

}
