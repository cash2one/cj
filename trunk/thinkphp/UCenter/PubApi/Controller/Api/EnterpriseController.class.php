<?php
/**
 * EnterpriseController.class.php
 * $author$
 */

namespace PubApi\Controller\Api;

class EnterpriseController extends AbstractController {


	// List_get
	public function List_get() {

		$params = I('get.');
		$serv_ep = D('PubApi/Enterprise', 'Service');
		$list = array();
		if (!$serv_ep->list_enterprise($list, $params)) {
			E('_ERR_DEFAULT');
			return false;
		}

		$ep_ids = array_column($list, 'ep_id');
		$enterprise['apps'] = array();
		// 如果插件ID不为空
		if (!empty($params['pluginids'])) {
			$this->_list_apps($enterprise['apps'], $enterprise['ep_id'], $params['pluginids']);
		}

		$this->_result = array('list' => $list);
		return true;
	}


	// Fetch_get
	public function Fetch_get() {

		$params = I('get.');
		$serv_ep = D('PubApi/Enterprise', 'Service');
		$enterprise = array();
		if (!$serv_ep->get_enterprise($enterprise, $params)) {
			E('_ERR_DEFAULT');
			return false;
		}

		$enterprise['apps'] = array();
		// 如果插件ID不为空
		if (!empty($params['pluginids'])) {
			$this->_list_apps($enterprise['apps'], $enterprise['ep_id'], $params['pluginids']);
		}

		$this->_result = array('enterprise' => $enterprise);
		return true;
	}

	/**
	 * 读取应用列表
	 *
	 * @param array $apps 应用列表
	 * @param array $ep_ids 企业ID
	 * @param array $pluginids 插件ID
	 * @return boolean
	 */
	protected function _list_apps(&$apps, $ep_ids, $pluginids) {

		$ep_ids = (array)$ep_ids;
		$pluginids = (array)$pluginids;
		$data = array();
		$url = cfg('CYADMIN_RPC_HOST') . '/UcRpc/Rpc/EnterpriseApp';
		if (!\Com\Rpc::query($data, $url, 'listApp', $ep_ids, $pluginids)) {
			E($data->getNumber() . ':' . $data->getMessage());
			return false;
		}

		// 应用列表
		$apps = array();
		foreach ($data as $_app) {
			$apps[] = array(
				'name' => $_app['ea_name'],
				'ep_id' => $_app['ep_id'],
				'agentid' => $_app['ea_agentid'],
				'appstatus' => $_app['ea_appstatus'],
				'icon' => $_app['ea_icon'],
				'desc' => $_app['ea_description'],
				'pluginid' => $_app['oacp_pluginid']
			);
		}

		return true;
	}


	// Update_get
	public function Update_post() {

		$params = I('post.');
		$serv_ep = D('PubApi/Enterprise', 'Service');
		if (!$serv_ep->update_enterprise($params)) {
			E('_ERR_DEFAULT');
			return false;
		}

		return true;
	}


	// CheckMobile_get
	public function CheckMobile_get() {

		$params = I('get.', '', 'trim');
		$serv_ep = D('PubApi/Enterprise', 'Service');
		if (!$serv_ep->checkMobile($this->_result, $params)) {
			E('_ERR_DEFAULT');
			return false;
		}

		return true;
	}


	// CheckCorpid_get
	public function CheckCorpID_get() {

		$params = I('get.', '', 'trim');
		$serv_ep = D('PubApi/Enterprise', 'Service');
		if (!$serv_ep->checkCorpID($this->_result, $params)) {
			E('_ERR_DEFAULT');
			return false;
		}

		return true;
	}

	// CheckEpname
	public function CheckEpname_get() {

		$params = I('get.', '', 'trim');
		$serv_ep = D('PubApi/Enterprise', 'Service');
		if (!$serv_ep->checkEpname($this->_result, $params)) {
			E('_ERR_DEFAULT');
			return false;
		}

		return true;
	}

	// 注册操作
	public function Register_post() {

		$params = I('post.');
		$serv_ep = D('PubApi/Enterprise', 'Service');
		if (!$serv_ep->register($this->_result, $params)) {
			E('_ERR_DEFAULT');
			return false;
		}

		return true;
	}

}
