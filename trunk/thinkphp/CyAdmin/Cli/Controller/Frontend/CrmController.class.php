<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/3/14
 * Time: 下午8:09
 * 调用方法:
 * php -q /data/wwwroot/vchangyi.com/thinkphp/UCenter/Common/Cli/Index.php /Frontend/Oa/MsgCrontab
 * OaController.class.php
 */

namespace Cli\Controller\Frontend;

class CrmController extends AbstractController {

	public function Data_cyadmin() {

		//汇总数据
		$service_crm = D('UcRpc/CrmStat', 'Service');
		$service_crm->stat();
		return true;
	}
}
