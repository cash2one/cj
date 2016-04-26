<?php
/**
 * EnterpriseProfileController.class.php
 * $author$
 */

namespace UcRpc\Controller\Rpc;

class EnterpriseProfileController extends AbstractController {

	/**
	 * 根据 corpid 读取企业详情列表
	 * @param string $corpid 微信企业号的 corpid
	 */
	public function list_by_corpid($corpid) {

		// 读取记录
		$serv = D('Common/EnterpriseProfile', 'Service');
		return $serv->list_by_wxcorpid($corpid);
	}

	/**
	 * 根据 corpid 读取企业详情
	 * @param string $corpid 微信企业号的 corpid
	 */
	public function get_by_corpid($corpid) {

		// 读取记录
		$serv = D('Common/EnterpriseProfile', 'Service');
		return $serv->get_by_wxcorpid($corpid);
	}

	/**
	 * 根据 ep_id 更新企业信息
	 * @param int $ep_id 企业ID
	 * @param array $enterprise 企业信息
	 */
	public function update_by_ep_id($ep_id, $enterprise) {

		$serv = D('Common/EnterpriseProfile', 'Service');
		return $serv->update_by_ep_id($ep_id, $enterprise);
	}

}
