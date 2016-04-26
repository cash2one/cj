<?php
/**
 * EnterpriseController.class.php
 * $author$
 */

namespace OaRpc\Controller\Rpc;

class EnterpriseController extends AbstractController {

	/**
	 * 根据 ep_id 更新企业信息
	 * @param int $ep_id 企业ID
	 * @param array $enterprise 企业信息
	 */
	public function update_by_ep_id($ep_id, $enterprise) {

		$serv = D('Common/EnterpriseProfile', 'Service');
		return $serv->update_by_ep_id($ep_id, $enterprise);
	}

	/**
	 * 根据 ep_id 和 domain 更新企业信息
	 * @param int $ep_id 企业ID
	 * @param string $domain 域名
	 * @param array $enterprise 企业信息
	 */
	public function update_by_ep_id_notin_domain($ep_id, $domain, $enterprise) {

		$serv = D('Common/EnterpriseProfile', 'Service');
		return $serv->update_by_ep_id_notin_domain($ep_id, $domain, $enterprise);
	}

	/**
	 * 根据域名更新企业信息
	 * @param string $domain 域名信息
	 * @param array $enterprise 企业信息
	 */
	public function update_by_domain($domain, $enterprise) {

		$serv = D('Common/EnterpriseProfile', 'Service');
		return $serv->update_by_domain($domain, $enterprise);
	}

	/**
	 * 根据 ep_id 获取企业信息
	 * @param int $ep_id 企业ID
	 */
	public function get_by_ep_id($ep_id) {

		$serv = D('Common/EnterpriseProfile', 'Service');
		return $serv->get($ep_id);
	}

}
