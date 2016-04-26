<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/10/26
 * Time: 下午4:50
 */

namespace OaRpc\Controller\Rpc;

class CompanyPaysettingController extends AbstractController {

	/**
	 * 写入老用户试用期记录
	 * @param $oa_to_cy_data
	 * @return bool
	 */
		public function oldprobation($oa_to_cy_data) {

		$serv = D('Common/CompanyPaysetting', 'Service');
		$serv->insert_old_probation_data($oa_to_cy_data);

		return true;
	}



}
