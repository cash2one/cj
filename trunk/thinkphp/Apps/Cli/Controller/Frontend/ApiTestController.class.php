<?php
/**
 * 调用方法:
 * php -q /.../thinkphp/Apps/Common/Cli/Index.php /Frontend/ApiTest/Run/ qywx.vcy.com
 *
 * ApiTestController.class.php
 * $author$
 */

namespace Cli\Controller\Frontend;

class ApiTestController extends AbstractController {

	// 运行计划任务
	public function Run() {

		$serv_at = D('Cli/ApiTest', 'Service');
		$serv_at->run();
		return true;
	}
}
