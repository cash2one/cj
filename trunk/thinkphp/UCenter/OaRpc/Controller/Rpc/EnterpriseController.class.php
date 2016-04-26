<?php
/**
 * EnterpriseController.class.php
 * $author$
 */

namespace OaRpc\Controller\Rpc;

class EnterpriseController extends AbstractController {

	/**
	 * 更新企业信息
	 * @param array $profile 企业信息
	 * @param number $ep_id 企业ID
	 */
	public function update_enterprise_corpid($corpid, $ep_id) {

		$serv_ep = D('Common/EnterpriseProfile', 'Service');
		return $serv_ep->update_enterprise_corpid($corpid, $ep_id);
	}

	/**
	 * 检查企业账号有效性验证
	 * @param string $enumber 企业号(即: 企业二级域名)
	 * @return boolean
	 */
	public function check_enumber($enumber) {

		// 检查企业账号的有效性
		$serv_ep = D('Common/Enterprise', 'Service');
		$enterprise = array();
		if (!$serv_ep->check_enumber($enterprise, $enumber)) {
			E($serv_ep->get_errcode() . ':' . $serv_ep->get_errmsg());
			return false;
		}

		return true;
	}

	// 注册企业账号
	public function register() {

	}

	/**
	 * 根据 ep_id 更新企业信息
	 * @param int $ep_id 企业ID
	 * @param array $enterprise 企业信息
	 */
	public function update_by_ep_id($ep_id, $enterprise) {

		$serv = D('Common/Enterprise', 'Service');
		return $serv->update_by_ep_id($ep_id, $enterprise);
	}

	/**
	 * 更加企业ID读取信息
	 * @param int $ep_id 企业ID
	 */
	public function get_by_ep_id($ep_id) {

		$serv = D('Common/Enterprise', 'Service');
		return $serv->get($ep_id);
	}

}
