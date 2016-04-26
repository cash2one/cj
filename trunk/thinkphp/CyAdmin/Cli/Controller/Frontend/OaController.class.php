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

class OaController extends AbstractController {

	public function Data_oa() {

		//正式统计所有企业写法
		$serv_profile = D('Common/EnterpriseProfile', 'Service');
		$total = $serv_profile->count();
		$limit = 200;
		//分批查询
		$times = ceil($total/$limit);

		$data = array();
		for ($i = 1; $i <= $times; $i ++) {
			// 分页参数
			list($start, $limit, $i) = page_limit($i, $limit);
			// 分页参数
			$page_option = array($start, $limit);
			$company_list = $serv_profile->list_all($page_option);
			if (!empty($company_list)) {
				foreach ($company_list as $_company) {
					// url
					$url = cfg('PROTOCAL') . $_company['ep_domain'] . '/UcRpc/Rpc/Crmstat';
					// 提交到总后台
					\Com\Rpc::query($data, $url, 'stat');
				}
			}
			//暂停1秒
			sleep(1);
		}
	}
}
