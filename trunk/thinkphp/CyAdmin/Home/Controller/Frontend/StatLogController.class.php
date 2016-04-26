<?php
/**
 * 统计日志
 * StatLogController.class.php
 * $author$
 */

namespace OaRpc\Controller\Rpc;

class StatLogController extends AbstractController {

	// 记录日志
	public function Record() {

		$serv_sl = D('Common/StatLog', 'Service');
		$gp = I('request.');
		return $serv_sl->record($gp);
	}

}
