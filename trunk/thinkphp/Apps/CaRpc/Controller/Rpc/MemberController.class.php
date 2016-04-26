<?php
/**
 * MemberController.class.php
 * $author$
 */

namespace CaRpc\Controller\Rpc;

class MemberController extends AbstractController {

	// 列出所有用户
	public function list_all() {

		$serv_mem = D('Common/Member', 'Service');
		$users = $serv_mem->list_all();

		return $users;
	}
}
