<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/29
 * Time: 上午9:48
 */
namespace UcRpc\Controller\Rpc;

class CrontabController extends AbstractController {

	public function Index() {

		return true;
	}

	/**
	 * 执行计划任务脚本
	 * @param array $types 任务类型
	 * @param array $params 请求的计划任务参数
	 */
	public function run($types, $params) {

		// 遍历所有任务
		foreach ($types as $_type) {
			switch ($_type) {
				case 'crmStat':
					$serv = D('UcRpc/CrmStat', 'Service');
					$serv->stat();
					break;
				default:
					break;
			}
		}

		return true;
	}

}